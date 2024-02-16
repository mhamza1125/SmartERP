<?php

namespace App\Repositories\Operator;

use Carbon\Carbon;
use App\Models\Purchase2;
use Illuminate\Support\Facades\DB;

class Purchase2Repository implements GlobalInterface {
    
    public function all(){
        return Purchase2::leftJoin('costs', 'costs.purchase_id', '=', 'purchases2.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases2.vendor_id')
        ->select(
            'purchases2.*',
            'vendors.name',
            DB::raw('IF(costs.purchase_id IS NOT NULL, 1, 0) as del')
        )
        ->orderBy('purchases2.created_at', 'desc')
        ->get();
    }

    public function get($id){
        return Purchase2::join('vendors', 'vendors.vendor_id', '=', 'purchases2.vendor_id')
        ->select('purchases2.*', 'vendors.name')
        ->orderBy('created_at', 'desc')
        ->where('purchases2.purchase_id', '=', $id)
        ->get();
    }

    public function batch(){
        return DB::table('purchases2')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases2.vendor_id')
            ->join('vendor_types', 'vendor_types.vt_id', '=', 'vendors.vt_id')
            ->leftJoin('batches', 'batches.purchase_id', '=', 'purchases2.purchase_id')
            ->select('purchases2.*', 'vendors.name')
            ->whereNull('batches.purchase_id')
            ->where('vendor_types.type', '=', '0')
            ->orderByDesc('purchases2.created_at')
            ->get();
    }

    public function cost(){
        return Purchase2::join('batches', 'purchases2.purchase_id', '=', 'batches.purchase_id')
            // ->leftJoin('costs', 'costs.purchase_id', '=', 'batches.purchase_id')
            // ->whereNull('costs.purchase_id')
            ->select('purchases2.*')
            ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $pruchase = Purchase2::create($data);
        $purchaseId = $pruchase->purchase_id;
        return $purchaseId;
    }

    public function count(){
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
    
        return Purchase2::whereMonth('purchase_date', $currentMonth)
            ->whereYear('purchase_date', $currentYear)
            ->count();
    }

    public function update($id, array $data){
        $purchase = Purchase2::findOrFail($id);
        $purchase->update($data);
    }

    public function delete($id){
        Purchase2::destroy($id);
    }
}
