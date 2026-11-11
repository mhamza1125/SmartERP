<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One employee's salary in a payroll: attendance, overtime, late deduction, advances
 * and the final payable amount, plus the transactions the payroll posted for it.
 */
class PayrollItem extends Model
{
    protected $primaryKey = 'payroll_item_id';

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'designation',
        'gross_salary',
        'total_working_days',
        'actual_working_days',
        'salary_for_days',
        'overtime_hours',
        'overtime_rate',
        'overtime_amount',
        'late_minutes',
        'late_deduction',
        'temp_advance',
        'fixed_advance',
        'fixed_advance_deduction',
        'remaining_fixed_advance',
        'net_salary',
        'salary_transaction_id',
        'temp_deduction_transaction_id',
        'fixed_deduction_transaction_id',
    ];
}
