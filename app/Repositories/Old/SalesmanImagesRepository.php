<?php

namespace App\Repositories\Operator;

use App\Models\SalesmanImages;

class SalesmanImagesRepository implements GlobalInterface {
    
    public function all(){
        return SalesmanImages::get();
    }

    public function get($id){
        return SalesmanImages::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        SalesmanImages::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
