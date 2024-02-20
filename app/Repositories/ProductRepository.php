<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository implements GlobalInterface {
    
    public function all(){
        return Product::join('categories', 'categories.category_id', '=', 'products.category_id')
        ->select('products.*', 'categories.name as cname')
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
