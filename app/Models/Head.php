<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Head extends Model
{
    use HasFactory;

    protected $primaryKey = 'head_id';

    protected $fillable = [
        'head_type_id',
        'name',
        'head_status',
        'created_by',
        'updated_at',
    ];
}
