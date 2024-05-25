<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBox extends Model
{
    use HasFactory;

    protected $primaryKey = 'dbox_id';

    protected $fillable = [
        'delivery_id',
        'vehicle_no',
        'rowQty',
        'totalQty',
        'created_by',
        'updated_at',
    ];
}
