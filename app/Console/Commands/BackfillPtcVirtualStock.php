<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Repositories\PtcStockRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off data fix for PTC records created before PTC virtual stock existed.
 *
 * 1. PTC stock_items stored "no component" as 0 instead of NULL. General stock only
 *    counts product lines WHERE component_product_type_id IS NULL, so those rows
 *    were invisible to /stock (receipts never added, some master issuances never
 *    deducted). They are normalized to NULL.
 * 2. Product received back on a PTC receiving is PTC virtual stock (ptc_virtual = 1),
 *    so it stays out of general stock until released from the PTC page.
 * 3. PTCs marked Completed whose end stage was never fully received become Closed Early.
 */
class BackfillPtcVirtualStock extends Command
{
    protected $signature = 'ptc:backfill-virtual-stock {--dry-run : Show what would change without writing}';

    protected $description = 'Normalize PTC stock items and move received PTC product into PTC virtual stock';

    public function handle(PtcStockRepository $ptcStockRepository): int
    {
        $ptcStockIds = Stock::where('is_ptc_master', 1)->orWhereNotNull('ptc_id')->pluck('stock_id');

        $zeroComponent = DB::table('stock_items')
            ->whereIn('stock_id', $ptcStockIds)
            ->where('component_product_type_id', 0)
            ->pluck('stock_item_id');

        $receiptProducts = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->whereNotNull('stocks.ptc_id')
            ->where('stocks.stock_type', 1)
            ->where('stocks.stock_status', '!=', Stock::STATUS_PTC_RELEASED)
            ->where('stock_items.material_id', 0)
            ->whereRaw('COALESCE(stock_items.component_product_type_id, 0) = 0')
            ->where('stock_items.ptc_virtual', 0)
            ->pluck('stock_items.stock_item_id');

        $this->info('component_product_type_id 0 -> NULL: '.$zeroComponent->count().' item(s) '.$zeroComponent->implode(', '));
        $this->info('PTC receipt product -> PTC virtual stock: '.$receiptProducts->count().' item(s) '.$receiptProducts->implode(', '));

        if (! $this->option('dry-run')) {
            DB::transaction(function () use ($zeroComponent, $receiptProducts) {
                DB::table('stock_items')->whereIn('stock_item_id', $zeroComponent)->update(['component_product_type_id' => null]);
                DB::table('stock_items')->whereIn('stock_item_id', $receiptProducts)->update(['ptc_virtual' => 1]);
            });
            $this->info('Done.');
        }

        // 3. The old "Close PTC" always stored Completed (7). A PTC is only Completed when
        //    its end stage was fully received; otherwise it was closed early (9).
        foreach (Stock::where('is_ptc_master', 1)->where('stock_status', Stock::STATUS_PTC_COMPLETED)->get() as $ptc) {
            $summary = $ptcStockRepository->stageSummary($ptc, $ptcStockRepository->productStages($ptc->stock_id));
            if (! $summary['can_complete']) {
                $this->info("PTC-{$ptc->stock_no}: end stage not fully received -> Closed Early");
                if (! $this->option('dry-run')) {
                    $ptc->update(['stock_status' => Stock::STATUS_PTC_CLOSED]);
                }
            }
        }

        // Legacy receipts that exceed what was issued cannot be corrected automatically.
        foreach (Stock::where('is_ptc_master', 1)->orderBy('stock_id')->get(['stock_id', 'stock_no']) as $ptc) {
            foreach ($ptcStockRepository->issuanceBalances($ptc->stock_id) as $balance) {
                foreach ($balance['products'] as $line) {
                    if ($line['issued'] > 0 && $line['received'] > $line['issued']) {
                        $this->warn("PTC-{$ptc->stock_no} I{$balance['seq_no']}: received {$line['received']} but only {$line['issued']} issued - review manually.");
                    }
                }
            }
        }

        return self::SUCCESS;
    }
}
