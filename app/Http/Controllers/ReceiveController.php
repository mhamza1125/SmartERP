<?php

namespace App\Http\Controllers;

use App\Models\Receive;
use Illuminate\Http\Request;
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
        $inspections = $request->input('inspection_status');
        $getId = $this->receiveRepository->store($validatedData);
        $this->storeRM($getId, $pid, $quantities, $inspections);
        return redirect()->route('receive.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $receive = $this->receiveRepository->get($id);
        $receiveMaterial = $this->receiveMaterialRepository->get($id);
        return view('receiveInfo', [
            'receive' => $receive,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }
    
    public function edit($id){
        $receive = $this->receiveRepository->get($id);
        $receiveMaterial = $this->receiveMaterialRepository->get($id);
        $purchaseItem = $this->purchaseItemRepository->editReceive($receive['purchase_id'], $id);
        return view('editReceive', [
            'receive' => $receive,
            'purchaseItem' => $purchaseItem,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    public function update(Request $request, $id){
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->receiveRepository->update($id, $request->input());
        $this->receiveMaterialRepository->update($id, $request->input());
        // $pid = $request->input('purchase_item_id');
        // $quantities = $request->input('quantity');
        // $inspections = $request->input('inspection_status');
        // $this->receiveMaterialRepository->delete($id);
        // $this->storeRM($id, $pid, $quantities, $inspections);
        return redirect()->route('receive.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function updateStatus($id, $status){
        $receiveStatus = ['receive_status' => $status];        
        $this->receiveRepository->update($id, $receiveStatus);
        return redirect()->route('receive')->with('success', 'Status Updated Successfully');    
    }
    
    public function destroy(ReceiveMaterial $receive){}

    private function storeRM($getId, $pids, $quantities, $inspections){
        foreach ($quantities as $key => $quantity) {
            $pid = $pids[$key] ?? null;
            $status = $inspections[$key] ?? null;
            $receiveMaterial = [
                'receive_id' => $getId,
                'purchase_item_id' => $pid,
                'quantity' => $quantity,
                'inspection_status' => $status,
            ];
            $this->receiveMaterialRepository->store($receiveMaterial);
        }
    }
}
