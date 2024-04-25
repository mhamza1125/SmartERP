<?php

namespace App\Repositories;

use App\Models\Box;

class BoxRepository implements GlobalInterface {
    
    public function all(){
        return Box::join('heads', 'heads.head_id', '=', 'boxes.head_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'boxes.vendor_id')
        ->select('boxes.*', 'heads.name as hname', 'vendors.fname', 'vendor_no')
        ->orderBy('boxes.created_at', 'desc')->get();
    }

    public function active(){
        return Box::where('boxes.box_status', '1')
        ->join('heads', 'heads.head_id', '=', 'boxes.head_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'boxes.vendor_id')
        ->select('boxes.*', 'heads.name as hname', 'vendors.fname', 'vendor_no')
        ->orderBy('boxes.created_at', 'desc')->get();
    }

    public function get($id){
        return Box::where('box_id', $id)
        ->join('heads', 'heads.head_id', '=', 'boxes.head_id')
        ->join('vendors', 'vendors.vendor_id', '=', 'boxes.vendor_id')
        ->select('boxes.*', 'heads.name as hname', 'vendors.fname', 'vendor_no')
        ->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Box::create($data);
        return $store->box_id;
    }

    public function update($id, array $data) {
        $update = Box::findOrFail($id);
        $update->update($data);
        return $update->product_id;
    }

    public function delete($id){}
}
