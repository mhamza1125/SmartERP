<?php

namespace App\Repositories;

use App\Models\ReceiveMaterial;

class ReceiveMaterialRepository implements GlobalInterface {
    
    public function all(){
        return ReceiveMaterial::
        join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->groupBy('purchase_items.purchase_id')
        ->select('receive_materials.receive_date', 'vendors.fname',
                'purchases.purchase_no', 'purchases.purchase_id')
        ->orderBy('receive_materials.created_at', 'desc')->get();
    }

    public function get($id){
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

    public function getEach($id){
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads', 'heads.head_id', '=' ,'materials.unit_id')
        ->select('purchase_items.quantity', 'materials.material_no', 'materials.name',
            'heads.name as hname', 'receive_materials.quantity as rqty', 'receive_materials.created_at')
        ->get();
    }

    public function times($id){
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->distinct('receive_materials.created_at')
        ->select('receive_materials.created_at', 'receive_date')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ReceiveMaterial::create($data);
        return $store->purchase_item_id;
    }

    public function update($id, array $data) {}

    public function delete($id){}
}
