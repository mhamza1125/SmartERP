<?php

namespace App\Repositories;

use App\Models\ProductBox;

class ProductBoxRepository implements GlobalInterface {
    
    public function all(){
        return ProductBox::all();
    }

    public function get($id){
        return ProductBox::where('product_type_id', $id)
        ->join('boxes', 'boxes.box_id', '=', 'product_boxes.box_id')
        ->join('heads', 'heads.head_id', '=', 'boxes.head_id')
        ->select('*', 'heads.name as hname')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ProductBox::create($data);
        return $store->product_box_id;
    }

    public function update($id, array $data) {
        $update = ProductBox::where('product_type_id', $id)->firstOrFail();
        $update->update($data);
        return $update->product_box_id;
    }

    public function delete($id){}
}
