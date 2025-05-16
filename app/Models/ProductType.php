<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_type_id';

    protected $fillable = [
        'product_id',
        'size_id',
        'color_id',
        'product_type_status',
        'created_by',
        'updated_at',
    ];
}
    