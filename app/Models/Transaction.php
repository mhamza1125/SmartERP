<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'transaction_to',
        'transaction_type',
        'transaction_date',
        'order_id',
        'debit',
        'credit',
        'payee_id',
        'payee_bank_id',
        'bank_id',
        'transaction_image',
        'description',
        'created_by',
        'updated_at',
    ];
}
