<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'box_id';

    protected $fillable = [
        'head_id',
        'box_no',
        'name',
        'length',
        'width',
        'height',
        'weight',
        'box_status',
        'description',
        'created_by',
        'updated_at',
    ];
}
