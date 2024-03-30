<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCost extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'product_cost_id';

    protected $fillable = [
        'product_type_id',
        'table_id',
        'table_name',
        'head_id',
        'amount',
        'created_by',
        'updated_at',
    ];
}
