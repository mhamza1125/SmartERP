<?php

namespace App\Repositories;

use App\Models\Delivery;

class DeliveryRepository implements GlobalInterface {
    
    public function all(){
        return Delivery::where('stocks.stock_status', '3') // Delivery
        ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
        ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
        ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
        ->orderBy('stocks.stock_date', 'desc')
        ->get();
    }

    public function get($id){
        return Delivery::where('deliveries.delivery_id', $id)
        ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
        ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
        ->join('customers', 'customers.customer_id', 'orders.customer_id')
        ->select('*', 'stocks.description')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Delivery::create($data);
        return $store->delivery_id;
    }

    public function update($id, array $data) {
        $update = Delivery::findOrFail($id);
        $update->update($data);
        return $update->delivery_id;
    }

    public function delete($id){}
}
