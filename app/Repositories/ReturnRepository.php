<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Models\Returns;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ReturnRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Returns::join('receives', 'receives.receive_id', '=', 'returns.receive_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('returns.*', 'receives.*', 'vendors.fname', 'purchases.purchase_no')
            ->orderBy('returns.created_at', 'desc')->get();
    }

    public function get($id)
    {
        return Returns::where('returns.return_id', $id)
            ->join('receives', 'receives.receive_id', '=', 'returns.receive_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->select('returns.*', 'receives.*', 'purchases.*', 'vendors.*', 'orders.job_no',
                'returns.description as desc')
            ->first();
    }

    public function refNo($id)
    {
        $yearMonth = Carbon::now()->format('ym');
        $count = Returns::where('receive_id', $id)->count();

        return 'Return'.$count + 1;
    }

    public function returned($id)
    {
        return Returns::where('returns.receive_id', $id)
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->select('return_materials.*')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Returns::create($data);

        return $store->return_id;
    }

    public function update($id, array $data)
    {
        $update = Returns::findOrFail($id);
        $update->update($data);

        return $update->return_id;
    }

    /**
     * A purchase return has no dependents: deleting it puts the returned quantity back in stock
     * and back on the vendor ledger.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $return = Returns::findOrFail($id);

        return DeletionImpact::for('Purchase return '.$return->return_no)
            ->cascadeIf($this->refCount('return_materials', 'return_id', $id), 'returned line')
            ->cascadeIf(DB::table('stocks')->where('table_name', Stock::TABLE_PURCHASE_RETURN)
                ->whereIn('source_id', DB::table('return_materials')->where('return_id', $id)->select('return_material_id'))->count(),
                'stage correction stock record (lines returned from another stage)')
            ->note('The returned quantity is added back to stock.');
    }

    public function delete($id)
    {
        $this->purgeStageCorrections((int) $id);
        DB::table('return_materials')->where('return_id', $id)->delete();

        return Returns::findOrFail($id)->delete();
    }

    // ------------------------------------------------------------ returns from another stage

    /**
     * Normalises the Return From choice of the form lines and checks it: a product line can be
     * returned from any of the product's stages or from Rejection (with the stage it was rejected
     * at, 0 = not recorded). The purchased stage is stored as NULL (an ordinary return).
     * Material lines have no stage.
     *
     * @param  array  $data  form input (receive_material_id[], quantity[], stage_id[], rejected_stage_id[])
     * @return array<int, array{stage_id: ?int, rejected_stage_id: ?int}> by line index
     *
     * @throws ValidationException
     */
    public function stageChoices(array $data): array
    {
        $rejection = app(RejectionRepository::class);
        $choices = [];
        foreach ($data['receive_material_id'] ?? [] as $key => $receiveMaterialId) {
            $choices[$key] = ['stage_id' => null, 'rejected_stage_id' => null];
            $stageId = (int) ($data['stage_id'][$key] ?? 0);
            $line = $this->purchaseLine((int) $receiveMaterialId);
            if (! $line || (int) $line->material_id > 0 || $stageId <= 0 || $stageId === (int) $line->product_stage_id) {
                continue;
            }

            $productStages = $rejection->rejectableStages((int) $line->product_type_id)->pluck('head_id')->map(fn ($id) => (int) $id);
            if ($stageId !== Stock::STAGE_REJECTED && ! $productStages->contains($stageId)) {
                throw ValidationException::withMessages(['stage_id' => 'Return From must be one of the product\'s stages or Rejection.']);
            }
            $rejectedAt = null;
            if ($stageId === Stock::STAGE_REJECTED) {
                $rejectedAt = (int) ($data['rejected_stage_id'][$key] ?? 0);
                if ($rejectedAt !== RejectionRepository::NOT_RECORDED && ! $productStages->contains($rejectedAt)) {
                    throw ValidationException::withMessages(['rejected_stage_id' => 'Rejected At must be one of the product\'s stages.']);
                }
            }
            $choices[$key] = ['stage_id' => $stageId, 'rejected_stage_id' => $rejectedAt];
        }

        return $choices;
    }

    /**
     * Posts the stock side of the return lines taken from another stage than the purchased one.
     * The return line itself (return_materials) is still counted at the purchased stage by every
     * stock query, so each such line gets a pair of stock documents (table purchase_returns,
     * source_id = return_material_id):
     *   in  at the purchased stage (back into the purchase's batch)  -> the purchased stage nets out
     *   out of the stage it really left (batches: the order's own, then free stock) -> that stage drops
     * Old pairs of the return are removed first, so this serves create and edit. Run it inside a
     * transaction: a shortage throws (StockBatchException / ValidationException) and nothing is kept.
     */
    public function syncStageCorrections(int $returnId): void
    {
        $this->purgeStageCorrections($returnId);

        $return = Returns::findOrFail($returnId);
        $lines = DB::table('return_materials')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->where('return_materials.return_id', $returnId)
            ->whereNotNull('return_materials.stage_id')
            ->where('return_materials.quantity', '>', 0)
            ->get(['return_materials.return_material_id', 'return_materials.quantity', 'return_materials.stage_id',
                'return_materials.rejected_stage_id', 'purchase_items.product_type_id', 'purchase_items.product_stage_id',
                'purchases.order_id']);

        // Rejection stock: the stage it was rejected at must hold the quantity
        $rejection = app(RejectionRepository::class);
        $lines->where('stage_id', Stock::STAGE_REJECTED)
            ->groupBy(fn ($l) => $l->product_type_id.'_'.(int) $l->rejected_stage_id)
            ->each(function ($group) use ($rejection) {
                $first = $group->first();
                if ($error = $rejection->shortfall((int) $first->product_type_id, (int) $first->rejected_stage_id, (float) $group->sum('quantity'))) {
                    throw ValidationException::withMessages(['quantity' => $error]);
                }
            });

        $stockRepository = app(StockRepository::class);
        $stockItemRepository = app(StockItemRepository::class);
        foreach ($lines as $line) {
            foreach ([1, 2] as $type) {
                $stockId = $stockRepository->store([
                    'stock_no' => 'RTN-'.$return->return_no,
                    'order_id' => $line->order_id ?: 0,
                    'source_id' => $line->return_material_id,
                    'table_name' => Stock::TABLE_PURCHASE_RETURN,
                    'employee_id' => 0,
                    'stock_type' => $type,
                    'stock_date' => $return->return_date,
                    'stock_status' => Stock::STATUS_RETURN_STAGE,
                    'description' => 'Purchase return '.$return->return_no.($type === 1 ? ' (purchased stage)' : ' (stage returned from)'),
                ]);
                $isOut = $type === 2;
                $stockItemRepository->store([
                    'stock_id' => $stockId,
                    'product_type_id' => $line->product_type_id,
                    'material_id' => 0,
                    'component_product_type_id' => null,
                    'quantity' => $line->quantity,
                    'stage_id' => $isOut ? $line->stage_id : $line->product_stage_id,
                    'rejected_stage_id' => $isOut && (int) $line->stage_id === Stock::STAGE_REJECTED ? ($line->rejected_stage_id ?: null) : null,
                    'ptc_virtual' => 0,
                    'work_logs' => '0',
                    'work_wages' => '0',
                ]);
                if ($isOut) {
                    app(StockBatchRepository::class)->allocateStockOut($stockId, $line->order_id ?: null);
                }
            }
        }
    }

    /** Removes the stage-correction stock documents of a return (see syncStageCorrections). */
    public function purgeStageCorrections(int $returnId): void
    {
        $ids = DB::table('stocks')->where('table_name', Stock::TABLE_PURCHASE_RETURN)
            ->whereIn('source_id', DB::table('return_materials')->where('return_id', $returnId)->select('return_material_id'))
            ->pluck('stock_id')->all();
        if ($ids) {
            app(StockRepository::class)->purge($ids);
        }
    }

    /** Purchase line of a receive line: material_id, product_type_id, product_stage_id. */
    private function purchaseLine(int $receiveMaterialId): ?object
    {
        return DB::table('receive_materials')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->where('receive_materials.receive_material_id', $receiveMaterialId)
            ->first(['purchase_items.material_id', 'purchase_items.product_type_id', 'purchase_items.product_stage_id']);
    }
}
