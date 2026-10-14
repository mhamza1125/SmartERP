<?php

namespace App\Observers;

use App\Models\Stock;
use App\Repositories\StockBatchRepository;

/**
 * Batches made by an issuance or PTC follow that record's order.
 */
class StockObserver
{
    public function __construct(protected StockBatchRepository $stockBatchRepository) {}

    public function updated(Stock $stock): void
    {
        if ($stock->wasChanged('order_id')) {
            $this->stockBatchRepository->syncSourceOrder($stock);
        }
    }
}
