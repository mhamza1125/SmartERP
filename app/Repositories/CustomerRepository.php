<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository implements GlobalInterface {
    
    public function all(){
        return Customer::all();
    }

    public function get($id){
        return Customer::where('customer_id', $id)
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Customer::create($data);
    }

    public function update($id, array $data) {
        $update = Customer::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){}
}
