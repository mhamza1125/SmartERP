<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use App\Http\Requests\BankRequest;
use App\Http\Controllers\Controller;
use App\Repositories\BankRepository;
use App\Repositories\HeadRepository;
use App\Repositories\VendorRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\TransactionRepository;

class BankController extends Controller
{
    protected $headRepository;
    protected $bankRepository;
    protected $vendorRepository;
    protected $employeeRepository;
    protected $customerRepository;
    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        BankRepository $bankRepository,
        VendorRepository $vendorRepository,
        EmployeeRepository $employeeRepository,
        CustomerRepository $customerRepository,
        TransactionRepository $transactionRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->bankRepository = $bankRepository;
        $this->vendorRepository = $vendorRepository;
        $this->employeeRepository = $employeeRepository;
        $this->customerRepository = $customerRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index(){
        $bank = $this->bankRepository->all();
        $head = $this->headRepository->get('6');
        return view('bank', [
            'bank' => $bank,
            'head' => $head,
        ]); 
    }

    public function create(){
        $head = $this->headRepository->get('6');
        $vendor = $this->vendorRepository->all();
        $employee = $this->employeeRepository->all();
        $customer = $this->customerRepository->all();
        return view('addBank', [
            'head' => $head,
            'vendor' => $vendor,
            'employee' => $employee,
            'customer' => $customer,
        ]);
    }

    public function store(BankRequest $request){
        $validatedData = $request->validated();
        $getId = $this->bankRepository->store($validatedData);
        if($validatedData['credit'] > 0 && isset($getId)){
            $transaction = [
                'transaction_to' => 'admin',
                'transaction_type' => 'openingBalance',
                'bank_id' => $getId,
                'credit' => $validatedData['credit'],
                'transaction_date' => date('Y-m-d'),
                'payee_bank_id' => '0',
            ];
            $this->transactionRepository->store($transaction);

        }
        return redirect()->route('bank.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){}
    
    public function edit(Box $id){}

    public function update(Request $request, $id){
        $getId = $this->bankRepository->update($id, $request->input());
        return redirect()->route('bank')->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(product $product){}
}
