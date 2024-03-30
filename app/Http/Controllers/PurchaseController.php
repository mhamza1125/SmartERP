<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Http\Requests\PurchaseRequest;
use App\Repositories\VendorRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\ReceiveMaterialRepository;

class PurchaseController extends Controller
{
    protected $orderRepository;
    protected $vendorRepository;
    protected $purchaseRepository;
    protected $materialRepository;
    protected $purchaseItemRepository;

    public function __construct(
        OrderRepository $orderRepository,
        VendorRepository $vendorRepository, 
        PurchaseRepository $purchaseRepository, 
        MaterialRepository $materialRepository, 
        PurchaseItemRepository $purchaseItemRepository, 
        ReceiveMaterialRepository $receiveMaterialRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->orderRepository = $orderRepository;
        $this->vendorRepository = $vendorRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->materialRepository = $materialRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
    }

    public function index(){
        $purchase = $this->purchaseRepository->all();
        return view('purchase', [
            'purchase' => $purchase,
        ]); 
    }

    public function create(){
        $order = $this->orderRepository->active();
        $vendor = $this->vendorRepository->all();
        $material = $this->materialRepository->all();
        $count = $this->purchaseRepository->refNo();
        return view('addPurchase', [
            'count' => $count,
            'order' => $order,
            'vendor' => $vendor,
            'material' => $material,
        ]);
    }

    public function store(PurchaseRequest $request){
        $validatedData = $request->validated();
        if (!$request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $materials = $request->input('material_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');
        $getId = $this->purchaseRepository->store($validatedData);
        $this->storePI($getId, $materials, $prices, $quantities);
        return redirect()->route('purchase.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $purchase = $this->purchaseRepository->get($id); // Old/New
        $purchaseItem = $this->purchaseItemRepository->get($id); // Old
        $receiveSum = $this->receiveMaterialRepository->rSum($id);
        $receiveAll = $this->receiveMaterialRepository->rAll($id);
        $totalTimes = $this->receiveMaterialRepository->times($id);

        return view('purchaseInfo', [
            'purchase' => $purchase, // Old/New
            'purchaseItem' => $purchaseItem, // Old
            'receiveSum' => $receiveSum,
            'receiveAll' => $receiveAll,
            'totalTimes' => $totalTimes,
            'count' => $totalTimes->count(),
        ]);
    }
    
    public function edit(Purchase $id){
        $order = $this->orderRepository->active();
        $vendor = $this->vendorRepository->all();
        $material = $this->materialRepository->all();
        $purchaseItem = $this->purchaseItemRepository->get($id->purchase_id);
        return view('editPurchase', [
            'purchase' => $id,
            'order' => $order,
            'vendor' => $vendor,
            'material' => $material,
            'purchaseItem' => $purchaseItem,
        ]);
    }

    public function update(Request $request, $id){
        if (!$request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->purchaseRepository->update($id, $request->input());
        $this->purchaseItemRepository->update($id, $request->input());
        return redirect()->route('purchase.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Purchase $purchase){}

    private function storePI($getId, $materials, $prices, $quantities){
        foreach ($prices as $key => $price) {
            $material = $materials[$key] ?? null;
            $quantity = $quantities[$key] ?? null;
            $total = $price * $quantity;
            $purchaseItem = [
                'purchase_id' => $getId,
                'material_id' => $material,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $this->purchaseItemRepository->store($purchaseItem);
        }
    }
}
