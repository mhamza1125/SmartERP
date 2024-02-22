<?php

namespace App\Repositories;

use App\Models\PurchaseItem;

class PurchaseItemRepository implements GlobalInterface {
    
    public function all(){
        return PurchaseItem::all();
    }

    public function get($id){
        return PurchaseItem::where('purchase_id', $id)
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->select('purchase_items.*', 'materials.name')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = PurchaseItem::create($data);
        return $store->purchase_item_id;
    }

    public function update($id, array $data) {}

    public function delete($id){
        PurchaseItem::where('purchase_id', $id)->delete();
    }
}
