<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Models\Transaction;
use App\Support\DeletionImpact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Asset register (Finance > Assets). LiabilityRepository is the same register for liabilities.
 *
 * An entry raises (debit) or lowers (credit) the value of the asset named asset_name, by its type
 * (Asset::TYPES / Liability::TYPES). When the user chooses it, an entry of a type that can move
 * money also posts a cash / bank transaction (transaction_to = transaction_type = the kind,
 * payee_id = the entry): debit = money received, credit = money paid, so it shows in the cash book /
 * bank ledger. Editing the entry updates that transaction; deleting the entry deletes it.
 */
class AssetRepository implements GlobalInterface
{
    /** @var class-string<Asset> */
    protected string $model = Asset::class;

    public function __construct(private TransactionRepository $transactionRepository)
    {
    }

    public function types(): array
    {
        return ($this->model)::TYPES;
    }

    public function kind(): string
    {
        return ($this->model)::KIND;
    }

    /**
     * One row per asset name: increases, decreases, current value, money paid / received through
     * cash or bank, number of entries and the last entry date.
     */
    public function all()
    {
        return ($this->model)::query()
            ->selectRaw('asset_name,
                COALESCE(SUM(debit), 0) as total_debit,
                COALESCE(SUM(credit), 0) as total_credit,
                COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as net_value,
                COUNT(*) as entries,
                MAX(transaction_date) as last_transaction_date')
            ->groupBy('asset_name')
            ->orderBy('asset_name')
            ->get()
            ->each(function ($row) {
                $money = $this->moneyTotals($row->asset_name);
                $row->money_in = $money['in'];
                $row->money_out = $money['out'];
            });
    }

    public function get($id)
    {
        return ($this->model)::findOrFail($id);
    }

    /** Distinct names, for the name suggestions on the form. */
    public function names(): Collection
    {
        return ($this->model)::query()->distinct()->orderBy('asset_name')->pluck('asset_name');
    }

    /** All entries of one name in date order, with the account they went through. */
    public function ledger(string $name): Collection
    {
        return $this->ledgerQuery()->where('assets.asset_name', $name)->get();
    }

    /** Ledger print: entries of one name, or of every name (grouped by name, names A-Z). */
    public function printLedgers(?string $name = null): Collection
    {
        return $this->ledgerQuery()
            ->when($name !== null && $name !== '', fn ($q) => $q->where('assets.asset_name', $name))
            ->reorder()
            ->orderBy('assets.asset_name')
            ->orderBy('assets.transaction_date')
            ->orderBy('assets.asset_id')
            ->get()
            ->groupBy('asset_name');
    }

    /** One entry with its cash / bank account, for the entry voucher print. */
    public function entryWithAccount($id)
    {
        return $this->ledgerQuery()->where('assets.asset_id', $id)->firstOrFail();
    }

    private function ledgerQuery()
    {
        return ($this->model)::query()
            ->leftJoin('banks', 'banks.bank_id', '=', 'assets.bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', '=', 'banks.head_id')
            ->select('assets.*', 'bhead.name as bank_name', 'banks.account_title', 'banks.account')
            ->orderBy('assets.transaction_date')
            ->orderBy('assets.asset_id');
    }

    /** Value (asset) / outstanding amount (liability) of a name, optionally without one entry. */
    public function balance(string $name, ?int $exceptId = null): float
    {
        $sums = ($this->model)::query()
            ->where('asset_name', $name)
            ->when($exceptId, fn ($query) => $query->where('asset_id', '!=', $exceptId))
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        return round((float) $sums, 2);
    }

    /** The entry that posted a cash / bank transaction, or null. */
    public function byTransaction(int $transactionId)
    {
        return ($this->model)::where('transaction_id', $transactionId)->first();
    }

    /**
     * $data: asset_name, transaction_type, transaction_date, amount, description, affects_money,
     *        bank_id (0 = cash), attachment (stored path or null)
     *
     * @throws ValidationException when a decrease is more than the current value, or a payment is
     *                             more than the cash / bank balance
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $values = $this->entryValues($data);
            $this->guard($data, $values);

            $entry = ($this->model)::create($values + ['created_by' => auth()->id()]);
            $this->syncTransaction($entry, $data);

            return $entry->asset_id;
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $entry = ($this->model)::lockForUpdate()->findOrFail($id);
            $values = $this->entryValues($data);
            $this->guard($data, $values, $entry);

            $oldAttachment = $entry->attachment;
            $entry->update($values + ['attachment' => $data['attachment'] ?? $entry->attachment]);
            $this->syncTransaction($entry, $data);
            if (! empty($data['attachment']) && $oldAttachment && $oldAttachment !== $data['attachment']) {
                Storage::disk('public')->delete($oldAttachment);
            }

            return $entry->asset_id;
        });
    }

    /**
     * An entry takes its cash / bank transaction and attachment with it. Refused when the value
     * would go below zero without it (later decreases rely on it).
     */
    public function deletionImpact($id): DeletionImpact
    {
        $entry = $this->get($id);
        $label = ucfirst($this->kind()).' entry "'.$entry->asset_name.'" #'.$entry->asset_id;
        $impact = DeletionImpact::for($label);

        if ((float) $entry->debit > 0 && $this->balance($entry->asset_name, $entry->asset_id) < -0.005) {
            $impact->block('the decreases of "'.$entry->asset_name.'" would then be more than its increases; delete or change those entries first');
        }

        $impact->cascadeIf($entry->transaction_id ? 1 : 0, 'cash / bank transaction');
        $impact->cascadeIf($entry->attachment ? 1 : 0, 'attachment');
        if ($entry->transaction_id) {
            $impact->note('The cash book / bank ledger is recalculated without its payment.');
        }

        return $impact;
    }

    public function delete($id)
    {
        $entry = $this->get($id);
        if ($entry->transaction_id) {
            Transaction::where('transaction_id', $entry->transaction_id)->delete();
        }
        if ($entry->attachment) {
            Storage::disk('public')->delete($entry->attachment);
        }

        return $entry->delete();
    }

    /** Money received ('in') / paid ('out') through cash or bank by the entries of a name. */
    private function moneyTotals(string $name): array
    {
        $row = DB::table('transactions')
            ->join('assets', 'assets.transaction_id', '=', 'transactions.transaction_id')
            ->where('assets.kind', $this->kind())
            ->where('assets.asset_name', $name)
            ->selectRaw('COALESCE(SUM(transactions.debit), 0) as money_in, COALESCE(SUM(transactions.credit), 0) as money_out')
            ->first();

        return ['in' => (float) ($row->money_in ?? 0), 'out' => (float) ($row->money_out ?? 0)];
    }

    private function entryValues(array $data): array
    {
        $type = $this->types()[$data['transaction_type']];
        $amount = round((float) $data['amount'], 2);

        return [
            'asset_name' => trim($data['asset_name']),
            'transaction_type' => $data['transaction_type'],
            'description' => $data['description'] ?? null,
            'transaction_date' => $data['transaction_date'],
            'debit' => $type['sign'] > 0 ? $amount : null,
            'credit' => $type['sign'] < 0 ? $amount : null,
            'bank_id' => $this->movesMoney($data) ? (int) $data['bank_id'] : null,
        ] + (array_key_exists('attachment', $data) && $data['attachment'] ? ['attachment' => $data['attachment']] : []);
    }

    private function movesMoney(array $data): bool
    {
        return ! empty($data['affects_money']) && ($this->types()[$data['transaction_type']]['money'] ?? null) !== null;
    }

    /**
     * A decrease cannot take the value below zero, and a payment cannot be more than the cash /
     * bank balance (this entry's own earlier payment from the same account counts as available).
     */
    private function guard(array $data, array $values, ?Asset $entry = null): void
    {
        $name = $values['asset_name'];
        $after = $this->balance($name, $entry?->asset_id) + (float) $values['debit'] - (float) $values['credit'];
        if ($after < -0.005) {
            throw ValidationException::withMessages([
                'amount' => 'This is more than the current '.($this->kind() === 'asset' ? 'value' : 'outstanding amount')
                    .' of "'.$name.'" ('.number_format($this->balance($name, $entry?->asset_id), 2).').',
            ]);
        }
        // Moving an entry to another name must not leave the old name below zero
        if ($entry && $entry->asset_name !== $name && $this->balance($entry->asset_name, $entry->asset_id) < -0.005) {
            throw ValidationException::withMessages([
                'asset_name' => 'The other entries of "'.$entry->asset_name.'" decrease it by more than they increase it without this entry; keep the name.',
            ]);
        }

        if (! $this->movesMoney($data) || $this->types()[$data['transaction_type']]['money'] !== 'out') {
            return;
        }
        $bankId = (int) $data['bank_id'];
        $available = $bankId === 0
            ? (float) $this->transactionRepository->cashBalance()
            : (fn ($row) => (float) ($row->tdebit ?? 0) - (float) ($row->tcredit ?? 0))($this->transactionRepository->bankBalance2($bankId));
        if ($entry?->transaction_id) {
            $own = Transaction::where('transaction_id', $entry->transaction_id)->first();
            if ($own && (int) $own->bank_id === $bankId) {
                $available += (float) $own->credit - (float) $own->debit;
            }
        }
        $amount = (float) ($values['debit'] ?? $values['credit']);
        if ($amount - $available > 0.005) {
            throw ValidationException::withMessages([
                'bank_id' => 'Insufficient balance: '.($bankId === 0 ? 'cash' : 'the account').' has '.number_format($available, 2).' available.',
            ]);
        }
    }

    /** Create, update or remove the entry's cash / bank transaction to match the entry. */
    private function syncTransaction(Asset $entry, array $data): void
    {
        $existing = $entry->transaction_id ? Transaction::where('transaction_id', $entry->transaction_id)->first() : null;

        if (! $this->movesMoney($data)) {
            if ($existing) {
                $entry->update(['transaction_id' => null]);
                $existing->delete();
            }

            return;
        }

        $type = $this->types()[$entry->transaction_type];
        $amount = (float) ($entry->debit ?? $entry->credit);
        $values = [
            'transaction_to' => $this->kind(),
            'transaction_type' => $this->kind(),
            'payee_id' => $entry->asset_id,
            'bank_id' => (int) $entry->bank_id,
            'transaction_date' => $entry->transaction_date->toDateString(),
            'debit' => $type['money'] === 'in' ? $amount : null,
            'credit' => $type['money'] === 'out' ? $amount : null,
            'ledger_flag' => 1,
            'description' => $type['short'].': '.$entry->asset_name.($entry->description ? ' - '.$entry->description : ''),
        ];

        if ($existing) {
            $existing->update($values);
        } else {
            $transaction = Transaction::create($values + ['created_by' => auth()->id()]);
            $entry->update(['transaction_id' => $transaction->transaction_id]);
        }
    }
}
