<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerRepository;

class CustomerController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository){
        $this->middleware(['auth', 'all']);
        $this->customerRepository = $customerRepository;
    }

    public function index(){
        $customer = $this->customerRepository->all();
        return view('customer', [
            'customer' => $customer,
        ]); 
    }

    public function create(){
        return view('addCustomer');
    }

    public function store(CustomerRequest $request){
        $validatedData = $request->validated();
        $this->customerRepository->store($validatedData);
        return redirect()->route('customer.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show(Customer $id){
        return view('customerInfo', [
            'customer' => $id,
        ]);
    }
    
    public function edit(Customer $id){
        return view('editCustomer', [
            'customer' => $id,
        ]);
    }

    public function update(Request $request, $id){
        $this->customerRepository->update($id, $request->input());      
        return redirect()->route('customer.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Customer $customer){}
}
