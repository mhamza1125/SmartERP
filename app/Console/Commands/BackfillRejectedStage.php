<?php

namespace App\Console\Commands;

use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off: records the rejected-at stage on PTC receipt lines received into Rejection before it was
 * tracked. A receipt is against an issuance, and the issuance's Issue For stage is the stage the
 * pieces were being worked at when they were rejected (the receipt's current stage for older rows).
 * Lines that already have a stage are left alone, so it is safe to run again.
 * Releases of rejection stock made before this stay "not recorded".
 */
class BackfillRejectedStage extends Command
{
    protected $signature = 'stock:backfill-rejected-stage {--dry-run : Only count the lines}';

    protected $description = 'Record the rejected-at stage on older PTC rejection receipt lines';

    public function handle(): int
    {
        $query = DB::table('stock_items')
            ->join('stocks as receipt', 'receipt.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('stocks as issuance', 'issuance.stock_id', '=', 'receipt.issue_id')
            ->where('stock_items.stage_id', Stock::STAGE_REJECTED)
            ->whereNull('stock_items.rejected_stage_id')
            ->where('receipt.stock_type', 1)
            ->where('receipt.ptc_id', '>', 0)
            ->where('receipt.stock_status', '!=', Stock::STATUS_PTC_RELEASED)
            ->whereRaw('COALESCE(issuance.issue_for, receipt.current_stage_id, 0) > 0');

        $count = (clone $query)->count();
        if (! $this->option('dry-run') && $count) {
            (clone $query)->update(['stock_items.rejected_stage_id' => DB::raw('COALESCE(issuance.issue_for, receipt.current_stage_id)')]);
        }

        $this->info(($this->option('dry-run') ? 'Would update ' : 'Updated ').$count.' rejection line(s).');

        return self::SUCCESS;
    }
}
