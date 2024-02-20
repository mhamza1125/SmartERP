<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ImageRepository;
use App\Repositories\OrderRepository;
use App\Http\Requests\PurchaseRequest;
use App\Repositories\VendorRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\PurchaseItemRepository;

class PurchaseController extends Controller
{
    protected $imageRepository;
    protected $orderRepository;
    protected $vendorRepository;
    protected $purchaseRepository;
    protected $materialRepository;
    protected $purchaseItemRepository;

    public function __construct(
        OrderRepository $orderRepository,
        ImageRepository $imageRepository,
        VendorRepository $vendorRepository, 
        PurchaseRepository $purchaseRepository, 
        MaterialRepository $materialRepository, 
        PurchaseItemRepository $purchaseItemRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->orderRepository = $orderRepository;
        $this->imageRepository = $imageRepository;
        $this->vendorRepository = $vendorRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->materialRepository = $materialRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
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
        return view('addPurchase', [
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
        $purchase = [
            'purchase_no' => $request->input('purchase_no'),
            'vendor_id' => $request->input('vendor_id'),
            'purchase_date' => $request->input('purchase_date'),
            'description' => $request->input('description'),
        ];
        $materials = $request->input('material_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');

        $getId = $this->purchaseRepository->store($validatedData);
        
        foreach ($prices as $key => $price){
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
        return redirect()->route('purchase.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $purchase = $this->purchaseRepository->get($id);
        return view('purchaseInfo', [
            'purchase' => $purchase,
        ]);
    }
    
    public function edit(Purchase $id){
        $department = $this->orderRepository->get('3');
        $purchaseType = $this->orderRepository->get('9');
        $city = $this->orderRepository->get('8');
        return view('editPurchase', [
            'purchase' => $id,
            'department' => $department,
            'purchaseType' => $purchaseType,
            'city' => $city,
        ]);
    }

    public function update(Request $request, $id){
        $getId = $this->purchaseRepository->update($id, $request->input());      
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'purchase', 'purchases', $getId);        
            }
        }
        return redirect()->route('purchase.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Purchase $purchase){}
}
