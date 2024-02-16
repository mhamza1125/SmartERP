<?php

namespace App\Repositories\Operator;

use App\Models\City;

class CityRepository implements GlobalInterface {
    
    public function all(){
        return City::get();
    }

    public function get($id){
        // return WeighBridge::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        City::create($data);
    }

    public function update($id, array $data) {
        $update = City::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
