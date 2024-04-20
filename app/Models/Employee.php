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
        'name',
        'fname',
        'sname',
        'cnic',
        'phone1',
        'phone2',
        'city_id',
        'address',
        'salary',
        'description',
        'joining_date',
        'employee_status',
        'created_by',
        'updated_at',
    ];
}
