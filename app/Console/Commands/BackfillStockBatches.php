<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use App\Repositories\StockBatchRepository;
use App\Repositories\StockItemRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rebuilds the batch history of existing product stock (one-off, before batch tracking).
 *
 * Every product movement is replayed in date order:
 *   - stock-ins get the batch of their source: PTC receipt / release -> the PTC's batch,
 *     purchase receipt -> the purchase item's batch, normal issuance receipt -> the issuance's
 *     batch, opening stock -> OPEN batch. Batches are reserved for the source's order.
 *   - stock-outs from general stock are matched to the batches holding stock at that time:
 *     the movement's own order first, then free stock, oldest first. History is recorded as it
 *     happened, so if only another order's stock existed it is used and reported as cross-order;
 *     if no stock existed at all the line is charged to a LEGACY batch and reported.
 *
 * --dry-run does everything inside a transaction and rolls it back.
 */
class BackfillStockBatches extends Command
{
    protected $signature = 'stock:backfill-batches
        {--dry-run : Show the result without writing}
        {--fresh : Delete existing batches and allocations first}
        {--check : Only report lines whose batch allocation is missing or stale}';

    protected $description = 'Assign batches to existing product stock movements';

    private const EPSILON = 0.0001;

    private StockBatchRepository $batches;

    /** batch_id => stage_id => general stock balance */
    private array $ledger = [];

    /** batch_id => StockBatch */
    private array $batchCache = [];

    private array $stockCache = [];

    private array $rows = [];

    private array $warnings = [];

    public function handle(StockBatchRepository $batches, StockItemRepository $stockItemRepository): int
    {
        $this->batches = $batches;

        // Batches record created_by; run as the first admin
        auth()->setUser(User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail());

        if ($this->option('check')) {
            return $this->check($stockItemRepository);
        }

        if (StockBatch::exists() && ! $this->option('fresh')) {
            $this->error('Batches already exist. Use --fresh to rebuild them (only before reservations were changed by hand).');

            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            if ($this->option('fresh')) {
                $this->wipe();
            }

            foreach ($this->events() as $event) {
                $event->kind === 'receipt' ? $this->receipt($event) : $this->stockLine($event);
            }

            $this->report();
            $mismatches = $this->reconcile($stockItemRepository);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        if ($this->option('dry-run') || $mismatches) {
            DB::rollBack();
            $this->warn($mismatches ? 'Totals do not reconcile - nothing written.' : 'Dry run - nothing written.');

            return $mismatches ? self::FAILURE : self::SUCCESS;
        }

        DB::commit();
        $this->info('Batches written.');

        return self::SUCCESS;
    }

    private function wipe(): void
    {
        DB::table('stock_item_batches')->delete();
        DB::table('receive_materials')->update(['batch_id' => null]);
        DB::table('stock_items')->update(['order_id' => null]);
        DB::table('stock_batches')->whereNotNull('parent_batch_id')->delete();
        DB::table('stock_batches')->delete();
    }

    /**
     * Product stock lines and product purchase receipts in date order.
     */
    private function events(): array
    {
        $stockLines = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where(fn ($query) => StockBatchRepository::whereProductLine($query))
            ->select(
                'stock_items.stock_item_id as id', 'stock_items.product_type_id', 'stock_items.material_id', 'stock_items.component_product_type_id',
                'stock_items.stage_id', 'stock_items.quantity', 'stock_items.ptc_virtual',
                'stocks.stock_id', 'stocks.stock_no', 'stocks.stock_type', 'stocks.table_name', 'stocks.order_id',
                'stocks.ptc_id', 'stocks.issue_id', 'stocks.stock_date as date', 'stocks.created_at'
            )
            ->get()
            ->map(function ($line) {
                $line->kind = 'stock';
                $line->pt = $line->component_product_type_id ?: $line->product_type_id;

                return $line;
            });

        $returned = DB::table('return_materials')
            ->select('receive_material_id')
            ->selectRaw('SUM(quantity) as quantity')
            ->groupBy('receive_material_id');

        $receipts = DB::table('receive_materials')
            ->join('receives', 'receives.receive_id', '=', 'receive_materials.receive_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->leftJoinSub($returned, 'returned', 'returned.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->where('purchase_items.material_id', 0)
            ->select(
                'receive_materials.receive_material_id as id', 'receive_materials.purchase_item_id',
                'purchase_items.product_type_id as pt', 'purchase_items.product_stage_id as stage_id',
                'purchases.purchase_no as stock_no', 'receives.receive_date as date', 'receive_materials.created_at'
            )
            ->selectRaw('receive_materials.approved_qty - IFNULL(returned.quantity, 0) as quantity')
            ->get()
            ->map(function ($line) {
                $line->kind = 'receipt';

                return $line;
            });

        return $stockLines->concat($receipts)
            ->sortBy(fn ($event) => [$event->date, (string) $event->created_at, $event->kind === 'receipt' ? 0 : 1, $event->id])
            ->values()
            ->all();
    }

    private function receipt(object $event): void
    {
        $batch = $this->batches->forPurchaseItem($event->purchase_item_id);
        DB::table('receive_materials')->where('receive_material_id', $event->id)->update(['batch_id' => $batch->batch_id]);
        $this->credit($batch, $event->stage_id, (float) $event->quantity);
        $this->row($event, 'IN', [$batch->batch_id => (float) $event->quantity]);
    }

    private function stockLine(object $line): void
    {
        $quantity = (float) $line->quantity;

        if ($line->material_id) {
            $this->warnings[] = "Line {$line->id} ({$line->stock_no}) is material {$line->material_id} but also carries component product {$line->component_product_type_id}: "
                .'it is counted in both material and product stock - review.';
        }

        if ((int) $line->stock_type === 1) {
            $allocation = $this->batches->stockInAllocation($this->stock($line->stock_id), $line);
            $this->batches->assign($line->id, $allocation);
            if (! $line->ptc_virtual) {
                foreach ($allocation as $batchId => $taken) {
                    $this->credit($this->batch($batchId), $line->stage_id, $taken);
                }
            }
            $this->row($line, $line->ptc_virtual ? 'IN (PTC)' : 'IN', $allocation);

            return;
        }

        if ($line->table_name === 'delivery' && $line->order_id) {
            DB::table('stock_items')->where('stock_item_id', $line->id)->update(['order_id' => $line->order_id]);
        }

        // Stage transfer inside a PTC: the PTC's own batch, never general stock
        if ($line->ptc_virtual) {
            $batch = $this->batches->forPtc($this->stock($line->ptc_id), $line->pt);
            $this->batches->assign($line->id, [$batch->batch_id => $quantity]);
            $this->row($line, 'OUT (PTC)', [$batch->batch_id => $quantity]);

            return;
        }

        [$allocation, $flag] = $this->allocate($line->pt, $line->stage_id, $quantity, $line->order_id ?: null);
        $this->batches->assign($line->id, $allocation);
        $this->row($line, 'OUT', $allocation, $flag);
    }

    /**
     * Match a historical stock-out to the batches holding stock at that moment.
     *
     * @return array{0: array<int, float>, 1: string}
     */
    private function allocate($productTypeId, $stageId, float $quantity, $orderId): array
    {
        $candidates = collect($this->ledger)
            ->map(fn ($stages, $batchId) => ['batch' => $this->batch($batchId), 'balance' => $stages[$stageId] ?? 0])
            ->filter(fn ($entry) => (int) $entry['batch']->product_type_id === (int) $productTypeId && $entry['balance'] > self::EPSILON)
            ->sortBy(fn ($entry) => [$this->rank($entry['batch'], $orderId), $entry['batch']->batch_id]);

        $allocation = [];
        $flags = [];
        $remaining = $quantity;
        foreach ($candidates as $entry) {
            if ($remaining <= self::EPSILON) {
                break;
            }
            $take = min($remaining, $entry['balance']);
            $allocation[$entry['batch']->batch_id] = round($take, 4);
            $remaining -= $take;
            if ($this->rank($entry['batch'], $orderId) === 2) {
                $flags['cross'] = 'CROSS-ORDER';
            }
        }

        if ($remaining > self::EPSILON) {
            $legacy = $this->batches->legacy($productTypeId);
            $this->batchCache[$legacy->batch_id] = $legacy;
            $allocation[$legacy->batch_id] = round(($allocation[$legacy->batch_id] ?? 0) + $remaining, 4);
            $flags['legacy'] = 'NO STOCK';
        }

        foreach ($allocation as $batchId => $taken) {
            $this->ledger[$batchId][$stageId] = ($this->ledger[$batchId][$stageId] ?? 0) - $taken;
        }

        return [$allocation, implode(', ', $flags)];
    }

    /**
     * 0 = the movement's own order, 1 = free, 2 = another order's stock.
     */
    private function rank(StockBatch $batch, $orderId): int
    {
        if ($batch->order_id === null) {
            return 1;
        }

        return $orderId !== null && (int) $batch->order_id === (int) $orderId ? 0 : 2;
    }

    private function credit(StockBatch $batch, $stageId, float $quantity): void
    {
        $this->batchCache[$batch->batch_id] = $batch;
        $this->ledger[$batch->batch_id][$stageId] = ($this->ledger[$batch->batch_id][$stageId] ?? 0) + $quantity;
    }

    private function batch($batchId): StockBatch
    {
        return $this->batchCache[$batchId] ??= StockBatch::findOrFail($batchId);
    }

    private function stock($stockId): Stock
    {
        return $this->stockCache[$stockId] ??= Stock::findOrFail($stockId);
    }

    private function row(object $event, string $direction, array $allocation, string $flag = ''): void
    {
        $this->rows[] = [
            $event->date,
            $event->kind === 'receipt' ? "{$event->stock_no} (receipt {$event->id})" : "{$event->stock_no} (line {$event->id})",
            $direction,
            $event->pt,
            $event->stage_id,
            round((float) $event->quantity, 4),
            collect($allocation)->map(fn ($qty, $batchId) => $this->batch($batchId)->batch_no.': '.round($qty, 4))->implode(', '),
            $flag,
        ];
    }

    private function report(): void
    {
        $this->info('Product movements');
        $this->table(['Date', 'Record', 'Dir', 'Product', 'Stage', 'Qty', 'Batches', 'Flag'], $this->rows);

        $jobNos = DB::table('orders')->pluck('job_no', 'order_id');
        $batchRows = $this->batches->balances()
            ->map(fn ($row) => [
                $row->batch_no,
                $row->source_type,
                $row->product_type_id,
                $row->order_id ? ($jobNos[$row->order_id] ?? $row->order_id) : 'free',
                $row->stage_id,
                round($row->qty_in, 4),
                round($row->qty_out, 4),
                round($row->balance, 4),
            ])
            ->all();
        $this->info('General stock per batch and stage');
        $this->table(['Batch', 'Source', 'Product', 'Reserved for', 'Stage', 'In', 'Out', 'Balance'], $batchRows);

        foreach ($this->warnings as $warning) {
            $this->warn($warning);
        }
        $flagged = collect($this->rows)->filter(fn ($row) => $row[7] !== '');
        $this->info("Flagged stock-outs: {$flagged->count()} (CROSS-ORDER = used stock of another order, NO STOCK = charged to a LEGACY batch)");
    }

    /**
     * Batch balances per product/stage must equal the pooled /stock figures.
     */
    private function reconcile(StockItemRepository $stockItemRepository): int
    {
        $pooled = $stockItemRepository->pStock()
            ->groupBy(fn ($row) => $row->product_type_id.'_'.$row->stage_id)
            ->map(fn ($rows) => $rows->sum(fn ($row) => $row->stockIn - $row->stockOut));
        $batched = $this->batches->balances()
            ->groupBy(fn ($row) => $row->product_type_id.'_'.$row->stage_id)
            ->map(fn ($rows) => $rows->sum('balance'));

        $mismatches = 0;
        foreach ($pooled->keys()->merge($batched->keys())->unique() as $key) {
            $difference = ($pooled[$key] ?? 0) - ($batched[$key] ?? 0);
            if (abs($difference) > self::EPSILON) {
                $mismatches++;
                $this->error("Product_stage {$key}: /stock ".round($pooled[$key] ?? 0, 4).' vs batches '.round($batched[$key] ?? 0, 4));
            }
        }
        $this->info($mismatches ? "{$mismatches} product/stage total(s) differ." : 'All product/stage totals match /stock.');

        return $mismatches;
    }

    private function check(StockItemRepository $stockItemRepository): int
    {
        $lines = $this->batches->unallocatedLines();
        $this->table(['Line', 'Stock', 'Record', 'Qty', 'Allocated'], $lines->map(fn ($line) => (array) $line)->all());
        $this->info("{$lines->count()} product line(s) with a missing or stale batch allocation.");
        $mismatches = $this->reconcile($stockItemRepository);

        return $lines->isEmpty() && ! $mismatches ? self::SUCCESS : self::FAILURE;
    }
}
