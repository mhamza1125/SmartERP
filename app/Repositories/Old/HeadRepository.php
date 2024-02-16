<?php

namespace App\Repositories\Operator;

use App\Models\Head;

class HeadRepository implements GlobalInterface {
    
    public function all(){
        return Head::join('head_types', 'head_types.ht_id', '=', 'heads.ht_id')
        ->select('heads.*', 'head_types.name as htname')
        ->get();
    }

    public function get($id){
        return Head::join('head_types', 'head_types.ht_id', '=', 'heads.ht_id')
        ->select('heads.*')
        ->where('head_types.ht_id', $id)
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Head::create($data);
    }

    public function update($id, array $data) {
        $update = Head::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
