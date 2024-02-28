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
        ->select('receives.*', 'purchases.*', 'vendors.*', 'orders.job_no', 'receives.description as desc')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Returns::create($data);
        return $store->return_id;
    }

    public function update($id, array $data) {}

    public function delete($id){}
}
