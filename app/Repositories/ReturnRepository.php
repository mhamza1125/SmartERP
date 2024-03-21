<?php

namespace App\Repositories;

use App\Models\Returns;

class ReturnRepository implements GlobalInterface {
    
    public function all(){
        return Returns::join('receives', 'receives.receive_id', '=', 'returns.receive_id')
        ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('returns.*', 'receives.*', 'vendors.fname', 'purchases.purchase_no')
        ->orderBy('receives.created_at', 'desc')->get();
    }

    public function get($id){
        return Returns::where('returns.return_id', $id)
        ->join('receives', 'receives.receive_id', '=', 'returns.receive_id')
        ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->select('returns.*', 'receives.*', 'purchases.*', 'vendors.*', 'orders.job_no', 
            'returns.description as desc')
        ->first();
    }

    public function returned($id){
        return Returns::where('returns.receive_id', $id)
        ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
        ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
        // ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        // ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        // ->join('heads', 'heads.head_id', '=' ,'materials.unit_id')
        // ->select('materials.*', 'heads.name as hname', 'return_materials.*')
        ->select('return_materials.*')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Returns::create($data);
        return $store->return_id;
    }

    public function update($id, array $data) {
        $update = Returns::findOrFail($id);
        $update->update($data);
        return $update->return_id;
    }

    public function delete($id){}
}
