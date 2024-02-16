<?php

namespace App\Repositories\Operator;

use App\Models\CostItem;
use Illuminate\Support\Facades\DB;

class CostItemRepository{
    
    public function all(){
        return CostItem::get();
    }

    public function get($id){
        $data = DB::table('cost_items')
        ->join('heads', 'heads.head_id', '=', 'cost_items.head_id')
        ->where('cost_items.cost_id', $id)
        ->get();
        return $data;
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        CostItem::create($data);
    }

    public function updateCost($id, $hid, array $data) {
        $cost = CostItem::where('cost_id', $id)
        ->where('head_id', $hid);
        $cost->update($data);
    } 

    public function delete($id){
        CostItem::where('cost_id', $id)->delete();
    }
}
