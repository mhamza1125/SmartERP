<?php

namespace App\Repositories\Operator;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemRepository {
    
    public function all(){
        return OrderItem::join('products', 'products.product_id', '=', 'order_items.product_id')
            ->get();
    }

    public function get($id){
        return OrderItem::join('products', 'products.product_id', '=', 'order_items.product_id')
        ->select('order_items.quantity as qty', 'order_items.price', 'products.name as pname', 'order_items.product_id', 'order_items.cost_id', 'products.quantity as pliter')
        ->where('order_items.order_id', $id)
        ->get();
    }

    public function getStock($id){
        return OrderItem::select('cost_id', 'product_id', 'quantity')
        ->where('order_items.order_id', $id)
        ->get();
    }

    public function returnItems($id) {
        return OrderItem::where('oi_id', $id)
            ->select('price', 'product_id', 'cost_id')
            ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        OrderItem::create($data);
    }

    public function updateOrder($id, $pid, $bid, array $data) {
        $oi = OrderItem::where('order_id', $id)
        ->where('product_id', $pid)
        ->where('cost_id', $bid);
        $oi->update($data);
    }   

    public function delete($id){
        OrderItem::where('order_id', $id)->delete();
    }
}
