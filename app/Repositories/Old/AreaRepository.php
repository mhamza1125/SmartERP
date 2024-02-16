<?php

namespace App\Repositories\Operator;

use App\Models\Area;

class AreaRepository implements GlobalInterface {
    
    public function all(){
        return Area::join('city', 'city.city_id', '=', 'areas.city_id')
        ->select('areas.*', 'city.name as cname')
        ->get();
    }

    public function get($id){
        return Area::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Area::create($data);
    }

    
    public function update($id, array $data) {
        $update = Area::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
