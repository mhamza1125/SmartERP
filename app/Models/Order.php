<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'order_no',
        'job_no',
        'customer_id',
        'order_status',
        'order_date',
        'description',
        'created_by',
        'updated_at',
    ];
}
