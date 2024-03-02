<?php

namespace App\Repositories;

use App\Models\ReceiveMaterial;

class ReceiveMaterialRepository implements GlobalInterface {
    
    public function all(){
        return ReceiveMaterial::all();
    }

    public function get($id){
        return ReceiveMaterial::where('receive_materials.receive_id', $id)
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads', 'heads.head_id', '=' ,'materials.unit_id')
        ->select('material_no', 'materials.name', 'heads.name as hname', 'receive_materials.*')
        ->get();
    }

    public function stock(){
        return ReceiveMaterial::leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->select('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name as mtname', 'uhead.name as uname')
        ->selectRaw('SUM(receive_materials.quantity) as total_received')
        ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
        ->where('receive_materials.inspection_status', '2')
        ->groupBy('materials.material_id')
        ->orderBy('materials.name')
        ->get();
    }

    public function rSum($id){
        // Used by Purchase
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads', 'heads.head_id', '=' ,'materials.unit_id')
        ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->groupBy('receive_materials.purchase_item_id')
        ->selectRaw('purchase_items.quantity, materials.material_no, materials.name,
            heads.name as hname, orders.job_no, SUM(receive_materials.quantity) AS rqty')
        ->get();
    }

    public function rAll($id){
        // Used by Purchase
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
        ->join('receives', 'receives.receive_id', 'receive_materials.receive_id')
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads', 'heads.head_id', '=' ,'materials.unit_id')
        ->select('purchase_items.quantity', 'materials.material_no', 'materials.name',
            'heads.name as hname', 'receive_materials.quantity as rqty', 'receive_materials.created_at',
            'receive_material_id', 'receives.receive_no')
        ->get();
    }

    public function times($id){
        // Used By Purchase
        return ReceiveMaterial::where('receives.purchase_id', $id)
        ->join('receives', 'receives.receive_id', '=', 'receive_materials.receive_id')
        ->groupBy('receives.receive_id')
        ->select('receive_no')->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ReceiveMaterial::create($data);
        return $store->receive_material_id;
    }

    public function update($id, array $data) {
        $existingItems = ReceiveMaterial::where('receive_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem->purchase_item_id, $data['purchase_item_id'])) {
                $existingItem->delete();
            }
        }
        
        foreach ($data['quantity'] as $key => $quantity) {
            $pid = $data['purchase_item_id'][$key] ?? null;
            $status = $data['inspection_status'][$key] ?? null;
            
            $receiveMaterial = [
                'receive_id' => $id,
                'purchase_item_id' => $pid,
                'quantity' => $quantity,
                'inspection_status' => $status,
            ];
            $receive = ReceiveMaterial::where('receive_id', $id)
                ->where('purchase_item_id', $pid)
                ->first();
            if ($receive) {
                $receive->update($receiveMaterial);
            } else {
                $receiveMaterial['created_by'] = auth()->id();
                $store = ReceiveMaterial::create($receiveMaterial);
            }
        }
    }

    public function delete($id){
        ReceiveMaterial::where('receive_id', $id)->delete();
    }
}
