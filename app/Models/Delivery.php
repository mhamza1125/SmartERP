<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $primaryKey = 'delivery_id';

    protected $fillable = [
        'stock_id',
        'fshipping',
        'tshipping',
        'fport_no',
        'tport_no',
        'delivery_method',
        'delivery_status',
        'created_by',
        'updated_at',
    ];
}
