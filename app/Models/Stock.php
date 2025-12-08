<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $primaryKey = 'stock_id';

    // Stock Status Constants
    const STATUS_NOT_RECEIVED = 0;
    const STATUS_COMPLETELY_RECEIVED = 1;
    const STATUS_PARTIALLY_RECEIVED = 2;
    const STATUS_DELIVERY = 3;
    const STATUS_MACHINE_MATERIAL = 4;
    const STATUS_MATERIAL_PROCESSING = 5;
    const STATUS_PTC_IN_PROGRESS = 6;
    const STATUS_PTC_COMPLETED = 7;

    protected $fillable = [
        'issue_id',
        'ptc_id',
        'current_stage_id',
        'next_stage_id',
        'is_ptc_master',
        'stock_no',
        'issue_for',
        'order_id',
        'machine_id',
        'table_name',
        'employee_id',
        'stock_type',
        'stock_date',
        'stock_status',
        'description',
        'created_by',
        'updated_at',
    ];
}
