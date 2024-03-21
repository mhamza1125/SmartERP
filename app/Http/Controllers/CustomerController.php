<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ImageRepository;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerRepository;

class CustomerController extends Controller
{
    protected $imageRepository;
    protected $customerRepository;

    public function __construct(
        ImageRepository $imageRepository,
        CustomerRepository $customerRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->imageRepository = $imageRepository;
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
        $getId = $this->customerRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'customer', 'customers', $getId);        
            }
        }
        return redirect()->route('customer.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show(Customer $id){
        $image = $this->imageRepository->image('customers', $id->customer_id);
        return view('customerInfo', [
            'customer' => $id,
            'image' => $image,
        ]);
    }
    
    public function edit(Customer $id){
        return view('editCustomer', [
            'customer' => $id,
        ]);
    }

    public function update(Request $request, $id){
        $getId = $this->customerRepository->update($id, $request->input());  
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'customer', 'customers', $getId);        
            }
        }    
        return redirect()->route('customer.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Customer $customer){}
}
