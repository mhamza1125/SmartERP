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
        ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
        ->select('purchase_items.*', 'materials.name', 'materials.material_no', 'heads.name as hname')
        ->get();
    }

    public function receive($id){
        // Receive Purchase Items
        return PurchaseItem::where('purchase_id', $id)
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
        ->select('purchase_items.*', 'materials.name')
        ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received')
        ->groupBy('purchase_items.purchase_item_id')
        ->get();
    }

    public function editReceive($pid, $rid){
        return PurchaseItem::where('purchase_id', $pid)
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->leftJoin('receive_materials', function ($join) use ($rid) {
            $join->on('receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                 ->where('receive_materials.receive_id', '!=', $rid);
        })
        ->select('purchase_items.*', 'materials.name')
        ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received')
        ->groupBy('purchase_items.purchase_item_id')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = PurchaseItem::create($data);
        return $store->purchase_item_id;
    }

    public function update($id, array $data) {
        $existingItems = PurchaseItem::where('purchase_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem->material_id, $data['material_id'])) {
                $existingItem->delete();
            }
        }
        foreach ($data['quantity'] as $key => $quantity) {
            $price = $data['price'][$key] ?? null;
            $material = $data['material_id'][$key] ?? null;
            $total = $price * $quantity;
            $purchaseItem = [
                'purchase_id' => $id,
                'material_id' => $material,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $purchase = PurchaseItem::where('purchase_id', $id)
                ->where('material_id', $material)
                ->first();
            if ($purchase) {
                $purchase->update($purchaseItem);
            } else {
                $purchaseItem['created_by'] = auth()->id();
                $store = PurchaseItem::create($purchaseItem);
            }
        }
    }

    public function delete($id){}
}
