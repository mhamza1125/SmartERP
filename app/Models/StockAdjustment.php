<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * A stock adjustment: a stocks row with table_name Stock::TABLE_ADJUSTMENT (stock_type 1 = increase,
 * 2 = decrease, stock_status Stock::STATUS_ADJUSTMENT) whose stock_items are the adjusted materials /
 * products. Used for listing and as the StockAdjustmentPolicy target; documents are written as Stock
 * through StockAdjustmentRepository (so the Stock / StockItem observers run).
 */
class StockAdjustment extends Stock
{
    protected $table = 'stocks';

    protected static function booted(): void
    {
        static::addGlobalScope('adjustment', fn (Builder $query) => $query->where('stocks.table_name', Stock::TABLE_ADJUSTMENT));
    }
}
