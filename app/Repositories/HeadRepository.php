<?php

namespace App\Repositories;

use App\Models\Head;

class HeadRepository implements GlobalInterface {
    
    public function all(){
        return Head::join('head_types', 'head_types.head_type_id', '=', 'heads.head_type_id')
        ->select('heads.*', 'head_types.name as htname')
        ->orderBy('head_types.name')
        ->get();
    }

    public function headType(){
        return \DB::table('head_types')
            ->get();
    }

    public function get($id){
        return Head::where('heads.head_type_id', $id)
        ->where('heads.head_status', '1')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Head::create($data);
    }

    public function update($id, array $data) {
        $update = Head::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){}
}
