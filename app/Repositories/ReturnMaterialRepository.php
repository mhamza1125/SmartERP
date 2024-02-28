<?php

namespace App\Repositories;

use App\Models\ReturnMaterial;

class ReturnMaterialRepository implements GlobalInterface {
    
    public function all(){
        return ReturnMaterial::all();
    }

    public function get($id){
        return ReturnMaterial::where('returns.return_id', $id)
        ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
        ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads', 'heads.head_id', '=' ,'materials.unit_id')
        ->select('materials.*', 'heads.name as hname', 'return_materials.*')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ReturnMaterial::create($data);
        return $store->return_material_id;
    }

    public function update($id, array $data) {}

    public function delete($id){}
}
