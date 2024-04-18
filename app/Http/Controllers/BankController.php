<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use App\Http\Requests\BankRequest;
use App\Http\Controllers\Controller;
use App\Repositories\BankRepository;
use App\Repositories\HeadRepository;
use App\Repositories\VendorRepository;
use App\Repositories\EmployeeRepository;

class BankController extends Controller
{
    protected $headRepository;
    protected $bankRepository;
    protected $employeeRepository;
    protected $vendorRepository;

    public function __construct(
        HeadRepository $headRepository,
        BankRepository $bankRepository,
        EmployeeRepository $employeeRepository,
        VendorRepository $vendorRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->bankRepository = $bankRepository;
        $this->employeeRepository = $employeeRepository;
        $this->vendorRepository = $vendorRepository;
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
        return view('addBank', [
            'head' => $head,
            'vendor' => $vendor,
            'employee' => $employee,
        ]);
    }

    public function store(BankRequest $request){
        $validatedData = $request->validated();
        $getId = $this->bankRepository->store($validatedData);
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
