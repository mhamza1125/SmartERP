<?php

namespace App\Repositories;

use App\Models\Receive;

class ReceiveRepository implements GlobalInterface {
    
    public function all(){
        return Receive::join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('receives.*', 'vendors.fname', 'purchases.purchase_no')
        ->orderBy('receives.created_at', 'desc')->get();
    }

    public function get($id){
        return Receive::where('receives.receive_id', $id)
        ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->select('receives.*', 'purchases.*', 'vendors.*', 'orders.job_no', 'receives.description as desc')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Receive::create($data);
        return $store->receive_id;
    }

    public function update($id, array $data) {}

    public function delete($id){}
}
