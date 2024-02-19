<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'product_variant_id';

    protected $fillable = [
        'product_id',
        'size_id',
        'color_id',
        'updated_at',
    ];
}
