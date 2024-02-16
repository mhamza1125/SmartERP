<?php

namespace App\Repositories\Operator;

use Illuminate\Support\Facades\DB;
use App\Models\AssignShops;

class AssignShopsRepository implements GlobalInterface {
    
    public function all(){
        $data = AssignShops::join('shops', 'shops.shop_id', '=', 'shops_assign.shop_id')
            ->orderBy('sname')
            ->get();
        return $data;
    }

    public function all2(){
        $data = DB::table('shops')
        ->join('areas', 'areas.area_id', '=', 'shops.area_id')
        ->join('city', 'areas.city_id', '=', 'city.city_id')
        ->get();
        return $data;
    }

    public function get($id){
        $data = DB::table('shops_assign')
        ->join('shops', 'shops.shop_id', '=', 'shops_assign.shop_id')
        ->where('shops_assign.salesman_id', $id)
        ->select('shops.*', 'shops_assign.*', 'shops_assign.status as status2')
        ->get();
        return $data;
    }
    
    public function get2($id){
        $data = DB::table('shops_assign')
        ->join('shops', 'shops.shop_id', '=', 'shops_assign.shop_id')
        ->leftJoin('transactions', 'transactions.shop_id', '=', 'shops_assign.shop_id')
        ->where('shops_assign.salesman_id', $id)
        ->groupBy('shops_assign.shop_id', 'shops.shop_id', 'shops.shop_no', 'shops.sname', 'shops.pay_day', 'shops_assign.salesman_id', 'shops_assign.status', 'shops_assign.sa_id')
        ->select(
            'shops.shop_id', 'shops.shop_no', 'shops.sname', 'shops.pay_day',
            'shops_assign.salesman_id', 'shops_assign.sa_id',
            'shops_assign.status as status2',
            DB::raw('SUM(transactions.debit) as tdebit'),
            DB::raw('SUM(transactions.credit) as tcredit'),
            DB::raw('(SUM(transactions.debit) - SUM(transactions.credit)) as balance')
        )
        ->get();
        
        return $data;

    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        AssignShops::create($data);
    }

    public function update($id, array $data) {
        $fetch = AssignShops::findOrFail($id);
        $fetch->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
