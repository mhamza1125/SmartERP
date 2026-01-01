<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'employee_id';

    protected $fillable = [
        'employee_no',
        'department_id',
        'employee_type_id',
        'attendance_id',
        'name',
        'fname',
        'sname',
        'cnic',
        'phone1',
        'phone2',
        'city_id',
        'address',
        'designation',
        'salary',
        'description',
        'joining_date',
        'employee_status',
        'created_by',
        'updated_at',
        // New personal and professional fields
        'marital_status',
        'siblings_count',
        'children_details',
        'education',
        'employment_history',
        'additional_skills',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'children_details' => 'array',
        'education' => 'array',
        'employment_history' => 'array',
    ];
}
