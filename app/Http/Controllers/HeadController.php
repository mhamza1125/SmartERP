<?php

namespace App\Http\Controllers;

use App\Models\Head;
use Illuminate\Http\Request;
use App\Http\Requests\HeadRequest;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\HeadTypeRepository;

class HeadController extends Controller
{
    protected $headRepository;
    protected $headTypeRepository;

    public function __construct(
        HeadRepository $headRepository, 
        HeadTypeRepository $headTypeRepository
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->headTypeRepository = $headTypeRepository;
    }

    public function index(){
        $head = $this->headRepository->all();
        $headType = $this->headTypeRepository->all();
        return view('head', [
            'head' => $head,
            'headType' => $headType,
        ]);   
    }

    public function headType(){
        $headType = $this->headRepository->headType();
        return view('headType', [
            'headType' => $headType,
        ]);   
    }

    public function create(){
        $headType = $this->headTypeRepository->all();
        return view('addHead', [
            'headType' => $headType,
        ]);
    }

    public function store(HeadRequest $request){
        $validatedData = $request->validated();
        $duplicate = $this->headRepository->duplicate($validatedData);
        if($duplicate){
            return redirect()->route('head.add')->with(['fails' => 'Head name already exists'])->withInput();
        }
        $this->headRepository->store($validatedData);
        return redirect()->route('head.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function update(Request $request, $id){
        $this->headRepository->update($id, $request->input());      
        return redirect()->route('head')->with('success', 'Record Updated Successfully');
    }

    public function show(Head $head){}

    public function edit(Head $head){}

    public function destroy(Head $head){}
}
