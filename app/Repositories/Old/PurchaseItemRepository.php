<?php

namespace App\Repositories\Operator;

use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseItemRepository implements GlobalInterface {
    
    public function all(){
        return PurchaseItem::get();
    }

    public function get($id){
        $data = PurchaseItem::join('products', 'products.product_id', '=', 'purchase_items.product_id')
        // ->join('packing_types', 'packing_types.pt_id', '=', 'products.pt_id')
        ->select('purchase_items.quantity as qty', 'purchase_items.price', 'products.name as pname')
        ->where('purchase_items.purchase_id', $id)
        ->get();
        return $data;
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        PurchaseItem::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
