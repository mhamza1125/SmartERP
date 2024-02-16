<?php

namespace App\Repositories\Operator;

use App\Models\Bank;

class BankRepository implements GlobalInterface {
    
    public function all(){
        return Bank::join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->leftJoin('vendors', 'vendors.vendor_id', '=', 'banks.vendor_id')
        ->select('banks.*', 'vendors.name as vname', 'heads.name as hname')
        ->get();
    }

    public function get($id){
        return Bank::join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->leftJoin('vendors', 'vendors.vendor_id', '=', 'banks.vendor_id')
        ->select('banks.*', 'vendors.name as vname', 'heads.name as hname')
        ->where('banks.vendor_id', $id)
        ->get();
    }

    public function self(){
        return Bank::join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->select('banks.*', 'heads.name as hname')
        ->where('banks.vendor_id', '0')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Bank::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
