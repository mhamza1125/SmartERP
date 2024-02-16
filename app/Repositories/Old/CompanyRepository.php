<?php

namespace App\Repositories\Operator;

use App\Models\Company;

class CompanyRepository implements GlobalInterface {
    
    public function all(){
    }

    public function get($id){
        return Company::where('id', $id)
        ->get();
    }

    public function store(array $data){
    }

    public function update($id, array $data) {
        $update = Company::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
