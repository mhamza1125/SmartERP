<?php

namespace App\Repositories;

use App\Models\Vendor;

class VendorRepository implements GlobalInterface {
    
    public function all(){
        return Vendor::join('heads as vthead', 'vthead.head_id', '=', 'vendors.vendor_type_id')
        ->select('vendors.*', 'vthead.name as vtname')
        ->orderBy('vendors.created_at', 'desc')->get();
    }

    public function get($id){
        return Vendor::where('vendor_id', $id)
        ->join('heads as vthead', 'vthead.head_id', '=', 'vendors.vendor_type_id')
        ->join('heads as chead', 'chead.head_id', '=', 'Vendors.city_id')
        ->select('vendors.*', 'vthead.name as vtname', 'chead.name as cname')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Vendor::create($data);
        return $store->vendor_id;
    }

    public function update($id, array $data) {
        $update = Vendor::findOrFail($id);
        $update->update($data);
        return $update->vendor_id;
    }

    public function delete($id){}
}
