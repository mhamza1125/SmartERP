<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class PurchaseRepository implements GlobalInterface {
    
    public function all(){
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
        ->leftJoin('mprocess', 'mprocess.purchase_id', '=', 'purchases.purchase_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendor_no',
            DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
        ->whereNull('mprocess.purchase_id')
        ->groupBy('purchases.purchase_id')
        ->orderBy('purchases.created_at', 'desc')
        ->get();

        // All Purchases with Status Check
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendor_no',
            DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
        ->groupBy('purchases.purchase_id')
        ->orderBy('purchases.created_at', 'desc')
        ->get();

        // Without Status of Pending / Checked etc
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname')
        ->orderBy('purchases.created_at', 'desc')->get();
    }

    public function mprocess(){
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
        ->leftJoin('mprocess', 'mprocess.purchase_id', '=', 'purchases.purchase_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendors.vendor_no',
            DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
        ->whereNotNull('mprocess.purchase_id')
        ->orderBy('purchases.created_at', 'desc')
        ->groupBy('purchases.purchase_id')
        ->get();    
        
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
        ->leftJoin('mprocess', 'mprocess.purchase_id', '=', 'purchases.purchase_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendor_no',
            DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
        ->whereNotNull('mprocess.purchase_id')
        ->groupBy('purchases.purchase_id', 'orders.job_no', 'vendors.fname', 'vendor_no', 'receives.purchase_id')
        ->orderBy('purchases.created_at', 'desc')
        ->get();
    }

    public function get($id){
        // Checking If it is received or not
        return Purchase::where('purchases.purchase_id', $id)
        ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendors.address', 'vendors.phone1', 
            DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
        ->groupBy('purchases.purchase_id')
        ->first();

        // Getting Without Check
        return Purchase::where('purchase_id', $id)
        ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendors.address', 'vendors.phone1')
        ->first();
    }

    public function getPurchase($id){
        return Purchase::where('purchases.vendor_id', $id)
        ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('purchases.*', 'orders.job_no', 'vendors.fname')
        ->orderBy('purchases.created_at', 'desc')->get();
    }

    public function refNo() {
        $yearMonth = Carbon::now()->format('ym');
        $count = Purchase::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
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
