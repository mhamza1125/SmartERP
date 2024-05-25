<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'order_item_id';

    protected $fillable = [
        'order_id',
        'product_type_id',
        'product_stage_id',
        'quantity',
        'price',
        'price2',
        'total',
        'updated_at',
    ];
}
