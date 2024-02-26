<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnMaterial extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'return_material_id';

    protected $fillable = [
        'receive_material_id',
        'quantity',
        'return_date',
        'remarks',
        'created_by',
        'updated_at',
    ];
}
