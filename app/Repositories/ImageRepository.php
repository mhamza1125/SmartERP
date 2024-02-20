<?php

namespace App\Repositories;

use App\Models\Image;

class ImageRepository implements GlobalInterface {
    
    public function all(){}

    public function get($id){}

    public function get2($tname, $tid){
        return Image::where('table_name', $tname)
        ->where('table_id', $tid)
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Image::create($data);
        return $store->image_id;
    }

    public function update($id, array $data){}

    public function delete($id){
        Image::destroy($id);
    }
     
    public function delete2($tname, $tid){
        // All Delete Not Used
        Image::where('table_name', $tname)
        ->where('table_id', $tid)
        ->delete();
    }
}
