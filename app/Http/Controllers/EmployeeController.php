<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\EmployeeRequest;
use App\Repositories\EmployeeRepository;

class EmployeeController extends Controller
{
    protected $employeeRepository;
    protected $headRepository;
    protected $imageRepository;

    public function __construct(
        EmployeeRepository $employeeRepository, 
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->employeeRepository = $employeeRepository;
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
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
        return view('addEmployee', [
            'department' => $department,
            'employeeType' => $employeeType,
            'city' => $city,
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
        return redirect()->route('employee.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $employee = $this->employeeRepository->get($id);
        $image = $this->imageRepository->get2('employees', $id);
        return view('employeeInfo', [
            'employee' => $employee,
            'image' => $image,
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
