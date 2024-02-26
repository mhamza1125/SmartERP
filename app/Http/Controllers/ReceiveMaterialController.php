<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReceiveMaterial;
use App\Http\Controllers\Controller;
use App\Repositories\MaterialRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\PurchaseItemRepository;
use App\Http\Requests\ReceiveMaterialRequest;
use App\Repositories\ReceiveMaterialRepository;

class ReceiveMaterialController extends Controller
{
    protected $purchaseRepository;
    protected $materialRepository;
    protected $purchaseItemRepository;
    protected $receiveMaterialRepository;

    public function __construct(
        PurchaseRepository $purchaseRepository, 
        MaterialRepository $materialRepository, 
        PurchaseItemRepository $purchaseItemRepository, 
        ReceiveMaterialRepository $receiveMaterialRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->purchaseRepository = $purchaseRepository;
        $this->materialRepository = $materialRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
    }

    public function index(){
        $receive = $this->receiveMaterialRepository->all();
        return view('receiveMaterial', [
            'receive' => $receive,
        ]); 
    }

    public function create($id){        
        $purchase = $this->purchaseRepository->get($id);
        $purchaseItem = $this->purchaseItemRepository->receive($id);
        return view('addReceiveMaterial', [
            'purchase' => $purchase,
            'purchaseItem' => $purchaseItem,
        ]);
    }

    public function store(ReceiveMaterialRequest $request){
        $validatedData = $request->validated();
        $id = $request->input('purchase_item_id');
        $quantities = $request->input('quantity');
        $date = $request->input('receive_date');

        $this->storeRM($id, $quantities, $date);

        return redirect()->route('purchase')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $purchase = $this->purchaseRepository->get($id);
        $receiveMaterial = $this->receiveMaterialRepository->get($id);
        $receiveMaterialAll = $this->receiveMaterialRepository->getEach($id);
        $totalReceive = $this->receiveMaterialRepository->times($id);

        return view('receiveInfo', [
            'purchase' => $purchase,
            'receiveMaterial' => $receiveMaterial,
            'receiveMaterialAll' => $receiveMaterialAll,
            'totalReceive' => $totalReceive,
            'count' => $totalReceive->count(),
        ]);
    }
    
    public function edit(Receive $id){}

    public function update(Request $request, $id){}
    
    public function destroy(Purchase $purchase){}

    private function storeRM($getIds, $quantities, $date){
        foreach ($quantities as $key => $quantity) {
            $id = $getIds[$key] ?? null;
            $receiveMaterial = [
                'purchase_item_id' => $id,
                'quantity' => $quantity,
                'receive_date' => $date,
            ];
            $this->receiveMaterialRepository->store($receiveMaterial);
        }
    }
}
