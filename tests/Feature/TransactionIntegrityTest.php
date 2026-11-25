<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-18 / F-25: money saves are atomic, the cash / bank balance is checked under a lock (also on
 * edits), and edits cannot set server-controlled columns.
 */
class TransactionIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    private float $cash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail());
        // a known cash balance: +1000 on top of whatever is there
        Transaction::create(['transaction_to' => 'brs', 'transaction_type' => 'brs', 'transaction_date' => '2026-01-01', 'bank_id' => 0,
            'payee_id' => 0, 'debit' => 1000, 'ledger_flag' => 1, 'description' => 'test float', 'created_by' => 1]);
        $this->cash = (float) app(TransactionRepository::class)->cashBalance();
    }

    private function expense(float $amount)
    {
        return $this->post(route('transaction.store'), [
            'transaction_to' => 'expense', 'transaction_type' => 'expense', 'expense_type' => 'credit', 'transaction_date' => '2026-01-02',
            'bank_id' => 0, 'payee_id' => (int) DB::table('heads')->where('head_type_id', 7)->value('head_id'), 'amount' => $amount, 'description' => 'zz',
        ]);
    }

    public function test_payment_above_the_cash_balance_is_refused_and_saves_nothing(): void
    {
        $count = Transaction::count();
        $this->expense($this->cash + 1)->assertSessionHas('fails', fn ($m) => str_contains($m, 'Insufficient'));
        $this->assertSame($count, Transaction::count());

        $this->expense(10)->assertSessionMissing('fails');
        $this->assertSame($count + 1, Transaction::count());
    }

    public function test_balance_check_runs_under_a_lock(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });
        $this->expense(5)->assertSessionMissing('fails');

        $this->assertNotEmpty(array_filter($queries, fn ($sql) => str_contains($sql, 'for update') && (str_contains($sql, '`company`') || str_contains($sql, '`banks`'))));
    }

    public function test_editing_a_payment_above_the_balance_is_refused(): void
    {
        $this->expense(10)->assertSessionMissing('fails');
        $id = (int) Transaction::max('transaction_id');
        $base = ['transaction_to' => 'expense', 'transaction_type' => 'expense', 'expense_type' => 'credit', 'transaction_date' => '2026-01-02',
            'bank_id' => 0, 'payee_id' => (int) DB::table('heads')->where('head_type_id', 7)->value('head_id'), 'description' => 'zz'];

        // its own 10 is available again when it is edited
        $this->post(route('transaction.update', $id), $base + ['amount' => $this->cash])->assertSessionMissing('fails');
        $this->post(route('transaction.update', $id), $base + ['amount' => $this->cash + 1])->assertSessionHas('fails', fn ($m) => str_contains($m, 'Insufficient'));
        $this->assertEqualsWithDelta($this->cash, (float) Transaction::find($id)->credit, 0.0001);
    }

    public function test_edit_cannot_set_server_controlled_columns(): void
    {
        $this->expense(10)->assertSessionMissing('fails');
        $id = (int) Transaction::max('transaction_id');
        $this->post(route('transaction.update', $id), [
            'transaction_to' => 'expense', 'transaction_type' => 'expense', 'expense_type' => 'credit', 'transaction_date' => '2026-01-02',
            'bank_id' => 0, 'payee_id' => (int) DB::table('heads')->where('head_type_id', 7)->value('head_id'), 'amount' => 10, 'description' => 'zz',
            'ledger_flag' => 0, 'transfer_pair_id' => 1, 'created_by' => 999,
        ])->assertSessionMissing('fails');
        $row = Transaction::find($id);
        $this->assertNotSame(0, (int) $row->ledger_flag, 'still counted in the cash book');
        $this->assertEmpty($row->transfer_pair_id);
        $this->assertNotSame(999, (int) $row->created_by);
    }

    public function test_edit_validates_type_amount_and_bank(): void
    {
        $this->expense(10)->assertSessionMissing('fails');
        $id = (int) Transaction::max('transaction_id');
        $base = ['transaction_to' => 'expense', 'transaction_type' => 'expense', 'expense_type' => 'credit', 'transaction_date' => '2026-01-02',
            'bank_id' => 0, 'payee_id' => (int) DB::table('heads')->where('head_type_id', 7)->value('head_id'), 'amount' => 10];

        $this->post(route('transaction.update', $id), array_merge($base, ['amount' => -5]))->assertSessionHasErrors('amount');
        $this->post(route('transaction.update', $id), array_merge($base, ['transaction_type' => 'wages']))->assertSessionHasErrors('transaction_type');
        $this->post(route('transaction.update', $id), array_merge($base, ['bank_id' => 987654]))->assertSessionHasErrors('bank_id');
    }

    public function test_transfer_above_balance_is_refused_and_a_valid_one_writes_both_halves(): void
    {
        $bankId = (int) DB::table('banks')->value('bank_id');
        $count = Transaction::count();

        $this->post(route('transaction.storeTransfer'), ['from_bank_id' => 0, 'to_bank_id' => $bankId, 'amount' => $this->cash + 1, 'transaction_date' => '2026-01-03'])
            ->assertSessionHas('fails');
        $this->assertSame($count, Transaction::count());

        $this->post(route('transaction.storeTransfer'), ['from_bank_id' => 0, 'to_bank_id' => $bankId, 'amount' => 100, 'transaction_date' => '2026-01-03'])
            ->assertSessionMissing('fails');
        $this->assertSame($count + 2, Transaction::count());
        $out = Transaction::where('transaction_to', 'transfer')->orderByDesc('transaction_id')->skip(1)->first();
        $in = Transaction::where('transaction_to', 'transfer')->orderByDesc('transaction_id')->first();
        $this->assertSame((int) $in->transaction_id, (int) $out->transfer_pair_id);
        $this->assertSame((int) $out->transaction_id, (int) $in->transfer_pair_id);

        // editing it above what the source holds (its own 100 counts as available) is refused
        $this->post(route('transaction.updateTransfer', $out->transaction_id), ['from_bank_id' => 0, 'to_bank_id' => $bankId, 'amount' => $this->cash + 1, 'transaction_date' => '2026-01-03'])
            ->assertSessionHas('fails');
        $this->assertEqualsWithDelta(100, (float) $out->fresh()->credit, 0.0001);
    }
}
