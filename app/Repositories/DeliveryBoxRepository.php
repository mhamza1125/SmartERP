<?php

namespace App\Repositories;

use App\Models\DeliveryBox;

class DeliveryBoxRepository implements GlobalInterface {
    
    public function all(){
        return DeliveryBox::all();
    }

    public function get($id){
        return DeliveryBox::where('delivery_id', $id)->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = DeliveryBox::create($data);
    }

    public function update($id, array $data) {}

    public function delete($id){
        DeliveryBox::where('delivery_id', $id)->delete();
    }
}