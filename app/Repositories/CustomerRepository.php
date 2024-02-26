<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository implements GlobalInterface {
    
    public function all(){
        return Customer::orderBy('customers.created_at', 'desc')
        ->get();
    }

    public function get($id){
        return Customer::where('customer_id', $id)
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Customer::create($data);
        return $store->customer_id;
    }

    public function update($id, array $data) {
        $update = Customer::findOrFail($id);
        $update->update($data);
        return $update->customer_id;
    }

    public function delete($id){}
}
