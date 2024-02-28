<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReturnMaterial;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnRequest;
use App\Repositories\ReturnRepository;
use App\Repositories\ReceiveRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\PurchaseRepository;
use App\Http\Requests\ReturnMaterialRequest;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\ReturnMaterialRepository;
use App\Repositories\ReceiveMaterialRepository;

class ReturnController extends Controller
{
    protected $returnRepository;
    protected $receiveRepository;
    protected $purchaseRepository;
    protected $materialRepository;
    protected $purchaseItemRepository;
    protected $receiveMaterialRepository;
    protected $returnMaterialRepository;

    public function __construct(
        ReturnRepository $returnRepository, 
        ReceiveRepository $receiveRepository, 
        PurchaseRepository $purchaseRepository, 
        MaterialRepository $materialRepository, 
        PurchaseItemRepository $purchaseItemRepository, 
        ReceiveMaterialRepository $receiveMaterialRepository, 
        ReturnMaterialRepository $returnMaterialRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->returnRepository = $returnRepository;
        $this->receiveRepository = $receiveRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->materialRepository = $materialRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
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
        $getId = $this->returnRepository->store($validatedData);
        $this->storeRM($getId, $rid, $quantities);
        return redirect()->route('receive')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $return = $this->returnRepository->get($id);
        $returnMaterial = $this->returnMaterialRepository->get($id);
        return view('returnInfo', [
            'return' => $return,
            'returnMaterial' => $returnMaterial,
        ]);
    }
    
    public function edit(ReturnMaterial $id){}

    public function update(Request $request, $id){}
    
    public function destroy(ReturnMaterial $return){}

    private function storeRM($getId, $rids, $quantities){
        foreach ($quantities as $key => $quantity) {
            if($quantity){
                $id = $rids[$key] ?? null;
                $returnMaterial = [
                    'return_id' => $getId,
                    'receive_material_id' => $id,
                    'quantity' => $quantity,
                ];
                $this->returnMaterialRepository->store($returnMaterial);
            }
        }
    }
}
