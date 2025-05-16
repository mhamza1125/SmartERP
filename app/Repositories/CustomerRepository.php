<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements GlobalInterface
{
    public function all()
    {
        return Customer::orderBy('customers.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        return Customer::where('customer_id', $id)
            ->join('heads as country', 'country.head_id', '=', 'customers.country_id')
            ->join('heads as currency', 'currency.head_id', '=', 'customers.currency_id')
            ->select('*', 'country.name as coname', 'currency.name as cuname')->first();
    }

    public function refNo()
    {
        $lastCustomer = Customer::all()->sortByDesc(function ($customer) {
            return intval(substr($customer->customer_no, 2)); // skip "CU"
        })->first();
        $lastNumber = $lastCustomer ? intval(substr($lastCustomer->customer_no, 2)) : 0;
        
        return 'CU' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);        

        // $year = Carbon::now()->format('y');
        // $count = Customer::whereYear('created_at', Carbon::now()->year)->count();
        // $threeDigitNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        // return 'CU'.$year.$threeDigitNumber;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Customer::create($data);

        return $store->customer_id;
    }

    public function update($id, array $data)
    {
        $update = Customer::findOrFail($id);
        $update->update($data);

        return $update->customer_id;
    }

    public function delete($id){}
}
