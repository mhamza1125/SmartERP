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
        'material_type_id',
        'vendor_id',
        'name',
        'unit_id',
        'cprice',
        'location',
        'description',
        'created_by',
        'updated_at',
    ];
}
