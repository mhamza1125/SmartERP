<?php

namespace App\Repositories;

use App\Models\Head;
use App\Models\ProductType;

class ProductTypeRepository implements GlobalInterface {
    
    public function all(){}

    public function get($id){
        return ProductType::where('product_id', $id)
        ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ProductType::create($data);
        return $store->product_variant_id;
    }

    public function update($id, array $data) {   
        ProductType::where('product_id', $id)->update(['product_type_status' => 0]);
        foreach($data as $size){
            $update = ProductType::where('product_id', $id)->where('size_id', $size)->first();
            if($update){
                $update->update(['product_type_status' => 1]);
            }else{
                $store = ['product_id' => $id, 'size_id' => $size, 'created_by' => auth()->id()];
                $store = ProductType::create($store);
            }
        }
    }

    public function delete($id){}
}
