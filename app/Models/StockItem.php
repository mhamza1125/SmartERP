<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'stock_item_id';

    protected $fillable = [
        'stock_id',
        'product_type_id',
        'material_id',
        'quantity',
        'stage_id',
        'work_logs',
        'work_wages',
        'created_by',
        'updated_at',
    ];
}
