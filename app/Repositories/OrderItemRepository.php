<?php

namespace App\Repositories;

use App\Models\OrderItem;

class OrderItemRepository implements GlobalInterface {
    
    public function all(){
        return OrderItem::all();
    }

    public function get($id){
        return OrderItem::where('order_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
        ->select('order_items.*', 'product_types.*', 'products.name', 'products.article_no', 'heads.name as hname')
        ->orderBy('product_types.product_id')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = OrderItem::create($data);
        return $store->order_item_id;
    }

    public function update($id, array $data) {}

    public function delete($id){
        OrderItem::where('order_id', $id)->delete();
    }
}
