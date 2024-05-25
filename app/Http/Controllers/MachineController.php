<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use App\Repositories\ImageRepository;
use App\Http\Requests\MachineRequest;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\MachineRepository;
use App\Repositories\EmployeeRepository;

class MachineController extends Controller
{
    protected $imageRepository;
    protected $headRepository;
    protected $machineRepository;
    protected $employeeRepository;

    public function __construct(
        ImageRepository $imageRepository, 
        HeadRepository $headRepository, 
        MachineRepository $machineRepository, 
        EmployeeRepository $employeeRepository
    ){
        $this->middleware(['auth', 'all']);
        $this->imageRepository = $imageRepository;
        $this->headRepository = $headRepository;
        $this->machineRepository = $machineRepository;
        $this->employeeRepository = $employeeRepository;
    }

    public function index(){
        $machine = $this->machineRepository->all();
        return view('machine', [
            'machine' => $machine,
        ]);   
    }

    public function create(){
        $head = $this->headRepository->get('19');
        $refNo = $this->machineRepository->refNo();
        $employee = $this->employeeRepository->all();
        return view('addMachine', [
            'head' => $head,
            'refNo' => $refNo,
            'employee' => $employee,
        ]);
    }

    public function store(MachineRequest $request){
        $validatedData = $request->validated();
        $getId = $this->machineRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'machine', 'machines', $getId);        
            }
        }
        return redirect()->route('machine.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function update(Request $request, $id){
        $this->machineRepository->update($id, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'machine', 'machines', $id);        
            }
        }
        return redirect()->route('machine.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function show($id){
        $image = $this->imageRepository->image('machines', $id);
        $machine = $this->machineRepository->get($id);
        return view('machineInfo', [
            'image' => $image,
            'machine' => $machine,
        ]);
    }

    public function edit($id){
        $head = $this->headRepository->get('19');
        $employee = $this->employeeRepository->all();
        $machine = $this->machineRepository->get($id);
        return view('editMachine', [
            'head' => $head,
            'machine' => $machine,
            'employee' => $employee,
        ]);
    }

    public function destroy(Head $head){}
}
