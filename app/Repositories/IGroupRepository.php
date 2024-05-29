<?php

namespace App\Repositories;

use App\Models\IGroup;

class IGroupRepository implements GlobalInterface {
    
    public function all(){
        return IGroup::join('orders', 'orders.order_id', 'igroups.order_id')
        ->orderBy('igroups.created_at', 'desc')->get();
    }

    public function get($id){
        return IGroup::where('igroup_id', $id)
        ->join('orders', 'orders.order_id', 'igroups.order_id')
        ->orderBy('igroups.created_at', 'desc')->first();
    }

    public function igroups($id){
        return IGroup::where('igroups.order_id', $id)
        ->join('orders', 'orders.order_id', 'igroups.order_id')
        ->orderBy('igroups.created_at', 'desc')->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = IGroup::create($data);
        return $store->igroup_id;
    }

    public function update($id, array $data) {
        $update = IGroup::findOrFail($id);
        $update->update($data);
        return $update->igroup_id;
    }

    public function delete($id){}
}
