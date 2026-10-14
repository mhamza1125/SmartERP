<?php

namespace App\Observers;

use App\Models\StockItem;
use App\Repositories\StockBatchRepository;

/**
 * Every product stock-in (and PTC stage transfer) gets its batch, whichever screen wrote it.
 * Allocations are removed with their line by the stock_item_batches foreign key.
 */
class StockItemObserver
{
    public function __construct(protected StockBatchRepository $stockBatchRepository) {}

    public function saved(StockItem $item): void
    {
        $this->stockBatchRepository->syncLine($item);
    }
}
