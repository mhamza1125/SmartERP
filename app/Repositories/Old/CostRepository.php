<?php

namespace App\Repositories\Operator;

use App\Models\Cost;
use Illuminate\Support\Facades\DB;

class CostRepository implements GlobalInterface {
    
    public function all(){
        return Cost::join('purchases2', 'purchases2.purchase_id', '=', 'costs.purchase_id')
        ->select(
            'costs.*',
            'purchases2.purchase_no',
            DB::raw('CASE WHEN EXISTS (SELECT 1 FROM stocks WHERE stocks.cost_id = costs.cost_id) THEN 1 ELSE 0 END as del')
        )
        ->get();
    }

    public function get($id){
        return Cost::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $cost = Cost::create($data);
        $insertId = $cost->cost_id;
        return $insertId;
    }

    public function update($id, array $data) {
        $cost = Cost::findOrFail($id);
        $cost->update($data);
    } 

    public function delete($id){
        Cost::destroy($id);
    }
}
