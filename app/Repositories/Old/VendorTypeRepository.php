<?php

namespace App\Repositories\Operator;

use App\Models\VendorType;

class VendorTypeRepository implements GlobalInterface {
    
    public function all(){
        return VendorType::get();
    }

    public function get($id){
        // return WeighBridge::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        VendorType::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
