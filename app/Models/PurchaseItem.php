<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'purchase_item_id';

    protected $fillable = [
        'purchase_id',
        'product_type_id',
        'product_stage_id',
        'material_id',
        'quantity',
        'price',
        'total',
        'created_by',
        'updated_at',
    ];
}
