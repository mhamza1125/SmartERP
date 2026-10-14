<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A lot of one product type with a known origin. Product stock lines
 * (stock_items, receive_materials) carry batch_id; order_id reserves the
 * batch for that order, NULL = free stock.
 */
class StockBatch extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'batch_id';

    const SOURCE_PTC = 'ptc';

    const SOURCE_PURCHASE = 'purchase';

    const SOURCE_ISSUANCE = 'issuance';

    const SOURCE_OPENING = 'opening';

    const SOURCE_SPLIT = 'split';

    const SOURCE_LEGACY = 'legacy';

    protected $fillable = [
        'batch_no',
        'product_type_id',
        'source_type',
        'source_id',
        'order_id',
        'parent_batch_id',
        'description',
        'created_by',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['batch_no', 'order_id', 'description'])->logOnlyDirty();
    }
}
