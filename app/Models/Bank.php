<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $primaryKey = 'bank_id';

    protected $fillable = [
        'bank_holder',
        'banker_id',
        'head_id',
        'account',
        'account_title',
        'created_by',
        'updated_at',
    ];
}
