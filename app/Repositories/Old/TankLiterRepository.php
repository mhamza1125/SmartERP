<?php

namespace App\Repositories\Operator;

use App\Models\Tank;
use App\Models\TankLiter;

class TankLiterRepository implements GlobalInterface {
    
    public function all(){
        return TankLiter::join('tanks', 'tanks.tank_id', '=', 'tank_liters.tank_id')
            ->join('purchases2', 'purchases2.purchase_id', '=', 'tank_liters.purchase_id')
            ->select('tanks.*', 'purchases2.purchase_no', 'tank_liters.liters', 'tl_id')
            ->orderBy('tank_liters.created_at', 'desc')
            ->get();
    }

    public function available(){
        return Tank::leftJoin('tank_liters', 'tanks.tank_id', '=', 'tank_liters.tank_id')
        ->selectRaw('tanks.name, 
            COALESCE(SUM(tank_liters.liters), 0) AS tliter, 
            tanks.supervisor')
        ->groupBy('tanks.tank_id', 'tanks.name', 'tanks.supervisor')
        ->get();
        
        // return TankLiter::join('tanks', 'tanks.tank_id', '=', 'tank_liters.tank_id')
        //     ->selectRaw('tanks.name, 
        //         SUM(tank_liters.liters) AS tliter, 
        //         tanks.supervisor')
        //     ->groupBy('tanks.tank_id', 'tanks.name', 'tanks.supervisor')
        //     ->get();
    }

    public function get($id){
        return TankLiter::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        TankLiter::create($data);
    }

    public function update($id, array $data) {
        $tankLiter = TankLiter::findOrFail($id);
        $tankLiter->update($data);
    }

    public function delete($pid){
        TankLiter::where('purchase_id', $pid)->delete();
    }
    
    public function deleteNeg($id, $pid){
        TankLiter::where('purchase_id', $pid)
        ->where('cost_id', $id)
        ->where('liters', '<', 0)
        ->delete();
    }
}
