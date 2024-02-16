<?php

namespace App\Repositories\Operator;

use App\Models\Vendor;

class VendorRepository implements GlobalInterface {
    
    public function all(){
        return Vendor::leftJoin('vendor_images', 'vendors.vendor_id', '=', 'vendor_images.vendor_id')
        ->join('vendor_types', 'vendors.vt_id', '=', 'vendor_types.vt_id')
        ->select('vendors.*', 'vendor_images.image', 'vendor_types.name as vtname')
        ->get();
    }

    public function purchase(){
        return Vendor::leftJoin('vendor_images', 'vendors.vendor_id', '=', 'vendor_images.vendor_id')
        ->join('vendor_types', 'vendors.vt_id', '=', 'vendor_types.vt_id')
        ->select('vendors.*', 'vendor_images.image', 'vendor_types.name as vtname')
        ->where('vendor_types.type', '=', '1')
        ->get();
    }
    
    public function batch(){
        return Vendor::leftJoin('vendor_images', 'vendors.vendor_id', '=', 'vendor_images.vendor_id')
        ->join('vendor_types', 'vendors.vt_id', '=', 'vendor_types.vt_id')
        ->select('vendors.*', 'vendor_images.image', 'vendor_types.name as vtname')
        ->where('vendor_types.type', '!=', '1')
        ->get();
    }

    public function get($id){
        return Vendor::leftJoin('vendor_images', 'vendors.vendor_id', '=', 'vendor_images.vendor_id')
        ->join('vendor_types', 'vendors.vt_id', '=', 'vendor_types.vt_id')
        ->select('vendors.*', 'vendor_images.image', 'vendor_types.name as vtname')
        ->where('vendors.vendor_id', $id)
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $vendor = Vendor::create($data);
        $insertId = $vendor->vendor_id;
        return $insertId;
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
