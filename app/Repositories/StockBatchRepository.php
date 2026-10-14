<?php

namespace App\Repositories;

use App\Exceptions\StockBatchException;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockBatchTransfer;
use App\Models\StockItem;
use App\Models\StockItemBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Batch-tracked product stock.
 *
 * Every product stock line belongs to batches (stock_batches): stock_items with
 * material_id = 0 through stock_item_batches (a stock-out may draw from several
 * batches; component lines count for their component product type), and
 * receive_materials of product purchase items through receive_materials.batch_id.
 * A batch reserved for an order
 * (order_id) may only be used for that order; order_id NULL = free stock.
 *
 * General stock = ptc_virtual = 0 lines, as everywhere else. The sum over all
 * batches of a product/stage equals the pooled figure of StockItemRepository::pStock().
 */
class StockBatchRepository implements GlobalInterface
{
    private const EPSILON = 0.0001;

    public function all()
    {
        return StockBatch::orderByDesc('batch_id')->get();
    }

    public function get($id)
    {
        return StockBatch::findOrFail($id);
    }

    public function store(array $data)
    {
        $data['created_by'] = $data['created_by'] ?? auth()->id();

        return StockBatch::create($data);
    }

    public function update($id, array $data)
    {
        $batch = StockBatch::findOrFail($id);
        $batch->update($data);

        return $batch;
    }

    public function delete($id)
    {
        return StockBatch::where('batch_id', $id)->delete();
    }

    /**
     * stock_items lines that are product stock, exactly as StockItemRepository::pStock()
     * counts them: product lines (material_id = 0) and every line carrying a component
     * product (legacy material lines with a component id included).
     */
    public static function whereProductLine($query)
    {
        return $query->where('stock_items.material_id', 0)
            ->orWhereNotNull('stock_items.component_product_type_id');
    }

    /**
     * General stock per batch and stage: in, out, balance.
     *
     * Filters: product_type_id, stage_id, batch_id, order_id (reserved for that order),
     * exclude_stock_id (ignore the lines of one stocks row, e.g. the stock-out being edited).
     */
    public function balanceQuery(array $filters = [])
    {
        $excludeStockId = $filters['exclude_stock_id'] ?? null;

        $stockLines = DB::table('stock_item_batches')
            ->join('stock_items', 'stock_items.stock_item_id', '=', 'stock_item_batches.stock_item_id')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->select('stock_item_batches.batch_id', 'stock_items.stage_id')
            ->selectRaw('CASE WHEN stocks.stock_type = 1 THEN stock_item_batches.quantity ELSE 0 END as qty_in')
            ->selectRaw('CASE WHEN stocks.stock_type = 2 THEN stock_item_batches.quantity ELSE 0 END as qty_out')
            ->where('stock_items.ptc_virtual', 0)
            ->when($excludeStockId, fn ($query) => $query->where('stock_items.stock_id', '!=', $excludeStockId));

        $returned = DB::table('return_materials')
            ->select('receive_material_id')
            ->selectRaw('SUM(quantity) as quantity')
            ->groupBy('receive_material_id');

        $receiptLines = DB::table('receive_materials')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->leftJoinSub($returned, 'returned', 'returned.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->select('receive_materials.batch_id', 'purchase_items.product_stage_id as stage_id')
            ->selectRaw('receive_materials.approved_qty - IFNULL(returned.quantity, 0) as qty_in')
            ->selectRaw('0 as qty_out')
            ->where('purchase_items.material_id', 0)
            ->whereNotNull('receive_materials.batch_id');

        // Splits move ownership between batches (out of one, into the other)
        $transfersOut = DB::table('stock_batch_transfers')
            ->select('from_batch_id as batch_id', 'stage_id')
            ->selectRaw('0 as qty_in')
            ->selectRaw('quantity as qty_out');
        $transfersIn = DB::table('stock_batch_transfers')
            ->select('to_batch_id as batch_id', 'stage_id')
            ->selectRaw('quantity as qty_in')
            ->selectRaw('0 as qty_out');

        $lines = $stockLines->unionAll($receiptLines)->unionAll($transfersOut)->unionAll($transfersIn);

        return DB::query()
            ->fromSub($lines, 'lines')
            ->join('stock_batches', 'stock_batches.batch_id', '=', 'lines.batch_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'stock_batches.order_id')
            ->select(
                'stock_batches.batch_id',
                'stock_batches.batch_no',
                'stock_batches.product_type_id',
                'stock_batches.order_id',
                'stock_batches.source_type',
                'stock_batches.created_at',
                'orders.job_no',
                'lines.stage_id'
            )
            ->selectRaw('SUM(lines.qty_in) as qty_in')
            ->selectRaw('SUM(lines.qty_out) as qty_out')
            ->selectRaw('SUM(lines.qty_in) - SUM(lines.qty_out) as balance')
            ->when(isset($filters['product_type_id']), fn ($query) => $query->where('stock_batches.product_type_id', $filters['product_type_id']))
            ->when(isset($filters['stage_id']), fn ($query) => $query->where('lines.stage_id', $filters['stage_id']))
            ->when(isset($filters['batch_id']), fn ($query) => $query->where('stock_batches.batch_id', $filters['batch_id']))
            ->when(array_key_exists('order_id', $filters), fn ($query) => $filters['order_id']
                ? $query->where('stock_batches.order_id', $filters['order_id'])
                : $query->whereNull('stock_batches.order_id'))
            ->groupBy(
                'stock_batches.batch_id',
                'stock_batches.batch_no',
                'stock_batches.product_type_id',
                'stock_batches.order_id',
                'stock_batches.source_type',
                'stock_batches.created_at',
                'orders.job_no',
                'lines.stage_id'
            );
    }

    public function balances(array $filters = []): Collection
    {
        return $this->balanceQuery($filters)->get();
    }

    /**
     * Batch list: product, source, reserved-for order and general stock left (total, rejected,
     * per stage). Empty batches are left out unless $withEmpty.
     */
    public function listing(bool $withEmpty = false): Collection
    {
        $stages = $this->balances()->groupBy('batch_id');
        $stageNames = DB::table('heads')->pluck('name', 'head_id');

        return $this->batchQuery()
            ->orderByDesc('stock_batches.batch_id')
            ->get()
            ->map(function ($batch) use ($stages, $stageNames) {
                $batch->stages = ($stages[$batch->batch_id] ?? collect())
                    ->filter(fn ($row) => abs($row->balance) > self::EPSILON)
                    ->map(function ($row) use ($stageNames) {
                        $row->stage_name = $stageNames[$row->stage_id] ?? ($row->stage_id ? 'Stage '.$row->stage_id : 'No stage');

                        return $row;
                    })
                    ->values();
                $batch->balance = $this->round($batch->stages->where('stage_id', '!=', Stock::STAGE_REJECTED)->sum('balance'));
                $batch->rejected = $this->round($batch->stages->where('stage_id', Stock::STAGE_REJECTED)->sum('balance'));

                return $batch;
            })
            ->when(! $withEmpty, fn ($batches) => $batches->filter(fn ($batch) => $batch->stages->isNotEmpty())->values());
    }

    /**
     * One batch with its stage balances, origin ("made from"), splits and full movement history.
     */
    public function detail($batchId): object
    {
        $batch = $this->listing(true)->firstWhere('batch_id', (int) $batchId);
        abort_if(! $batch, 404);

        $batch->source = $this->sourceDocument($batch);
        $batch->made_from = $this->madeFrom($batch);
        $batch->children = $this->batchQuery()->where('stock_batches.parent_batch_id', $batch->batch_id)->get();
        $batch->movements = $this->movements($batch->batch_id);

        return $batch;
    }

    /**
     * Reserve a whole batch for an order, move it to another order, or free it ($orderId NULL).
     */
    public function reserve($batchId, $orderId, ?string $note = null): StockBatch
    {
        $batch = StockBatch::findOrFail($batchId);
        $batch->update([
            'order_id' => $orderId ?: null,
            'description' => $this->appendNote($batch->description, $note),
        ]);

        return $batch;
    }

    /**
     * Split part of one stage of a batch off into a new batch, reserved for $orderId
     * (NULL = free). Only ownership moves; the stock stays where it is.
     *
     * @throws StockBatchException
     */
    public function split($batchId, $stageId, float $quantity, $orderId, string $date, ?string $note = null): StockBatch
    {
        return DB::transaction(function () use ($batchId, $stageId, $quantity, $orderId, $date, $note) {
            $parent = StockBatch::lockForUpdate()->findOrFail($batchId);
            $balance = (float) $this->balances(['batch_id' => $parent->batch_id, 'stage_id' => $stageId])->sum('balance');
            if ($quantity <= self::EPSILON || $quantity > $balance + self::EPSILON) {
                throw new StockBatchException("Batch {$parent->batch_no} has only {$this->round($balance)} at this stage, {$this->round($quantity)} requested.");
            }

            $n = StockBatch::where('parent_batch_id', $parent->batch_id)->count() + 1;
            while (StockBatch::where('batch_no', "{$parent->batch_no}-S{$n}")->exists()) {
                $n++;
            }

            $child = $this->store([
                'batch_no' => "{$parent->batch_no}-S{$n}",
                'product_type_id' => $parent->product_type_id,
                'source_type' => StockBatch::SOURCE_SPLIT,
                'source_id' => null,
                'order_id' => $orderId ?: null,
                'parent_batch_id' => $parent->batch_id,
                'description' => $note,
            ]);

            StockBatchTransfer::create([
                'from_batch_id' => $parent->batch_id,
                'to_batch_id' => $child->batch_id,
                'stage_id' => $stageId,
                'quantity' => $this->round($quantity),
                'transfer_date' => $date,
                'description' => $note,
                'created_by' => auth()->id(),
            ]);

            return $child;
        });
    }

    /**
     * Batches joined with product, size, order and parent batch, for listings.
     */
    private function batchQuery()
    {
        return DB::table('stock_batches')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_batches.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'stock_batches.order_id')
            ->leftJoin('stock_batches as parent', 'parent.batch_id', '=', 'stock_batches.parent_batch_id')
            ->select(
                'stock_batches.batch_id', 'stock_batches.batch_no', 'stock_batches.product_type_id', 'stock_batches.source_type',
                'stock_batches.source_id', 'stock_batches.order_id', 'stock_batches.parent_batch_id', 'stock_batches.description',
                'stock_batches.created_at', 'products.article_no', 'products.name as product_name', 'shead.name as size_name',
                'uhead.name as unit_name', 'orders.job_no', 'parent.batch_no as parent_batch_no'
            );
    }

    /**
     * The document a batch was created from: [label, route name, route id].
     */
    private function sourceDocument(object $batch): ?array
    {
        $sourceId = $batch->source_id;

        return match ($batch->source_type) {
            StockBatch::SOURCE_PTC => ['PTC-'.Stock::where('stock_id', $sourceId)->value('stock_no'), 'ptc.show', $sourceId],
            StockBatch::SOURCE_ISSUANCE => [Stock::where('stock_id', $sourceId)->value('stock_no'), 'stock.show', $sourceId],
            StockBatch::SOURCE_PURCHASE => ($purchase = DB::table('purchase_items')
                ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
                ->where('purchase_items.purchase_item_id', $sourceId)
                ->first(['purchases.purchase_id', 'purchases.purchase_no']))
                ? [$purchase->purchase_no, 'purchase.show', $purchase->purchase_id] : null,
            StockBatch::SOURCE_OPENING => ['Opening stock', null, null],
            StockBatch::SOURCE_SPLIT => ['Split from '.$batch->parent_batch_no, 'batch.show', $batch->parent_batch_id],
            default => ['Legacy (no recorded stock-in)', null, null],
        };
    }

    /**
     * Batches whose stock the batch's source consumed: what a PTC or issuance was made from.
     */
    private function madeFrom(object $batch): Collection
    {
        $stockIds = match ($batch->source_type) {
            StockBatch::SOURCE_PTC => Stock::where('stock_id', $batch->source_id)->orWhere('ptc_id', $batch->source_id)->pluck('stock_id'),
            StockBatch::SOURCE_ISSUANCE => collect([$batch->source_id]),
            default => collect(),
        };

        if ($stockIds->isEmpty()) {
            return collect();
        }

        return DB::table('stock_item_batches')
            ->join('stock_items', 'stock_items.stock_item_id', '=', 'stock_item_batches.stock_item_id')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('stock_batches', 'stock_batches.batch_id', '=', 'stock_item_batches.batch_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->whereIn('stocks.stock_id', $stockIds)
            ->where('stocks.stock_type', 2)
            ->where('stock_items.ptc_virtual', 0)
            ->where('stock_batches.batch_id', '!=', $batch->batch_id)
            ->groupBy('stock_batches.batch_id', 'stock_batches.batch_no', 'stock_items.stage_id', 'sthead.name')
            ->select('stock_batches.batch_id', 'stock_batches.batch_no', 'sthead.name as stage_name')
            ->selectRaw('SUM(stock_item_batches.quantity) as quantity')
            ->get();
    }

    /**
     * Every movement of a batch in date order: stock lines, purchase receipts / returns and splits.
     * Each row: date, movement, reference, link [route, id], stage_name, qty_in, qty_out, place, job_no.
     */
    public function movements($batchId): Collection
    {
        $stageNames = DB::table('heads')->pluck('name', 'head_id');
        $stageName = fn ($stageId) => $stageNames[$stageId] ?? ($stageId ? 'Stage '.$stageId : 'No stage');

        $stockLines = DB::table('stock_item_batches')
            ->join('stock_items', 'stock_items.stock_item_id', '=', 'stock_item_batches.stock_item_id')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('stocks as ptc', 'ptc.stock_id', '=', 'stocks.ptc_id')
            ->leftJoin('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('orders', 'orders.order_id', '=', DB::raw('COALESCE(stock_items.order_id, NULLIF(stocks.order_id, 0))'))
            ->where('stock_item_batches.batch_id', $batchId)
            ->select(
                'stocks.stock_id', 'stocks.stock_no', 'stocks.stock_date as date', 'stocks.stock_type', 'stocks.stock_status',
                'stocks.table_name', 'stocks.ptc_id', 'stocks.is_ptc_master', 'stocks.issue_id', 'stocks.source_id',
                'stock_items.stage_id', 'stock_items.ptc_virtual', 'stock_item_batches.quantity', 'ptc.stock_no as ptc_no',
                'deliveries.delivery_id', 'orders.job_no', 'stock_item_batches.stock_item_batch_id as sort_id'
            )
            ->get()
            ->map(function ($row) use ($stageName) {
                [$movement, $reference, $link] = $this->describe($row);

                return (object) [
                    'date' => $row->date, 'sort' => 'b'.str_pad($row->sort_id, 10, '0', STR_PAD_LEFT),
                    'movement' => $movement, 'reference' => $reference, 'link' => $link,
                    'stage_name' => $stageName($row->stage_id),
                    'qty_in' => (int) $row->stock_type === 1 ? (float) $row->quantity : 0,
                    'qty_out' => (int) $row->stock_type === 2 ? (float) $row->quantity : 0,
                    'place' => $row->ptc_virtual ? 'Inside PTC' : 'General stock',
                    'job_no' => $row->job_no,
                ];
            });

        $receipts = DB::table('receive_materials')
            ->join('receives', 'receives.receive_id', '=', 'receive_materials.receive_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->where('receive_materials.batch_id', $batchId)
            ->select('receives.receive_id', 'receives.receive_no', 'receives.receive_date as date', 'purchases.purchase_no',
                'purchase_items.product_stage_id as stage_id', 'receive_materials.approved_qty', 'receive_materials.receive_material_id')
            ->get();

        $receiptRows = $receipts->map(fn ($row) => (object) [
            'date' => $row->date, 'sort' => 'a'.str_pad($row->receive_material_id, 10, '0', STR_PAD_LEFT),
            'movement' => 'Purchase receipt', 'reference' => "{$row->purchase_no} / {$row->receive_no}",
            'link' => ['receive.show', $row->receive_id], 'stage_name' => $stageName($row->stage_id),
            'qty_in' => (float) $row->approved_qty, 'qty_out' => 0, 'place' => 'General stock', 'job_no' => null,
        ]);

        $returnRows = DB::table('return_materials')
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->where('receive_materials.batch_id', $batchId)
            ->select('returns.return_id', 'returns.return_no', 'returns.return_date as date', 'purchase_items.product_stage_id as stage_id',
                'return_materials.quantity', 'return_materials.return_material_id')
            ->get()
            ->map(fn ($row) => (object) [
                'date' => $row->date, 'sort' => 'c'.str_pad($row->return_material_id, 10, '0', STR_PAD_LEFT),
                'movement' => 'Purchase return', 'reference' => $row->return_no, 'link' => ['return.show', $row->return_id],
                'stage_name' => $stageName($row->stage_id), 'qty_in' => 0, 'qty_out' => (float) $row->quantity,
                'place' => 'General stock', 'job_no' => null,
            ]);

        $transferRows = DB::table('stock_batch_transfers')
            ->join('stock_batches as from_batch', 'from_batch.batch_id', '=', 'stock_batch_transfers.from_batch_id')
            ->join('stock_batches as to_batch', 'to_batch.batch_id', '=', 'stock_batch_transfers.to_batch_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'to_batch.order_id')
            ->where(fn ($query) => $query->where('stock_batch_transfers.from_batch_id', $batchId)->orWhere('stock_batch_transfers.to_batch_id', $batchId))
            ->select('stock_batch_transfers.*', 'from_batch.batch_no as from_no', 'to_batch.batch_no as to_no', 'orders.job_no')
            ->get()
            ->map(function ($row) use ($batchId, $stageName) {
                $out = (int) $row->from_batch_id === (int) $batchId;

                return (object) [
                    'date' => $row->transfer_date, 'sort' => 'd'.str_pad($row->transfer_id, 10, '0', STR_PAD_LEFT),
                    'movement' => $out ? 'Split off' : 'Split from',
                    'reference' => $out ? $row->to_no : $row->from_no,
                    'link' => ['batch.show', $out ? $row->to_batch_id : $row->from_batch_id],
                    'stage_name' => $stageName($row->stage_id),
                    'qty_in' => $out ? 0 : (float) $row->quantity, 'qty_out' => $out ? (float) $row->quantity : 0,
                    'place' => 'General stock', 'job_no' => $row->job_no,
                ];
            });

        return $stockLines->concat($receiptRows)->concat($returnRows)->concat($transferRows)
            ->sortBy(fn ($row) => [(string) $row->date, $row->sort])
            ->values();
    }

    /**
     * [movement, reference, link] of a stock line in a batch's history.
     */
    private function describe(object $row): array
    {
        $ptcNo = $row->ptc_no ? 'PTC-'.$row->ptc_no : null;

        return match (true) {
            (bool) $row->is_ptc_master => ['PTC start issue', 'PTC-'.$row->stock_no, ['ptc.show', $row->stock_id]],
            $row->ptc_id && (int) $row->stock_status === Stock::STATUS_PTC_RELEASED => ['PTC release', $ptcNo.' / '.Stock::ptcDisplayNo($row->stock_no, $row->ptc_no), ['ptc.show', $row->ptc_id]],
            $row->ptc_id && (int) $row->stock_type === 1 => ['PTC receipt', $ptcNo.' / '.Stock::ptcDisplayNo($row->stock_no, $row->ptc_no), ['ptc.show', $row->ptc_id]],
            (bool) $row->ptc_id => ['PTC issue', $ptcNo.' / '.Stock::ptcDisplayNo($row->stock_no, $row->ptc_no), ['ptc.show', $row->ptc_id]],
            $row->table_name === 'delivery' => ['Delivery', 'Delivery '.($row->delivery_id ?? $row->stock_no), $row->delivery_id ? ['delivery.show', $row->delivery_id] : null],
            $row->table_name === 'delivery_returns' => ['Delivery return', $row->stock_no, $row->source_id ? ['delivery-return.show', $row->source_id] : null],
            (int) $row->stock_id === 1 => ['Opening stock', 'Opening stock', null],
            (int) $row->stock_type === 1 => ['Issuance receipt', $row->stock_no, ['rstock.show', $row->stock_id]],
            default => ['Issuance', $row->stock_no, ['stock.show', $row->stock_id]],
        };
    }

    private function appendNote(?string $description, ?string $note): ?string
    {
        if (! $note) {
            return $description;
        }

        return trim(($description ? $description."\n" : '').now()->format('d-m-Y').': '.$note);
    }

    /**
     * Stock of an order's products, per "{product_type_id}_{stage_id}":
     *   reserved = general stock in batches reserved for the order, free = free batches.
     *
     * @return array<string, array{reserved: float, free: float}>
     */
    public function orderStock($orderId, array $productTypeIds): array
    {
        $stock = [];
        foreach ($this->balances() as $row) {
            if (! in_array((int) $row->product_type_id, array_map('intval', $productTypeIds), true)
                || ($row->order_id !== null && (int) $row->order_id !== (int) $orderId)) {
                continue;
            }
            $key = $row->product_type_id.'_'.$row->stage_id;
            $stock[$key] ??= ['reserved' => 0.0, 'free' => 0.0];
            $stock[$key][$row->order_id === null ? 'free' : 'reserved'] += (float) $row->balance;
        }

        return $stock;
    }

    /**
     * What an order still needs, per order line (StockItemRepository::orderRemainingItems rows):
     *   reserved_ready  reserved for the order at the line's stage (ready to deliver)
     *   reserved_other  reserved for the order at other stages (rejected excluded)
     *   in_production   in the order's open PTCs ($workInProgress, product_type_id => qty)
     *   free_ready      free stock at the line's stage (usable by any order)
     *   to_make         remaining - reserved_ready - reserved_other - in_production, not below 0
     */
    public function orderProgress($orderId, Collection $items, array $workInProgress): Collection
    {
        $stock = collect($this->orderStock($orderId, $items->pluck('product_type_id')->all()));

        return $items->map(function ($item) use ($stock, $workInProgress) {
            $key = $item->product_type_id.'_'.$item->product_stage_id;
            $item->reserved_ready = $this->round($stock[$key]['reserved'] ?? 0);
            $item->free_ready = $this->round($stock[$key]['free'] ?? 0);
            $item->reserved_other = $this->round($stock
                ->filter(function ($row, $rowKey) use ($item) {
                    [$productTypeId, $stageId] = explode('_', $rowKey);

                    return (int) $productTypeId === (int) $item->product_type_id
                        && (int) $stageId !== (int) $item->product_stage_id
                        && (int) $stageId !== Stock::STAGE_REJECTED;
                })
                ->sum('reserved'));
            $item->in_production = $this->round($workInProgress[$item->product_type_id] ?? 0);
            $item->to_make = max(0, $this->round($item->remaining_quantity - $item->reserved_ready - $item->reserved_other - $item->in_production));

            return $item;
        });
    }

    /**
     * Product stock a stock-out for $orderIds may use (its reserved batches + free batches),
     * shaped like pStock() rows for the issue forms: product_type_id, stage_id, stockIn, stockOut (0).
     *
     * @param  int|array|null  $orderIds
     */
    public function allowedStock($orderIds): Collection
    {
        $orderIds = $this->orderIds($orderIds);

        return $this->balances()
            ->filter(fn ($row) => $this->allowed($row, $orderIds))
            ->groupBy(fn ($row) => $row->product_type_id.'_'.$row->stage_id)
            ->map(fn ($rows) => (object) [
                'product_type_id' => $rows->first()->product_type_id,
                'stage_id' => $rows->first()->stage_id,
                'stockIn' => $this->round($rows->sum('balance')),
                'stockOut' => 0,
            ])
            ->values();
    }

    /**
     * Batch choices for one product on an issue form, per stage: stage_id, stage_name and the
     * batches $orderIds may take from (own first, then free) - the shape batchPicker expects.
     *
     * @param  int|array|null  $orderIds
     */
    public function batchChoices($productTypeId, $orderIds): Collection
    {
        if (! $productTypeId) {
            return collect();
        }

        $orderIds = $this->orderIds($orderIds);
        $stageNames = DB::table('heads')->pluck('name', 'head_id');

        return $this->balances(['product_type_id' => $productTypeId])
            ->filter(fn ($row) => $row->balance > self::EPSILON && $this->allowed($row, $orderIds))
            ->sortBy(fn ($row) => [$row->order_id === null ? 1 : 0, $row->batch_id])
            ->groupBy('stage_id')
            ->map(fn ($batches, $stageId) => (object) [
                'product_type_id' => (int) $productTypeId,
                'stage_id' => (int) $stageId,
                'stage_name' => $stageNames[$stageId] ?? 'No stage',
                'batches' => $batches->values(),
            ])
            ->sortBy('stage_id')
            ->values();
    }

    /**
     * Batches holding general stock (balance not zero), grouped by product type, for the
     * /stock page: batch_no, source_type, order_id, job_no, stage_id, balance.
     */
    public function stockByProduct(): Collection
    {
        return $this->balances()
            ->filter(fn ($row) => abs($row->balance) > self::EPSILON)
            ->sortBy(fn ($row) => [$row->order_id === null ? 1 : 0, $row->stage_id, $row->batch_id])
            ->groupBy('product_type_id');
    }

    /**
     * Batches a stock-out for $orderIds may take from, with stock left at this stage:
     * the orders' own batches first, then free batches, oldest first.
     * No order (0 / NULL / []) -> free batches only.
     *
     * @param  int|array|null  $orderIds
     */
    public function available($productTypeId, $stageId, $orderIds = null, $excludeStockId = null): Collection
    {
        $orderIds = $this->orderIds($orderIds);

        return $this->balances([
            'product_type_id' => $productTypeId,
            'stage_id' => $stageId,
            'exclude_stock_id' => $excludeStockId,
        ])
            ->filter(fn ($row) => $row->balance > self::EPSILON && $this->allowed($row, $orderIds))
            ->sortBy(fn ($row) => [$row->order_id === null ? 1 : 0, $row->batch_id])
            ->values();
    }

    /**
     * Split a stock-out quantity over the allowed batches.
     * $batchId pins one batch (it must be allowed and hold the full quantity).
     *
     * @param  int|array|null  $orderIds
     * @return array<int, float> batch_id => quantity
     *
     * @throws StockBatchException
     */
    public function allocate($productTypeId, $stageId, float $quantity, $orderIds = null, $batchId = null, $excludeStockId = null): array
    {
        $pool = $this->available($productTypeId, $stageId, $orderIds, $excludeStockId);

        return $this->take($pool, $quantity, $batchId, $this->productLabel($productTypeId, $stageId));
    }

    /**
     * (Re-)allocate every general product line of a stock-out record (delivery, issuance,
     * PTC issuance) to batches, after its lines were written. The record's own lines are
     * left out of the balances, so this works the same on create and on edit.
     *
     * @param  int|array|null  $orderIds  orders the stock is for (a multi-order delivery has several)
     * @param  array  $pins  "{product_type_id}_{stage_id}" => batch_id chosen on the form
     *
     * @throws StockBatchException when the allowed batches do not hold enough stock
     */
    public function allocateStockOut($stockId, $orderIds = null, array $pins = []): void
    {
        $orderIds = $this->orderIds($orderIds);
        $lines = StockItem::where('stock_id', $stockId)
            ->where('ptc_virtual', 0)
            ->where(fn ($query) => self::whereProductLine($query))
            ->orderBy('stock_item_id')
            ->get();

        $pools = [];
        foreach ($lines as $line) {
            $productTypeId = $line->component_product_type_id ?: $line->product_type_id;
            $key = $productTypeId.'_'.$line->stage_id;
            $pools[$key] ??= $this->available($productTypeId, $line->stage_id, $orderIds, $stockId)->keyBy('batch_id');

            $allocation = $this->take($pools[$key], (float) $line->quantity, $pins[$key] ?? null, $this->productLabel($productTypeId, $line->stage_id));
            foreach ($allocation as $batchId => $quantity) {
                $pools[$key][$batchId]->balance -= $quantity;
            }

            $this->assign($line->stock_item_id, $allocation);
        }
    }

    /**
     * allocateStockOut() for a stock-out record made for its own order (issuance, IGroup
     * issuance, PTC master / issuance). Stock-in records are left to StockItemObserver.
     *
     * @throws StockBatchException
     */
    public function allocateRecord($stockId, array $pins = []): void
    {
        $stock = Stock::find($stockId);
        if ($stock && (int) $stock->stock_type === 2) {
            $this->allocateStockOut($stockId, $stock->order_id, array_filter($pins));
        }
    }

    /**
     * Record the order each product line of a delivery was delivered for (stock_items.order_id).
     * A multi-order delivery line belongs to the order whose reserved batch it was taken from;
     * a line taken only from free stock stays NULL (the delivery header's order applies).
     *
     * @param  int|array|null  $orderIds
     */
    public function markLineOrders($stockId, $orderIds): void
    {
        $orderIds = $this->orderIds($orderIds);
        $lines = StockItem::where('stock_id', $stockId)
            ->where('ptc_virtual', 0)
            ->where(fn ($query) => self::whereProductLine($query))
            ->pluck('stock_item_id');

        foreach ($lines as $stockItemId) {
            $reservedFor = DB::table('stock_item_batches')
                ->join('stock_batches', 'stock_batches.batch_id', '=', 'stock_item_batches.batch_id')
                ->where('stock_item_batches.stock_item_id', $stockItemId)
                ->whereNotNull('stock_batches.order_id')
                ->distinct()
                ->pluck('stock_batches.order_id');

            $orderId = count($orderIds) === 1 ? $orderIds[0] : ($reservedFor->count() === 1 ? $reservedFor->first() : null);
            DB::table('stock_items')->where('stock_item_id', $stockItemId)->update(['order_id' => $orderId]);
        }
    }

    /**
     * @return array<int, float> batch_id => quantity
     *
     * @throws StockBatchException
     */
    private function take(Collection $pool, float $quantity, $batchId, string $label): array
    {
        if ($batchId) {
            $batch = $pool->firstWhere('batch_id', (int) $batchId);
            if (! $batch) {
                throw new StockBatchException("{$label}: the selected batch is not available for this order.");
            }
            if ($batch->balance + self::EPSILON < $quantity) {
                throw new StockBatchException("{$label}: batch {$batch->batch_no} has only {$this->round($batch->balance)} left, {$this->round($quantity)} requested.");
            }

            return [(int) $batch->batch_id => $this->round($quantity)];
        }

        $allocation = [];
        $remaining = $quantity;
        foreach ($pool as $batch) {
            if ($remaining <= self::EPSILON) {
                break;
            }
            if ($batch->balance <= self::EPSILON) {
                continue;
            }
            $taken = min($remaining, (float) $batch->balance);
            $allocation[(int) $batch->batch_id] = $this->round($taken);
            $remaining -= $taken;
        }

        if ($remaining > self::EPSILON) {
            $total = $this->round($pool->sum(fn ($batch) => max(0, $batch->balance)));
            throw new StockBatchException("{$label}: only {$total} available for this order, {$this->round($quantity)} requested.");
        }

        return $allocation;
    }

    /**
     * A free batch, or one reserved for one of $orderIds.
     */
    private function allowed(object $batch, array $orderIds): bool
    {
        return $batch->order_id === null || in_array((int) $batch->order_id, $orderIds, true);
    }

    private function orderIds($orderIds): array
    {
        return array_values(array_unique(array_filter(array_map('intval', (array) $orderIds))));
    }

    /**
     * "Article - Size X at Stage" for messages.
     */
    public function productLabel($productTypeId, $stageId): string
    {
        $product = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->where('product_types.product_type_id', $productTypeId)
            ->select('products.article_no', 'shead.name as size')
            ->first();
        $stage = $stageId ? DB::table('heads')->where('head_id', $stageId)->value('name') : 'no stage';

        return trim(($product->article_no ?? "Product {$productTypeId}").($product && $product->size ? " - Size {$product->size}" : ''))." at {$stage}";
    }

    /**
     * Record which batches a product stock line moved (replaces any previous allocation).
     *
     * @param  array<int, float>  $allocation  batch_id => quantity
     */
    public function assign($stockItemId, array $allocation): void
    {
        StockItemBatch::where('stock_item_id', $stockItemId)->delete();

        foreach ($allocation as $batchId => $quantity) {
            if ($quantity > self::EPSILON) {
                StockItemBatch::create([
                    'stock_item_id' => $stockItemId,
                    'batch_id' => $batchId,
                    'quantity' => $this->round($quantity),
                ]);
            }
        }
    }

    /**
     * Keep a product stock line's batch allocation in step with the line (StockItemObserver).
     * Stock-ins and PTC stage transfers have exactly one batch, known from their header.
     * General stock-outs are allocated by the screen that writes them.
     */
    public function syncLine(StockItem $item): void
    {
        if ($item->material_id && ! $item->component_product_type_id) {
            return;
        }

        $header = Stock::find($item->stock_id);
        if (! $header) {
            return;
        }

        if ((int) $header->stock_type === 1) {
            $this->assign($item->stock_item_id, $this->stockInAllocation($header, $item));
        } elseif ($item->ptc_virtual && $header->ptc_id) {
            $batch = $this->forPtc(Stock::findOrFail($header->ptc_id), $item->component_product_type_id ?: $item->product_type_id);
            $this->assign($item->stock_item_id, [$batch->batch_id => (float) $item->quantity]);
        }
    }

    /**
     * The batch(es) a product stock-in belongs to, from its source:
     *   PTC receipt / release -> the PTC's batch; normal issuance receipt -> the issuance's batch;
     *   opening stock -> OPEN batch; delivery return -> the batches the delivered line came from;
     *   anything else -> its own batch.
     *
     * @param  object  $line  stock_items row (stock_item_id, product_type_id, component_product_type_id, stage_id, quantity)
     * @return array<int, float> batch_id => quantity
     */
    public function stockInAllocation(Stock $header, object $line): array
    {
        $productTypeId = $line->component_product_type_id ?: $line->product_type_id;
        $quantity = (float) $line->quantity;

        if ($header->table_name === 'delivery_returns' && ($allocation = $this->deliveryReturnAllocation($header, $line))) {
            return $allocation;
        }

        if ($header->ptc_id) {
            $batch = $this->forPtc(Stock::findOrFail($header->ptc_id), $productTypeId);
        } elseif ($header->issue_id) {
            $issuance = Stock::findOrFail($header->issue_id);
            $batch = $issuance->is_ptc_master ? $this->forPtc($issuance, $productTypeId) : $this->forIssuance($issuance, $productTypeId);
        } elseif ((int) $header->stock_id === 1 || $header->table_name === 'openingStock') {
            $batch = $this->forOpening($productTypeId, $header->stock_id);
        } else {
            $batch = $this->forIssuance($header, $productTypeId);
        }

        return [$batch->batch_id => $quantity];
    }

    /**
     * A delivery return goes back into the batches its delivered line was taken from
     * (filled in the order of that line's allocation).
     */
    private function deliveryReturnAllocation(Stock $header, object $line): array
    {
        if (! $header->source_id) {
            return [];
        }

        $delivered = DB::table('delivery_return_items')
            ->join('stock_items', 'stock_items.stock_item_id', '=', 'delivery_return_items.stock_item_id')
            ->join('stock_item_batches', 'stock_item_batches.stock_item_id', '=', 'stock_items.stock_item_id')
            ->where('delivery_return_items.delivery_return_id', $header->source_id)
            ->where('stock_items.product_type_id', $line->product_type_id)
            ->where('stock_items.stage_id', $line->stage_id)
            ->when($line->component_product_type_id,
                fn ($query) => $query->where('stock_items.component_product_type_id', $line->component_product_type_id),
                fn ($query) => $query->whereNull('stock_items.component_product_type_id'))
            ->orderBy('stock_item_batches.stock_item_batch_id')
            ->get(['stock_item_batches.batch_id', 'stock_item_batches.quantity']);

        $allocation = [];
        $remaining = (float) $line->quantity;
        foreach ($delivered as $index => $row) {
            $take = $index === $delivered->count() - 1 ? $remaining : min($remaining, (float) $row->quantity);
            if ($take > self::EPSILON) {
                $allocation[(int) $row->batch_id] = ($allocation[(int) $row->batch_id] ?? 0) + $take;
                $remaining -= $take;
            }
        }

        return $allocation;
    }

    /**
     * Batch of a received purchase item, NULL for material purchase items.
     */
    public function purchaseReceiptBatchId($purchaseItemId): ?int
    {
        $isProduct = DB::table('purchase_items')
            ->where('purchase_item_id', $purchaseItemId)
            ->where('material_id', 0)
            ->exists();

        return $isProduct ? $this->forPurchaseItem($purchaseItemId)->batch_id : null;
    }

    /**
     * A purchase's batches stay reserved for the purchase's order when that order is changed.
     */
    public function syncPurchaseOrder($purchaseId, $orderId): void
    {
        StockBatch::where('source_type', StockBatch::SOURCE_PURCHASE)
            ->whereIn('source_id', DB::table('purchase_items')->where('purchase_id', $purchaseId)->pluck('purchase_item_id'))
            ->get()
            ->each(fn (StockBatch $batch) => $batch->update(['order_id' => $orderId ?: null]));
    }

    /**
     * An issuance's / PTC's batches stay reserved for its order when that order is changed.
     */
    public function syncSourceOrder(Stock $stock): void
    {
        StockBatch::where('source_type', $stock->is_ptc_master ? StockBatch::SOURCE_PTC : StockBatch::SOURCE_ISSUANCE)
            ->where('source_id', $stock->stock_id)
            ->get()
            ->each(fn (StockBatch $batch) => $batch->update(['order_id' => $stock->order_id ?: null]));
    }

    /**
     * Product stock lines whose batch allocation does not add up to the line quantity
     * (missing or stale after an edit).
     */
    public function unallocatedLines(): Collection
    {
        $allocated = DB::table('stock_item_batches')
            ->select('stock_item_id')
            ->selectRaw('SUM(quantity) as quantity')
            ->groupBy('stock_item_id');

        return DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoinSub($allocated, 'allocated', 'allocated.stock_item_id', '=', 'stock_items.stock_item_id')
            ->where(fn ($query) => self::whereProductLine($query))
            ->whereRaw('ABS(stock_items.quantity - IFNULL(allocated.quantity, 0)) > ?', [self::EPSILON])
            ->select('stock_items.stock_item_id', 'stocks.stock_id', 'stocks.stock_no', 'stock_items.quantity')
            ->selectRaw('IFNULL(allocated.quantity, 0) as allocated')
            ->orderBy('stock_items.stock_item_id')
            ->get();
    }

    /**
     * Batch of a PTC's output product (or a component returned on it), reserved for the PTC's order.
     */
    public function forPtc(Stock $ptc, $productTypeId): StockBatch
    {
        return $this->firstOrCreate(StockBatch::SOURCE_PTC, $ptc->stock_id, $productTypeId, $ptc->order_id, 'PTC-'.$ptc->stock_no);
    }

    /**
     * Batch of a received product purchase item, reserved for the purchase's order.
     */
    public function forPurchaseItem($purchaseItemId): StockBatch
    {
        $item = DB::table('purchase_items')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->where('purchase_items.purchase_item_id', $purchaseItemId)
            ->select('purchase_items.product_type_id', 'purchases.purchase_no', 'purchases.order_id')
            ->first();

        return $this->firstOrCreate(StockBatch::SOURCE_PURCHASE, $purchaseItemId, $item->product_type_id, $item->order_id, $item->purchase_no);
    }

    /**
     * Batch of product received back on a normal (non-PTC) issuance, reserved for the issuance's order.
     */
    public function forIssuance(Stock $issuance, $productTypeId): StockBatch
    {
        return $this->firstOrCreate(StockBatch::SOURCE_ISSUANCE, $issuance->stock_id, $productTypeId, $issuance->order_id, $issuance->stock_no);
    }

    /**
     * Free batch of a product's opening stock (stocks row 1).
     */
    public function forOpening($productTypeId, $openingStockId = 1): StockBatch
    {
        return $this->firstOrCreate(StockBatch::SOURCE_OPENING, $openingStockId, $productTypeId, null, 'OPEN-'.$productTypeId);
    }

    /**
     * Free catch-all batch for historical stock-outs that no recorded stock-in covers.
     */
    public function legacy($productTypeId): StockBatch
    {
        return $this->firstOrCreate(StockBatch::SOURCE_LEGACY, null, $productTypeId, null, 'LEGACY-'.$productTypeId);
    }

    private function firstOrCreate(string $sourceType, $sourceId, $productTypeId, $orderId, string $baseNo): StockBatch
    {
        $batch = StockBatch::where('source_type', $sourceType)
            ->where('product_type_id', $productTypeId)
            ->when($sourceId === null, fn ($query) => $query->whereNull('source_id'), fn ($query) => $query->where('source_id', $sourceId))
            ->first();

        return $batch ?? $this->store([
            'batch_no' => $this->uniqueBatchNo($baseNo),
            'product_type_id' => $productTypeId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'order_id' => $orderId ?: null,
        ]);
    }

    /**
     * $baseNo, or $baseNo-2, -3 ... when a document produced several batches.
     */
    private function uniqueBatchNo(string $baseNo): string
    {
        $batchNo = $baseNo;
        for ($n = 2; StockBatch::where('batch_no', $batchNo)->exists(); $n++) {
            $batchNo = "{$baseNo}-{$n}";
        }

        return $batchNo;
    }

    private function round($value): float
    {
        return round((float) $value, 4);
    }
}
