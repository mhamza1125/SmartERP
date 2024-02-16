<?php

namespace App\Repositories\Operator;

use App\Models\Salesman;

class SalesmanRepository implements GlobalInterface {
    
    public function all(){
        return Salesman::join('city', 'city.city_id', '=', 'salesman.city_id')
        ->leftJoin('salesman_images', 'salesman_images.salesman_id', '=', 'salesman.salesman_id')
        ->select('salesman.*', 'city.name as cname', 'salesman_images.image')
        ->get();
    }

    public function salesman(){
        return Salesman::join('city', 'city.city_id', '=', 'salesman.city_id')
        ->leftJoin('salesman_images', 'salesman_images.salesman_id', '=', 'salesman.salesman_id')
        ->select('salesman.*', 'city.name as cname', 'salesman_images.image')
        // ->where('salesman.salesman_no', '<>', '0')
        ->where('salesman.head_id', '=', '0')
        ->get();
    }

    public function employee(){
        return Salesman::join('city', 'city.city_id', '=', 'salesman.city_id')
        ->join('heads', 'heads.head_id', '=', 'salesman.head_id')
        ->leftJoin('salesman_images', 'salesman_images.salesman_id', '=', 'salesman.salesman_id')
        ->select('salesman.*', 'city.name as cname', 'salesman_images.image', 'heads.name as hname')
        // ->where('salesman.salesman_no', '0')
        ->where('salesman.head_id', '>', '0')
        ->get();
    }

    public function get($id){
        return Salesman::where('salesman_id', $id)->first();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $salesman = Salesman::create($data);
        $insertId = $salesman->salesman_id;
        return $insertId;
    }

    public function update($id, array $data) {
        $salesman = Salesman::findOrFail($id);
        $salesman->update($data);
    }
    
    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
