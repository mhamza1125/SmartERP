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
        'stage_id',          // product returned from this stage (NULL = the purchased stage)
        'rejected_stage_id', // when returned from Rejection: the stage it was rejected at
        'remarks',
        'created_by',
        'updated_at',
    ];
}
