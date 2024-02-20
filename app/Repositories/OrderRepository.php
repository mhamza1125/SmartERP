<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository implements GlobalInterface {
    
    public function all(){
        return Order::all();
    }

    public function active(){
        return Order::where('order_status', '1')
        ->get();
    }

    public function get($id){
        return Order::where('order_id', $id)
        ->join('customers', 'customer.customer_id', '=', 'order.customer_id')
        ->select('orders.*', 'customer.name')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Order::create($data);
        return $store->order_id;
    }

    public function update($id, array $data) {
        $update = Order::findOrFail($id);
        $update->update($data);
        return $update->order_id;
    }

    public function delete($id){}
}
