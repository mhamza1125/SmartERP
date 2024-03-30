<?php

namespace App\Repositories;

use Carbon\Carbon;
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

    public function refNo() {
        $yearMonth = Carbon::now()->format('ym');
        $count = Purchase::whereMonth('purchase_date', Carbon::now()->month)
            ->whereYear('purchase_date', Carbon::now()->year)->count();
        $threeDigitNumber = str_pad($count+1, 3, '0', STR_PAD_LEFT);
        return 'P' . $yearMonth . $threeDigitNumber;
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
