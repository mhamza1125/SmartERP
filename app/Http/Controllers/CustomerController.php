<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerRepository;

class CustomerController extends Controller
{
    protected $headRepository;
    protected $imageRepository;
    protected $customerRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        CustomerRepository $customerRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
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
        $count = $this->customerRepository->refNo();
        $country = $this->headRepository->get('15');
        $currency = $this->headRepository->get('16');
        return view('addCustomer', [
            'count' => $count,
            'country' => $country,
            'currency' => $currency,
        ]);
    }

    public function store(CustomerRequest $request){
        $validatedData = $request->validated();
        $getId = $this->customerRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'customer', 'customers', $getId);        
            }
        }
        return redirect()->route('customer.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $customer = $this->customerRepository->get($id);
        $image = $this->imageRepository->image('customers', $id);
        return view('customerInfo', [
            'customer' => $customer,
            'image' => $image,
        ]);
    }
    
    public function edit(Customer $id){
        $country = $this->headRepository->get('15');
        $currency = $this->headRepository->get('16');
        return view('editCustomer', [
            'customer' => $id,
            'country' => $country,
            'currency' => $currency,
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
