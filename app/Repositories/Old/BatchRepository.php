<?php

namespace App\Repositories\Operator;

use App\Models\Batch;
use Illuminate\Support\Facades\DB;

class BatchRepository implements GlobalInterface {
    
    public function all(){
        return Batch::join('purchases2', 'purchases2.purchase_id', '=', 'batches.purchase_id')
    ->join('vendors', 'vendors.vendor_id', '=', 'purchases2.vendor_id')
    ->join('heads as head_in', 'head_in.head_id', '=', 'batches.head_in')
    ->join('heads as head_out', 'head_out.head_id', '=', 'batches.head_out')
    ->leftJoin('costs', 'costs.purchase_id', '=', 'purchases2.purchase_id')
    ->select(
        'batches.*',
        'purchases2.total',
        'purchases2.purchase_no',
        'vendors.name',
        'head_in.name as hname_in',
        'head_out.name as hname_out',
        DB::raw('IF(costs.purchase_id IS NOT NULL, 1, 0) as del')
    )
    ->orderBy('batches.created_at', 'desc')
    ->get();
    }

    public function batchCost(){
        return Batch::join('purchases2', 'purchases2.purchase_id', '=', 'batches.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases2.vendor_id')
            ->join('costs', 'costs.purchase_id', '=', 'batches.purchase_id')
            ->select('purchases2.*', 'costs.*')
            ->orderBy('costs.created_by', 'desc')
            ->get();
    }

    public function all2(){
        $data = Batch::whereIn('batch_id', function ($query) {
            $query->select('batch_id')
                ->from('products');
        })
        ->get();
        return $data;
    }

    public function liter(){
        return Batch::whereNull('total_liter')->get();
    }

    public function price(){
        return Batch::whereNull('batch_price')->get();
    }
    
    public function cost(){
        return Batch::
        // whereNotNull('batch_price')
        whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('costs')
                ->whereRaw('costs.purchase_id = batches.purchase_id');
        })->get();
    }
    
    public function return(){
        return Batch::whereNotNull('per_liter')->get();
    }

    public function get($id){
        return Batch::join('purchases2', 'purchases2.purchase_id', '=', 'batches.purchase_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'purchases2.vendor_id')
        ->join('costs', 'costs.purchase_id', '=', 'batches.purchase_id')
        ->select('batches.*', 'vendors.name', 'purchases2.purchase_no', 'costs.cost_no')
        ->where('costs.cost_id', '=', $id)
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $batch = Batch::create($data);
        $batchId = $batch->batch_id;
        return $batchId;
    }

    public function update($id, array $data) {
        $batch = Batch::findOrFail($id);
        $batch->update($data);
    } 

    public function updateLiter($id, array $data) {
        $batch = Batch::where('purchase_id', $id);
        $batch->update($data);
    }    

    public function delete($id){
        Batch::destroy($id);
    }
}
