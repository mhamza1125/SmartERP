<?php

namespace App\Repositories;

use App\Models\Material;

class MaterialRepository implements GlobalInterface {
    
    public function all(){
        return Material::join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
        ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
        ->orderBy('materials.created_at', 'desc')
        ->get();
    }

    public function get($id){
        return Material::where('materials.material_id', $id)
        ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
        ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
        ->first();
    }

    public function getMaterial($id){
        // Vendor Selling  & Product Raw Materials
        $materialIds = explode('|', $id);
        return Material::whereIn('materials.material_id', $materialIds)
            ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
            ->get();
    }

    public function getBox(){
        // Product Boxes
        return Material::where('mthead.head_id', '61')
        ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
        ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
        ->orderBy('materials.created_at', 'desc')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Material::create($data);
        return $store->material_id;
    }

    public function update($id, array $data) {
        $update = Material::findOrFail($id);
        $update->update($data);
        return $update->material_id;
    }

    public function delete($id){}
}
