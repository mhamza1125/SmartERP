<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryReturnItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'delivery_return_item_id';

    protected $fillable = [
        'delivery_return_id',
        'stock_item_id',
        'quantity',
        'reason',
        'created_by',
        'updated_at',
    ];
}
