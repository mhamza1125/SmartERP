<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receive extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'receive_id';

    protected $fillable = [
        'receive_no',
        'purchase_id',
        'receive_date',
        'description',
        'created_by',
        'updated_at',
    ];
}
