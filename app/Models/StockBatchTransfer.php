<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Quantity of one stage moved from one batch to another (a split). Ownership only:
 * no stock moves, so product/stage totals stay the same.
 */
class StockBatchTransfer extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'transfer_id';

    protected $fillable = [
        'from_batch_id',
        'to_batch_id',
        'stage_id',
        'quantity',
        'transfer_date',
        'description',
        'created_by',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['from_batch_id', 'to_batch_id', 'stage_id', 'quantity', 'description']);
    }
}
