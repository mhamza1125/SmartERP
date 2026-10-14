<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Repositories\PtcStockRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off data fix for PTCs created before stocks.start_stage_id existed.
 *
 * The start stage is the stock a PTC starts from (e.g. Stage 1 bought in), not a
 * work stage, so the initial issuance is for the stage after it (Stage 1 -> Stage 2).
 * Older PTCs stored the start stage itself as the initial issuance's issue_for, which
 * showed "Stage 1 -> Stage 1" and marked Stage 1 as the worked stage.
 *
 * For each such PTC master: start_stage_id = its old issue_for, issue_for = the next
 * stage (not past the end stage), and a current stage still at the start stage moves
 * to that stage. Only stage labels change - no stock quantity uses issue_for.
 */
class BackfillPtcStartStage extends Command
{
    protected $signature = 'ptc:backfill-start-stage {--dry-run : Show what would change without writing}';

    protected $description = 'Record the start stage of older PTCs and issue their initial issuance for the stage after it';

    public function handle(PtcStockRepository $ptcStockRepository): int
    {
        $changes = [];
        foreach (Stock::where('is_ptc_master', 1)->whereNull('start_stage_id')->orderBy('stock_id')->get() as $ptc) {
            $stages = $ptcStockRepository->productStages($ptc->stock_id)->values();
            $stageIds = $stages->pluck('head_id')->map(fn ($id) => (int) $id);
            $startIndex = $stageIds->search((int) $ptc->issue_for);
            $endIndex = $stageIds->search((int) $ptcStockRepository->endStageId($ptc, $stages));
            $endIndex = $endIndex === false ? $stageIds->count() - 1 : $endIndex;

            $update = ['start_stage_id' => $ptc->issue_for];
            if ($startIndex !== false && $startIndex < $endIndex) {
                $update['issue_for'] = $stageIds[$startIndex + 1];
                if ((int) $ptc->current_stage_id === (int) $ptc->issue_for) {
                    $update['current_stage_id'] = $stageIds[$startIndex + 1];
                    $update['next_stage_id'] = $startIndex + 1 < $endIndex ? $stageIds[$startIndex + 2] : null;
                }
            }

            $names = $stages->pluck('name', 'head_id');
            $this->line(sprintf(
                'PTC-%s: start %s | initial issuance for %s -> %s | current %s -> %s',
                $ptc->stock_no,
                $names[$ptc->issue_for] ?? $ptc->issue_for,
                $names[$ptc->issue_for] ?? $ptc->issue_for,
                $names[$update['issue_for'] ?? $ptc->issue_for] ?? '-',
                $names[$ptc->current_stage_id] ?? $ptc->current_stage_id,
                $names[$update['current_stage_id'] ?? $ptc->current_stage_id] ?? '-'
            ));
            $changes[$ptc->stock_id] = $update;
        }

        $this->info(count($changes).' PTC(s) to update.');

        if (! $this->option('dry-run') && $changes) {
            DB::transaction(function () use ($changes) {
                foreach ($changes as $stockId => $update) {
                    Stock::where('stock_id', $stockId)->update($update);
                }
            });
            $this->info('Done.');
        }

        return self::SUCCESS;
    }
}
