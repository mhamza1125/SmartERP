<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMaterial extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_material_id';

    protected $fillable = [
        'product_type_id',
        'material_id',
        'quantity',
        'created_by',
        'updated_at',
    ];
}
