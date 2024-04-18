<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\EmployeeRequest;
use App\Repositories\EmployeeRepository;
use App\Repositories\TransactionRepository;

class EmployeeController extends Controller
{
    protected $headRepository;
    protected $imageRepository;
    protected $employeeRepository;
    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        EmployeeRepository $employeeRepository, 
        TransactionRepository $transactionRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
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
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'employee', 'employees', $getId);        
            }
        }
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

    public function detail($id){
        $employee = $this->employeeRepository->get($id);
        $detail = $this->transactionRepository->eDetail($id);
        $totalCredit = $detail->where('transaction_type', 'advance')->sum('credit');
        $totalDebit = $detail->where('transaction_type', 'advance')->sum('debit');
        $balance = $totalCredit - $totalDebit;
        return view('employeeDetail', [
            'employee' => $employee,
            'detail' => $detail,
            'balance' => $balance,
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
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'employee', 'employees', $getId);        
            }
        }
        return redirect()->route('employee.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Employee $employee){}
}
