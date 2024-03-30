<?php

namespace App\Repositories;

use App\Models\Head;
use App\Models\ProductType;

class ProductTypeRepository implements GlobalInterface {
    
    public function all(){}

    public function get($id){
        return ProductType::where('product_type_id', $id)
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=' ,'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=' ,'products.unit_id')
        ->select('products.*', 'shead.name as hname', 'uhead.name as uname', 'product_type_id')
        ->first();
    }

    public function active($id){
        return ProductType::where('product_id', $id)
        ->where('product_types.product_type_status', '=', '1')
        ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ProductType::create($data);
        return $store->product_type_id;
    }

    public function update($id, array $data) {   
        // Used By Product for Updating sizes
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

    public function updatePro($id, array $data) {
        // Used by Product Material for Updating product_id
        $update = ProductType::findOrFail($id);
        $update->update($data);
        return $update->product_type_id;
    }

    public function delete($id){}
}
