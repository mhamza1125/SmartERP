<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReceiveMaterial;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReceiveRequest;
use App\Repositories\ReceiveRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\ReceiveMaterialRepository;

class ReceiveController extends Controller
{
    protected $receiveRepository;
    protected $purchaseRepository;
    protected $purchaseItemRepository;
    protected $receiveMaterialRepository;

    public function __construct(
        ReceiveRepository $receiveRepository, 
        PurchaseRepository $purchaseRepository, 
        PurchaseItemRepository $purchaseItemRepository, 
        ReceiveMaterialRepository $receiveMaterialRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->receiveRepository = $receiveRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
    }

    public function index(){
        $receive = $this->receiveRepository->all();
        return view('receive', [
            'receive' => $receive,
        ]); 
    }

    public function create($id){        
        $purchase = $this->purchaseRepository->get($id);
        $purchaseItem = $this->purchaseItemRepository->receive($id);
        return view('addReceive', [
            'purchase' => $purchase,
            'purchaseItem' => $purchaseItem,
        ]);
    }

    public function store(ReceiveRequest $request){
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $pid = $request->input('purchase_item_id');
        $quantities = $request->input('quantity');
        $getId = $this->receiveRepository->store($validatedData);
        $this->storeRM($getId, $pid, $quantities);
        return redirect()->route('purchase')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $receive = $this->receiveRepository->get($id);
        $receiveMaterial = $this->receiveMaterialRepository->get($id);
        return view('receiveInfo', [
            'receive' => $receive,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }
    
    public function edit(ReceiveMaterial $id){}

    public function update(Request $request, $id){}
    
    public function destroy(ReceiveMaterial $receive){}

    private function storeRM($getId, $pids, $quantities){
        foreach ($quantities as $key => $quantity) {
            if($quantity){
                $pid = $pids[$key] ?? null;
                $receiveMaterial = [
                    'receive_id' => $getId,
                    'purchase_item_id' => $pid,
                    'quantity' => $quantity,
                ];
                $this->receiveMaterialRepository->store($receiveMaterial);
            }
        }
    }
}
