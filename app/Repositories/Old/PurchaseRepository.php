<?php

namespace App\Repositories\Operator;

use Carbon\Carbon;
use App\Models\Purchase;

class PurchaseRepository implements GlobalInterface {
    
    public function all(){
        return Purchase::join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('purchases.*', 'vendors.name')
            ->orderBy('purchases.created_at', 'desc')
            ->get();
    }

    public function get($id){
        return Purchase::join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
        ->select('purchases.*', 'vendors.name')
        ->orderBy('created_at', 'desc')
        ->where('purchases.purchase_id', '=', $id)
        ->get();
    }

    public function count(){
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
    
        return Purchase::whereMonth('purchase_date', $currentMonth)
            ->whereYear('purchase_date', $currentYear)
            ->count();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $pruchase = Purchase::create($data);
        $purchaseId = $pruchase->purchase_id;
        return $purchaseId;
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
