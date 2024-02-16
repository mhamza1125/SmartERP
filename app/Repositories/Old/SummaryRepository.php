<?php

namespace App\Repositories\Operator;

use App\Models\Transaction;

class SummaryRepository implements GlobalInterface {
    
    public function all(){      
        return Transaction::join('orders', 'orders.order_id', '=', 'transactions.order_id')
            ->join('shops', 'shops.shop_id', '=', 'transactions.shop_id')
            ->join('salesman', 'salesman.salesman_id', 'transactions.salesman_id')
            ->orderBy('transactions.shop_id')
            ->get();
    }

    public function daily(){
        // Daily Summary
    }

    public function get($id){
        // Not Used
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Transaction::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
