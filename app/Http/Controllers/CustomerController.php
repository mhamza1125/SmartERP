<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerRepository;
use App\Repositories\TransactionRepository;

class CustomerController extends Controller
{
    protected $headRepository;
    protected $imageRepository;
    protected $customerRepository;
    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        CustomerRepository $customerRepository,
        TransactionRepository $transactionRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->customerRepository = $customerRepository;
        $this->transactionRepository = $transactionRepository;
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
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'customer',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->store($transaction);
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

    public function detail(Request $request, $id){
        $customer = $this->customerRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance
        if(!empty($dfrom) && !empty($dto)){
            $all = $this->transactionRepository->cDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        }else{
            $detail = $this->transactionRepository->cDetail($id);
        }
        $totalCredit = $detail->sum('credit');
        $totalDebit = $detail->sum('debit');
        $balance = $totalCredit - $totalDebit + $oBalance + $cBalance;
        return view('customerDetail', [
            'customer' => $customer,
            'detail' => $detail,
            'balance' => $balance,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
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
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'customer',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->updateOB($getId, 'customer', $transaction);
        return redirect()->route('customer.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Customer $customer){}
}
