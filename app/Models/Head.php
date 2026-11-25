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
        // Department heads (type 3): working hours of the department
        'work_start_time',
        'work_end_time',
        'break_minutes', // department break, deducted from Work In -> Work Out for payroll hours
        'created_by',
        'updated_at',
    ];
}
