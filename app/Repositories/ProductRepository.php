<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository implements GlobalInterface {
    
    public function all(){
        return Product::join('categories', 'categories.category_id', '=', 'products.category_id')
        ->select('products.*', 'categories.name as cname')
        ->get();
    }

    public function active(){
        return Product::join('product_types', 'product_types.product_id', 'products.product_id')
        ->join('heads', 'heads.head_id', 'product_types.size_id')
        ->where('products.product_status', '1')
        ->where('product_types.product_type_status', '1')
        ->where('heads.head_type_id', '1')
        ->select('products.*', 'product_types.product_type_id', 'heads.name as hname')
        ->get();
    }

    public function get($id){
        return Product::where('product_id', $id)->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Product::create($data);
        return $store->product_id;
    }

    public function update($id, array $data) {
        $update = Product::findOrFail($id);
        $update->update($data);
        return $update->product_id;
    }

    public function delete($id){}
}
