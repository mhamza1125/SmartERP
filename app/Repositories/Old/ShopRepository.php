<?php

namespace App\Repositories\Operator;

use DateTime;
use Carbon\Carbon;
use App\Models\Shop;

class ShopRepository implements GlobalInterface {
    
    public function all(){
        return Shop::join('areas', 'areas.area_id', '=', 'shops.area_id')
        ->join('city', 'city.city_id', 'areas.city_id')
        ->select('shops.*', 'areas.name as aname', 'city.name as cname')
        ->get();
    }

    public function get($id){
        return Shop::join('areas', 'areas.area_id', '=', 'shops.area_id')
        ->join('city', 'city.city_id', '=', 'areas.city_id')
        ->select('shops.*', 'city.name as cname', 'areas.name as aname')
        ->where('shops.shop_id', $id)
        ->get();
    }
    
    public function count(){
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
    
        return Shop::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();
    }
    
    public function sname($id){
        return Shop::where('shops.shop_id', $id)
        ->select('shops.shop_no')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $shop = Shop::create($data);
        $shopId = $shop->shop_id;
        return $shopId;
    }

    public function today(){
        $today = new DateTime('now');
        $todayDay = $today->format('l'); // 'l' format retrieves the full textual representation of the day
        return Shop::where('pay_day', $todayDay)
        ->get();
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
