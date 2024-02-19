<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMaterial extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'store_material_id';

    protected $fillable = [
        'store_id',
        'receive_material_id',
        'quantity',
        'created_by',
        'updated_at',
    ];
}
