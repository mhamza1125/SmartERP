<?php

namespace App\Repositories\Operator;

use App\Models\Salary;

class SalaryRepository implements GlobalInterface {
    
    public function all(){
        return Salary::get();
    }

    public function get($id){
        // return WeighBridge::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Salary::create($data);
    }

    public function update($id, array $data) {
        $salary = Salary::where('status', 1)->findOrFail($id);
        $salary->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
