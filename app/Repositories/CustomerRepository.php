<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Customer;

class CustomerRepository implements GlobalInterface {
    
    public function all(){
        return Customer::orderBy('customers.created_at', 'desc')
        ->get();
    }

    public function get($id){
        return Customer::where('customer_id', $id)
        ->join('heads as country', 'country.head_id', '=', 'customers.country_id')
        ->join('heads as currency', 'currency.head_id', '=', 'customers.currency_id')
        ->select('*', 'country.name as coname', 'currency.name as cuname')->first();
    }
    
    public function refNo(){
        $year = Carbon::now()->format('y');
        $count = Customer::whereYear('created_at', Carbon::now()->year)->count();
        $threeDigitNumber = str_pad($count+1, 3, '0', STR_PAD_LEFT);
        return 'C' . $year . $threeDigitNumber;
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
