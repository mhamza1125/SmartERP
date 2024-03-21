<?php

namespace App\Repositories;

use App\Models\ProductCost;

class ProductCostRepository implements GlobalInterface {
    
    public function all(){
        return ProductCost::join('customers', 'customers.customer_id', '=', 'orders.customer_id')
        ->orderBy('orders.created_at', 'desc')->get();
    }

    public function active(){
        return ProductCost::where('order_status', '1')
        ->get();
    }

    public function get($id){
        return ProductCost::where('product_costs.product_type_id', $id)
        ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
        ->select('product_costs.*', 'heads.name as hname')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ProductCost::create($data);
        return $store->product_cost_id;
    }

    public function update($id, array $data) {
        $existingItems = ProductCost::where('product_type_id', $id)->get();
        // Assuming $data['head_id'] are arrays of IDs.
        foreach ($existingItems as $existingItem) {
            // Check if it does not exist in the provided data
            if (!in_array($existingItem->head_id, $data['head_id'])) {
                ProductCost::where('product_cost_id', $existingItem->product_cost_id)->delete();
            }
        }
        
        // Assuming you have an array of product_type_ids and head_ids indexed similarly
        foreach ($data['amount'] as $key => $amount) {
            // Assuming you have these arrays in your $data and they are indexed accordingly
            $hid = $data['head_id'][$key] ?? null;
            // Validate that $hid is not null
            if ($hid !== null) {
                $productCostItem = [
                    'product_type_id' => $id,
                    'head_id' => $hid,
                    'amount' => $amount,
                ];
                
                $productCost = ProductCost::where('product_type_id', $id)
                    ->where('head_id', $hid)
                    ->first();
                if ($productCost) {
                    $productCost->update($productCostItem);
                } else {
                    $productCostItem['created_by'] = auth()->id();
                    ProductCost::create($productCostItem);
                }
            }
        }
    }

    public function delete($id){}
}
