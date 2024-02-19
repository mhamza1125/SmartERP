<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'material_id';

    protected $fillable = [
        'material_no',
        'head_id',
        'name',
        'unit_id',
        'description',
        'created_by',
        'updated_at',
    ];
}
