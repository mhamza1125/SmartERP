<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Repositories\ImageRepository;
use App\Models\Employee;
use Carbon\Carbon;

class EmployeeRepository implements GlobalInterface
{
    use ChecksReferences;

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

    /**
     * An employee can be deleted only while no payroll, issuance, machine or ledger entry uses
     * them; their own opening balance entry and images are deleted with them.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $employee = Employee::findOrFail($id);
        $impact = DeletionImpact::for('Employee '.$employee->employee_no.' ('.$employee->name.')');
        $employeeStock = fn ($q) => $q->where(fn ($w) => $w->where('table_name', 'employee')->orWhereNull('table_name'));
        $ledger = fn ($q) => $q->where('transaction_to', 'employee')->where('transaction_type', '!=', 'openingBalance');
        $opening = fn ($q) => $q->where('transaction_to', 'employee')->where('transaction_type', 'openingBalance');

        $impact->blockIf($this->refCount('payroll_items', 'employee_id', $id), 'monthly payroll entry', 'monthly payroll entries');
        $impact->blockIf($this->refCount('stocks', 'employee_id', $id, $employeeStock), 'issuance / receiving / PTC record', 'issuance / receiving / PTC records', $this->refExamples('stocks', 'employee_id', $id, 'stock_no', $employeeStock));
        $impact->blockIf($this->refCount('machines', 'employee_id', $id), 'machine', null, $this->refExamples('machines', 'employee_id', $id, 'machine_no'));
        $impact->blockIf($this->refCount('salaries', 'employee_id', $id), 'salary record');
        $impact->blockIf($this->refCount('transactions', 'payee_id', $id, $ledger), 'ledger transaction (salary / loan / payment)', 'ledger transactions (salaries / loans / payments)');
        $impact->blockIf($this->refCount('banks', 'banker_id', $id, fn ($q) => $q->where('bank_holder', 'employee')), 'bank account');

        $impact->cascadeIf($this->refCount('transactions', 'payee_id', $id, $opening), 'opening balance entry', 'opening balance entries');
        $impact->cascadeIf(app(ImageRepository::class)->countFor('employees', $id), 'image');

        return $impact;
    }

    public function delete($id)
    {
        DB::table('transactions')->where('transaction_to', 'employee')->where('transaction_type', 'openingBalance')->where('payee_id', $id)->delete();
        app(ImageRepository::class)->deleteFor('employees', $id);

        return Employee::findOrFail($id)->delete();
    }
}
