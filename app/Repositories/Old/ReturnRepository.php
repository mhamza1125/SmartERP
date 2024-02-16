<?php

namespace App\Repositories\Operator;

use App\Models\Returns;

class ReturnRepository implements GlobalInterface {
    
    public function all(){
        return Returns::join('order_items', 'order_items.oi_id', '=', 'returns.oi_id')
        ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
        ->join('shops', 'shops.shop_id', '=', 'orders.shop_id')
        ->select('orders.order_id', 'shops.sname', 'orders.order_no', 'return_date')
        ->orderBy('returns.created_at', 'desc')
        ->groupBy('orders.order_id', 'shops.sname', 'orders.order_no', 'return_date')
        ->get();
    }

    
    public function fDate($id1, $id2){
        return Returns::join('order_items', 'order_items.oi_id', '=', 'returns.oi_id')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->join('shops', 'shops.shop_id', '=', 'orders.shop_id')
            ->select('orders.order_id', 'shops.sname', 'orders.order_no', 'return_date')
            ->groupBy('orders.order_id', 'shops.sname', 'orders.order_no', 'return_date')
            ->orderBy('returns.created_at', 'desc')
            ->whereBetween('returns.return_date', [$id1, $id2])
            ->get();
    }
    
    public function returnItems(){
        return Returns::join('order_items', 'order_items.oi_id', '=', 'returns.oi_id')
        ->join('products', 'products.product_id', '=', 'order_items.product_id')
        ->select('order_items.*', 'products.name', 'returns.quantity as rqty')
        ->get();
    }

    public function get($id){
        return Returns::join('orders', 'orders.order_id', '=', 'returns.order_id')
            ->join('products', 'products.product_id', '=', 'returns.product_id')
            ->join('shops', 'shops.shop_id', '=', 'orders.shop_id')
            ->join('order_items', function ($join) {
                $join->on('products.product_id', '=', 'order_items.product_id')
                    ->whereRaw('order_items.order_id = orders.order_id');
            })
            ->where('returns.order_id', '=', $id)
            ->select('returns.return_id', 'shops.sname', 'products.name', 'order_items.price', 'returns.quantity', 'return_date')
            ->distinct()
            ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Returns::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
