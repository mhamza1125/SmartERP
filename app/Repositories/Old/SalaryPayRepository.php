<?php

namespace App\Repositories\Operator;

use App\Models\SalaryPay;

class SalaryPayRepository implements GlobalInterface {
    
    public function all(){
        return SalaryPay::join('salesman', 'salesman.salesman_id', '=', 'salary_pay.salesman_id')
        ->select('salary_pay.*', 'salesman.salesman_no', 'salesman.name')
        ->orderBy('salary_pay.created_at', 'desc')
        ->get();
    }

    public function get($id){
        // return SalaryPay::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        SalaryPay::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
