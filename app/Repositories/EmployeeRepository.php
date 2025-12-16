<?php

namespace App\Repositories;

use App\Models\Employee;
use Carbon\Carbon;

class EmployeeRepository implements GlobalInterface
{
    public function all()
    {
        return Employee::join('heads as dhead', 'dhead.head_id', '=', 'employees.department_id')
            ->join('heads as chead', 'chead.head_id', '=', 'employees.city_id')
            ->select('employees.*', 'dhead.name as dname', 'chead.name as cname')
            ->orderBy('employees.created_at', 'desc')
            ->get();
    }

    public function salary()
    {
        // Salary Employees
        return Employee::where('employee_type_id', '39')
            ->join('heads as dhead', 'dhead.head_id', '=', 'employees.department_id')
            ->join('heads as chead', 'chead.head_id', '=', 'employees.city_id')
            ->select('employees.*', 'dhead.name as dname', 'chead.name as cname')
            ->orderBy('employees.created_at', 'desc')
            ->get();
    }

    public function wages()
    {
        // Wages Type Employees Used in Stock Issuance
        return Employee::where('employee_type_id', '!=', '39')
            ->join('heads as dhead', 'dhead.head_id', '=', 'employees.department_id')
            ->join('heads as chead', 'chead.head_id', '=', 'employees.city_id')
            ->select('employees.*', 'dhead.name as dname', 'chead.name as cname')
            ->orderBy('employees.created_at', 'desc')
            ->get();
    }

    public function allActive()
    {
        // All active employees (both salary and wage types) for issuance/receiving forms
        return Employee::join('heads as dhead', 'dhead.head_id', '=', 'employees.department_id')
            ->join('heads as chead', 'chead.head_id', '=', 'employees.city_id')
            ->select('employees.*', 'dhead.name as dname', 'chead.name as cname')
            ->orderBy('employees.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        return Employee::where('employee_id', $id)
            ->join('heads as dhead', 'dhead.head_id', '=', 'employees.department_id')
            ->join('heads as ethead', 'ethead.head_id', '=', 'employees.employee_type_id')
            ->join('heads as chead', 'chead.head_id', '=', 'employees.city_id')
            ->select('employees.*', 'dhead.name as dname',
                'ethead.name as etname', 'chead.name as cname')
            ->first();
    }

    public function refNo()
    {
        $lastEmployee = Employee::all()->sortByDesc(function ($employee) {
            return intval(substr($employee->employee_no, 1));
        })->first();
        $lastNumber = $lastEmployee ? intval(substr($lastEmployee->employee_no, 1)) : 0;

        return 'E'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        // $year = Carbon::now()->format('y');
        // $count = Employee::whereYear('created_at', Carbon::now()->year)->count();
        // $threeDigitNumber = str_pad($count+1, 3, '0', STR_PAD_LEFT);
        // return 'E' . $year . $threeDigitNumber;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Employee::create($data);

        return $store->employee_id;
    }

    public function update($id, array $data)
    {
        $update = Employee::findOrFail($id);
        $update->update($data);

        return $update->employee_id;
    }

    public function delete($id)
    {
    }
}
