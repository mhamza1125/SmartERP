<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'return_id';

    protected $fillable = [
        'return_no',
        'receive_id',
        'return_date',
        'description',
        'created_by',
        'updated_at',
    ];
}
