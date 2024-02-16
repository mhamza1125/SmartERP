<?php

namespace App\Repositories\Operator;

use App\Models\PackingType;

class PackingTypeRepository implements GlobalInterface {
    
    public function all(){
        return PackingType::get();
    }

    public function category(){
        return PackingType::get();
        // return PackingType::where('pt_pid', 1)->get();
    }

    public function get($id){
        return PackingType::find($id);
    }

    public function store(array $data){
        if(isset($data['pt_id'])){
            $data['pt_pid'] = $data['pt_id'];
            unset($data['pt_id']);
        }
        $data['created_by'] = auth()->id();
        PackingType::create($data);
    }

    public function update($id, array $data) {
        $packingType = PackingType::findOrFail($id);
        $packingType->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
