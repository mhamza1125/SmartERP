<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'purchase_id';

    protected $fillable = [
        'purchase_no',
        'order_id',
        'vendor_id',
        'description',
        'purchase_date',
        'created_by',
        'updated_at',
    ];
}
