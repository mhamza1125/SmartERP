<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBox extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'product_box_id';

    protected $fillable = [
        'product_type_id',
        'box_id',
        'quantity',
        'created_by',
        'updated_at',
    ];
}
