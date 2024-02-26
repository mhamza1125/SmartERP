<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository implements GlobalInterface {
    
    public function all(){
        return Order::join('customers', 'customers.customer_id', '=', 'orders.customer_id')
        ->orderBy('orders.created_at', 'desc')->get();
    }

    public function active(){
        return Order::where('order_status', '1')
        ->get();
    }

    public function get($id){
        return Order::where('order_id', $id)
        ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
        ->select('orders.*', 'customers.*')
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
