<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnRequest;
use App\Repositories\ReturnRepository;
use App\Repositories\ReceiveRepository;
use App\Repositories\ReturnMaterialRepository;
use App\Repositories\ReceiveMaterialRepository;

class ReturnController extends Controller
{
    protected $returnRepository;
    protected $receiveRepository;
    protected $receiveMaterialRepository;
    protected $returnMaterialRepository;

    public function __construct(
        ReturnRepository $returnRepository, 
        ReceiveRepository $receiveRepository, 
        ReceiveMaterialRepository $receiveMaterialRepository, 
        ReturnMaterialRepository $returnMaterialRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->returnRepository = $returnRepository;
        $this->receiveRepository = $receiveRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
        $this->returnMaterialRepository = $returnMaterialRepository;
    }

    public function index(){
        $return = $this->returnRepository->all();
        return view('return', [
            'return' => $return,
        ]); 
    }

    public function create($id){  
        $receive = $this->receiveRepository->get($id);
        $receiveMaterial = $this->receiveMaterialRepository->get($id);
        return view('addReturn', [
            'receive' => $receive,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    public function store(ReturnRequest $request){
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $rid = $request->input('receive_material_id');
        $quantities = $request->input('quantity');
        $remarks = $request->input('remarks');
        $getId = $this->returnRepository->store($validatedData);
        $this->storeRM($getId, $rid, $quantities, $remarks);
        return redirect()->route('return.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $return = $this->returnRepository->get($id);
        $returnMaterial = $this->returnMaterialRepository->get($id);
        return view('returnInfo', [
            'return' => $return,
            'returnMaterial' => $returnMaterial,
        ]);
    }
    
    public function edit($id){
        $return = $this->returnRepository->get($id);
        $returnMaterial = $this->returnMaterialRepository->get($id);
        $receiveMaterial = $this->receiveMaterialRepository->get($return['receive_id']);
        return view('editReturn', [
            'return' => $return,
            'returnMaterial' => $returnMaterial,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    public function update(Request $request, $id){
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->returnRepository->update($id, $request->input());
        $this->returnMaterialRepository->update($id, $request->input());
        // $rid = $request->input('receive_material_id');
        // $quantities = $request->input('quantity');
        // $remarks = $request->input('remarks');
        // $this->returnMaterialRepository->delete($id);
        // $this->storeRM($id, $rid, $quantities, $remarks);
        return redirect()->route('return.show', $id)->with('success', 'Record Updated Successfully');
    }
    
    public function destroy(ReturnMaterial $return){}

    private function storeRM($getId, $rids, $quantities, $remarks){
        foreach ($quantities as $key => $quantity) {
            $id = $rids[$key] ?? null;
            $remark = $remarks[$key] ?? null;
            $returnMaterial = [
                'return_id' => $getId,
                'receive_material_id' => $id,
                'quantity' => $quantity,
                'remarks' => $remark,
            ];
            $this->returnMaterialRepository->store($returnMaterial);
        }
    }
}
