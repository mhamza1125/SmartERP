<?php

namespace App\Repositories;

use App\Models\WorkTime;
use App\Models\WorkHoliday;

class AttendanceRepository implements GlobalInterface {
    
    public function all(){
        return WorkTime::orderBy('customers.created_at', 'desc')
        ->get();
    }

    public function workTime(){
        return WorkTime::orderBy('created_at', 'desc')->get();
    }

    public function workHoliday(){
        return WorkHoliday::orderBy('created_at', 'desc')->get();
    }

    public function get($id){}

    public function store(array $data){
        $data['created_by'] = auth()->id();
        if(isset($data['time_from'])){
            $store = WorkTime::create($data);
        }else{
            $store = WorkHoliday::create($data);
        }
        return $store->id;
    }

    public function update($id, array $data) {
        if(isset($data['time_from'])){
            $update = WorkTime::findOrFail($id);
        }else{
            $update = WorkHoliday::findOrFail($id);
        }
        $update->update($data);
        return $update->id;
    }

    
    public function delete($id){}
}
