<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Models\ReceiveMaterial;
use Illuminate\Support\Facades\DB;

class StockRepository implements GlobalInterface {
    
    public function all(){
        // Not Used
        return ReceiveMaterial123::leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->select('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name as mtname', 'uhead.name as uname')
        ->selectRaw('SUM(receive_materials.quantity) as total_received')
        ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
        ->where('receive_materials.inspection_status', '2')
        ->groupBy('materials.material_id')
        ->orderBy('materials.name')
        ->get();
    }

    public function get($id){
        $data['created_by'] = auth()->id();
        $store = Stock::create($data);
        return $store->stock_id;
    }

    public function store(array $data){}

    public function update($id, array $data) {}

    public function delete($id){}
}
