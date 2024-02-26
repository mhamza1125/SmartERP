<?php

namespace App\Repositories;

use App\Models\Purchase;

class PurchaseRepository implements GlobalInterface {
    
    public function all(){
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname')
        ->orderBy('purchases.created_at', 'desc')->get();
    }

    public function get($id){
        return Purchase::where('purchase_id', $id)
        ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendors.address', 'vendors.phone1')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Purchase::create($data);
        return $store->purchase_id;
    }

    public function update($id, array $data) {
        $update = Purchase::findOrFail($id);
        $update->update($data);
        return $update->purchase_id;
    }

    public function delete($id){}
}
