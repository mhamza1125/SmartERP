<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnMaterial extends Model
{
    use HasFactory;

    protected $primaryKey = 'return_material_id';

    protected $fillable = [
        'return_id',
        'receive_material_id',
        'quantity',
        'remarks',
        'created_by',
        'updated_at',
    ];
}
