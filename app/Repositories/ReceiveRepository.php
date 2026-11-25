<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Exceptions\DeletionBlockedException;
use App\Models\StockBatch;
use App\Models\Receive;
use Carbon\Carbon;

class ReceiveRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Receive::join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->join('receive_materials', 'receive_materials.receive_id', 'receives.receive_id')
            ->select('receives.*', 'vendors.fname', 'purchases.purchase_no')
            ->groupBy('receive_materials.receive_id')
            ->selectRaw('SUM(receive_materials.pending_qty) as pqty')
            ->orderBy('receives.created_at', 'desc')->get();

        // Without Status of Pending / Checked etc
        return Receive::join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('receives.*', 'vendors.fname', 'purchases.purchase_no')
            ->orderBy('receives.created_at', 'desc')->get();
    }

    public function get($id)
    {
        return Receive::where('receives.receive_id', $id)
            ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->select('receives.*', 'purchases.*', 'vendors.*', 'orders.job_no', 'receives.description as desc')
            // Receiving status as of this receipt (F-22: it used to join an unrelated stocks row and
            // always showed Pending): 1 = every purchase line fully received by now, 2 = partially
            ->selectRaw('CASE WHEN EXISTS (
                    SELECT 1 FROM purchase_items pi
                    WHERE pi.purchase_id = receives.purchase_id
                    AND pi.quantity > (SELECT COALESCE(SUM(rm.quantity), 0) FROM receive_materials rm
                        WHERE rm.purchase_item_id = pi.purchase_item_id AND rm.receive_id <= receives.receive_id)
                ) THEN 2 ELSE 1 END as stock_status')
            ->first();
    }

    public function pending()
    {
        // Used to Add Return
        return Receive::where('receives.receive_status', '0')
            ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('receives.*', 'vendors.fname', 'purchases.purchase_no')
            ->orderBy('receives.created_at', 'desc')->get();
    }

    public function refNo($id)
    {
        $yearMonth = Carbon::now()->format('ym');
        $count = Receive::where('purchase_id', $id)->count();

        return 'R'.$count + 1;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Receive::create($data);

        return $store->receive_id;
    }

    public function update($id, array $data)
    {
        $update = Receive::findOrFail($id);
        $update->update($data);

        return $update->receive_id;
    }

    /** Receipt header columns a new receipt / an edit may set. */
    private const NEW_HEADER_FIELDS = ['receive_no', 'purchase_id', 'receive_date', 'description'];

    private const EDIT_HEADER_FIELDS = ['receive_date', 'description'];

    private const EPSILON = 0.0001;

    /**
     * Saves a purchase receipt (new when $receiveId is null) and its lines in one transaction.
     *
     * - The purchase's lines are row-locked first, so receipts of one purchase are checked one by one.
     * - D2: a line may not bring the total received above the ordered quantity (tolerance 0). A line
     *   that is not raised (unchanged or lowered) is not checked, so an already over-received legacy
     *   line never blocks other saves.
     * - D4: a line with returns cannot be lowered or dropped; a line whose stock was used cannot be
     *   lowered / dropped by more than is still in stock (StockItemRepository::stockInRemovalShortfalls).
     *
     * @param  array  $data  validated request data (header + purchase_item_id[], quantity[], approved_qty[], rejected_qty[], pending_qty[], inspection_date[])
     *
     * @throws \App\Exceptions\DocumentLockedException
     */
    public function saveReceipt(?int $receiveId, array $data): int
    {
        return DB::transaction(function () use ($receiveId, $data) {
            $receive = $receiveId ? Receive::findOrFail($receiveId) : null;
            $purchaseId = (int) ($receive->purchase_id ?? $data['purchase_id']);

            $purchaseLines = DB::table('purchase_items')->where('purchase_id', $purchaseId)->orderBy('purchase_item_id')->lockForUpdate()
                ->get(['purchase_item_id', 'quantity', 'material_id', 'product_type_id', 'product_stage_id'])->keyBy('purchase_item_id');
            $existing = $receiveId
                ? \App\Models\ReceiveMaterial::where('receive_id', $receiveId)->lockForUpdate()->get()->keyBy('purchase_item_id')
                : collect();

            $lines = [];
            foreach ($data['quantity'] as $key => $quantity) {
                $itemId = (int) $data['purchase_item_id'][$key];
                $lines[$itemId] = [
                    'purchase_item_id' => $itemId,
                    'quantity' => (float) $quantity,
                    'approved_qty' => (float) $data['approved_qty'][$key],
                    'rejected_qty' => (float) $data['rejected_qty'][$key],
                    'pending_qty' => (float) $data['pending_qty'][$key],
                    'inspection_date' => $data['inspection_date'][$key],
                ];
            }

            $this->assertNotAboveOrdered($purchaseLines, $lines, $existing, $receiveId);
            $this->assertUsedLinesKept($existing, $lines);

            if ($receive) {
                $receive->update(array_intersect_key($data, array_flip(self::EDIT_HEADER_FIELDS)));
                app(ReceiveMaterialRepository::class)->update($receiveId, [
                    'purchase_item_id' => array_column($lines, 'purchase_item_id'),
                    'quantity' => array_column($lines, 'quantity'),
                    'approved_qty' => array_column($lines, 'approved_qty'),
                    'rejected_qty' => array_column($lines, 'rejected_qty'),
                    'pending_qty' => array_column($lines, 'pending_qty'),
                    'inspection_date' => array_column($lines, 'inspection_date'),
                ]);

                return $receiveId;
            }

            $newId = $this->store(array_intersect_key($data, array_flip(self::NEW_HEADER_FIELDS)));
            foreach ($lines as $line) {
                app(ReceiveMaterialRepository::class)->store($line + ['receive_id' => $newId]);
            }

            return $newId;
        });
    }

    /** D2: total received per purchase line may not exceed the ordered quantity (raised lines only). */
    private function assertNotAboveOrdered($purchaseLines, array $lines, $existing, ?int $receiveId): void
    {
        foreach ($lines as $itemId => $line) {
            $before = (float) ($existing->get($itemId)->quantity ?? 0);
            if ($line['quantity'] <= $before + self::EPSILON) {
                continue; // not raised: an over-received legacy line does not block the save
            }
            $others = (float) DB::table('receive_materials')->where('purchase_item_id', $itemId)
                ->when($receiveId, fn ($q) => $q->where('receive_id', '!=', $receiveId))
                ->sum('quantity');
            $ordered = (float) $purchaseLines->get($itemId)->quantity;
            if ($others + $line['quantity'] > $ordered + self::EPSILON) {
                throw new \App\Exceptions\DocumentLockedException(
                    $this->purchaseLineLabel($purchaseLines->get($itemId)).": ordered {$this->qty($ordered)}, already received {$this->qty($others)}; "
                    ."this would make it {$this->qty($others + $line['quantity'])}. Receiving more than was ordered is not allowed."
                );
            }
        }
    }

    /** D4: lines with returns, or whose stock was used since, cannot be lowered or dropped. */
    private function assertUsedLinesKept($existing, array $lines): void
    {
        foreach ($existing as $itemId => $row) {
            $new = $lines[$itemId] ?? null;
            $lowered = ! $new
                || $new['approved_qty'] < (float) $row->approved_qty - self::EPSILON
                || $new['quantity'] < (float) $row->quantity - self::EPSILON;
            if (! $lowered) {
                continue;
            }
            $label = $this->purchaseLineLabel(DB::table('purchase_items')->where('purchase_item_id', $itemId)->first());
            if (DB::table('return_materials')->where('receive_material_id', $row->receive_material_id)->exists()) {
                throw new \App\Exceptions\DocumentLockedException(
                    "{$label}: part of this receipt line was returned, so it cannot be lowered or removed.".\App\Exceptions\DocumentLockedException::ASK_ADMIN
                );
            }
            $removed = (float) $row->approved_qty - ($new['approved_qty'] ?? 0.0);
            if ($removed > self::EPSILON) {
                $shortfalls = app(StockItemRepository::class)->stockInRemovalShortfalls([], [$row->receive_material_id], [$row->receive_material_id => $removed]);
                if ($shortfalls) {
                    throw new \App\Exceptions\DocumentLockedException(
                        "{$label}: stock from this receipt was already used (".implode('; ', $shortfalls).').'.\App\Exceptions\DocumentLockedException::ASK_ADMIN
                    );
                }
            }
        }
    }

    private function purchaseLineLabel(?object $item): string
    {
        if (! $item) {
            return 'Purchase line';
        }
        if ((int) $item->material_id > 0) {
            return (string) (DB::table('materials')->where('material_id', $item->material_id)->value(DB::raw("CONCAT(material_no, ' - ', name)")) ?? 'Material #'.$item->material_id);
        }

        return app(StockBatchRepository::class)->productLabel((int) $item->product_type_id, (int) ($item->product_stage_id ?? 0));
    }

    private function qty(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2, '.', ','), '0'), '.');
    }

    /**
     * A receiving can be deleted only while nothing was returned against it and its approved
     * quantity is still in stock (not issued, delivered or used since).
     */
    public function deletionImpact($id): DeletionImpact
    {
        $receive = Receive::findOrFail($id);
        $impact = DeletionImpact::for('Receiving '.$receive->receive_no);
        $lineIds = DB::table('receive_materials')->where('receive_id', $id)->pluck('receive_material_id')->all();

        $impact->blockIf($this->refCount('returns', 'receive_id', $id), 'purchase return', null, $this->refExamples('returns', 'receive_id', $id, 'return_no'));
        foreach (app(StockItemRepository::class)->stockInRemovalShortfalls([], $lineIds) as $shortfall) {
            $impact->block('stock already used: '.$shortfall);
        }

        $impact->cascadeIf(count($lineIds), 'received line');
        $impact->note('The approved quantity leaves stock and the vendor ledger no longer shows this receiving.');

        return $impact;
    }

    public function delete($id)
    {
        $receive = Receive::findOrFail($id);
        $lines = DB::table('receive_materials')->where('receive_id', $id)->lockForUpdate()->get(['receive_material_id', 'purchase_item_id']);

        // Checked again under lock: stock may have been used since the user confirmed
        $shortfalls = app(StockItemRepository::class)->stockInRemovalShortfalls([], $lines->pluck('receive_material_id')->all());
        if ($shortfalls) {
            throw new DeletionBlockedException('Receiving '.$receive->receive_no.' cannot be deleted: '.implode('; ', $shortfalls));
        }

        DB::table('receive_materials')->where('receive_id', $id)->delete();
        app(StockBatchRepository::class)->deleteUnused(StockBatch::SOURCE_PURCHASE, $lines->pluck('purchase_item_id')->unique()->all());

        return $receive->delete();
    }
}
