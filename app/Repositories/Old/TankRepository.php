<?php

namespace App\Repositories\Operator;

use App\Models\Tank;

class TankRepository implements GlobalInterface {
    
    public function all(){
        return Tank::all();
    }

    public function get($id){
        return Tank::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Tank::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
