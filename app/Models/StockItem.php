<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'store_item_id';

    protected $fillable = [
        'store_id',
        'product_item_id',
        'material_id',
        'quantity',
        'product_stage',
        'created_by',
        'updated_at',
    ];
}
