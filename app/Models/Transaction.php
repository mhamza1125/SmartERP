<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_id';

    // advance_type of employee Give Loan / Receive Loan entries
    const ADVANCE_TEMPORARY = 'temporary'; // salary taken early in the month; deducted in that month's payroll
    const ADVANCE_FIXED = 'fixed';         // long-term loan repaid in monthly instalments

    protected $fillable = [
        'transaction_to',
        'transaction_type',
        'transaction_date',
        'order_id',
        'debit',
        'credit',
        'cc_amount',
        'fb_charges',
        'db_charges',
        'ledger_flag',
        'advance_type',
        'payee_id',
        'payee_bank_id',
        'bank_id',
        'transfer_pair_id',
        'transaction_image',
        'description',
        'created_by',
        'updated_at',
    ];
}
