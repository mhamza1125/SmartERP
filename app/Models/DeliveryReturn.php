<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryReturn extends Model
{
    use HasFactory;

    protected $primaryKey = 'delivery_return_id';

    protected $fillable = [
        'return_no',
        'delivery_id',
        'return_date',
        'return_reason',
        'description',
        'created_by',
        'updated_at',
    ];
}
