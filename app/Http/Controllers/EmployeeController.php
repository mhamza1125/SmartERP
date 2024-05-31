<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\SalaryRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\EmployeeRequest;
use App\Repositories\EmployeeRepository;
use App\Repositories\TransactionRepository;

class EmployeeController extends Controller
{
    protected $headRepository;
    protected $salaryRepository;
    protected $imageRepository;
    protected $employeeRepository;
    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        SalaryRepository $salaryRepository,
        ImageRepository $imageRepository,
        EmployeeRepository $employeeRepository, 
        TransactionRepository $transactionRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->salaryRepository = $salaryRepository;
        $this->imageRepository = $imageRepository;
        $this->employeeRepository = $employeeRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index(){
        $employee = $this->employeeRepository->all();
        return view('employee', [
            'employee' => $employee,
        ]); 
    }

    public function create(){
        $department = $this->headRepository->get('3');
        $employeeType = $this->headRepository->get('9');
        $city = $this->headRepository->get('8');
        $count = $this->employeeRepository->refNo();
        return view('addEmployee', [
            'department' => $department,
            'employeeType' => $employeeType,
            'city' => $city,
            'count' => $count,  
        ]);
    }

    public function store(EmployeeRequest $request){
        $validatedData = $request->validated();
        $getId = $this->employeeRepository->store($validatedData);
        $salary = ['employee_id' => $getId, 'amount' => $request->input('salary')];
        $this->salaryRepository->store($salary);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'employee', 'employees', $getId);
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'employee',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->store($transaction);
        return redirect()->route('employee.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $employee = $this->employeeRepository->get($id);
        $image = $this->imageRepository->image('employees', $id);
        return view('employeeInfo', [
            'employee' => $employee,
            'image' => $image,
        ]);
    }

    public function detail(Request $request, $id){
        $employee = $this->employeeRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance
        if(!empty($dfrom) && !empty($dto)){
            $all = $this->transactionRepository->eDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        }else{
            $detail = $this->transactionRepository->eDetail($id);
        }
        echo $oBalance;
        echo $cBalance;
        // dd($detail);
        $totalCredit = $detail->whereIn('transaction_type', ['advance', 'receiveAdvance', 'openingBalance'])->sum('credit');
        $totalDebit = $detail->whereIn('transaction_type', ['advance', 'receiveAdvance', 'openingBalance'])->sum('debit');
        $balance = $totalCredit - $totalDebit + $oBalance + $cBalance;

        return view('employeeDetail', [
            'employee' => $employee,
            'detail' => $detail,
            'balance' => $balance,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }
    
    public function edit(Employee $id){
        $department = $this->headRepository->get('3');
        $employeeType = $this->headRepository->get('9');
        $city = $this->headRepository->get('8');
        return view('editEmployee', [
            'employee' => $id,
            'department' => $department,
            'employeeType' => $employeeType,
            'city' => $city,
        ]);
    }

    public function update(Request $request, $id){
        $getId = $this->employeeRepository->update($id, $request->input());      
        $salary = ['employee_id' => $id, 'amount' => $request->input('salary')];
        $this->salaryRepository->update($id, $salary);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'employee', 'employees', $getId);        
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'employee',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->updateOB($getId, 'employee', $transaction);
        return redirect()->route('employee.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Employee $employee){}
}
