<?php

namespace App\Repositories;

use App\Models\PurchaseItem;

class PurchaseItemRepository implements GlobalInterface {
    
    public function all(){
        return PurchaseItem::all();
    }

    public function get($id){
        return PurchaseItem::where('purchase_item_id', $id)
        ->select('PurchaseItems.*', 'orders.job_no', 'vendors.fname')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = PurchaseItem::create($data);
        return $store->purchase_item_id;
    }

    public function update($id, array $data) {
        $update = PurchaseItem::findOrFail($id);
        $update->update($data);
        return $update->purchase_item_id;
    }

    public function delete($id){}
}
