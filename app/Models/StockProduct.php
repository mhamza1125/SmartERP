<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProduct extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'stock_product_id';

    protected $fillable = [
        'stock_material_id',
        'department_id',
        'emoloyee_id',
        'stock_type',
        'quantity',
        'product_stage',
        'movement_date',
        'created_by',
        'updated_at'
    ];
}
