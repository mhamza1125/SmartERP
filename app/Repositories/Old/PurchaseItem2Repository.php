<?php

namespace App\Repositories\Operator;

use App\Models\PurchaseItem2;
use Illuminate\Support\Facades\DB;

class PurchaseItem2Repository implements GlobalInterface {
    
    public function all(){
        return PurchaseItem2::get();
    }

    public function get($id){
        $data = PurchaseItem2::join('heads', 'heads.head_id', '=', 'purchase_items2.head_id')
        // ->join('packing_types', 'packing_types.pt_id', '=', 'products.pt_id')
        ->select('purchase_items2.quantity as qty', 'purchase_items2.price', 'heads.name as hname')
        ->where('purchase_items2.purchase_id', $id)
        ->get();
        return $data;
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        PurchaseItem2::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
