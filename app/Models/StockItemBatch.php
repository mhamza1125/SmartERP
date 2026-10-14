<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Quantity of a product stock line moved in / out of one batch.
 * SUM(quantity) per stock_item_id = stock_items.quantity.
 */
class StockItemBatch extends Model
{
    use HasFactory;

    protected $primaryKey = 'stock_item_batch_id';

    protected $fillable = [
        'stock_item_id',
        'batch_id',
        'quantity',
        'updated_at',
    ];
}
