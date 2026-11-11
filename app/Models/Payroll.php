<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One month's payroll: the period settings its salaries were calculated with.
 * Employee breakdowns are PayrollItem rows (payroll_id).
 */
class Payroll extends Model
{
    protected $primaryKey = 'payroll_id';

    protected $fillable = [
        'month',
        'total_working_days',
        'hours_per_day',
        'overtime_multiplier',
        'late_multiplier',
        'payment_date',
        'bank_id',
        'description',
        'created_by',
    ];
}
