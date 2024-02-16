<?php

namespace App\Repositories\Operator;

use App\Models\Leakage;

class LeakageRepository implements GlobalInterface {
    
    public function all(){
        return Leakage::join('order_items', 'order_items.oi_id', '=', 'leakages.oi_id')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->join('products', 'products.product_id', '=', 'order_items.product_id')
            ->select('leakages.*', 'products.name', 'orders.order_no')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function fDate($id1, $id2){
        return Leakage::join('order_items', 'order_items.oi_id', '=', 'leakages.oi_id')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->join('products', 'products.product_id', '=', 'order_items.product_id')
            ->select('leakages.*', 'products.name', 'orders.order_no')
            ->orderBy('created_at', 'desc')
            ->whereBetween('leakages.leakage_date', [$id1, $id2])
            ->get();
    }

    public function get($id){
        // return Area::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Leakage::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
