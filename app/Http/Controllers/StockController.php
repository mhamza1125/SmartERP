<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMaterial;
use App\Http\Requests\StockRequest;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\ReceiveMaterialRepository;
use App\Repositories\ProductMaterialRepository;

class StockController extends Controller
{
    protected $orderRepository;
    protected $stockRepository;
    protected $employeeRepository;
    protected $orderItemRepository;
    protected $receiveMaterialRepository;
    protected $productMaterialRepository;

    public function __construct(
        OrderRepository $orderRepository,  
        StockRepository $stockRepository,  
        EmployeeRepository $employeeRepository,  
        OrderItemRepository $orderItemRepository,  
        ReceiveMaterialRepository $receiveMaterialRepository,  
        ProductMaterialRepository $productMaterialRepository,  
    ){
        $this->middleware(['auth', 'all']);
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->employeeRepository = $employeeRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
        $this->productMaterialRepository = $productMaterialRepository;
    }

    public function index(){
        $stock = $this->receiveMaterialRepository->stock();
        // $stock = $this->stockRepository->all();
        return view('stock', [
            'stock' => $stock,
        ]); 
    }

    public function create(){
        $product = $this->productMaterialRepository->all();
        $employee = $this->employeeRepository->wages();
        $productMaterial = $this->productMaterialRepository->get('12');
        // $stock = $this->stockRepository->all();
        $stock = $this->receiveMaterialRepository->stock();
        $order = $this->orderRepository->active();
        $orderItem = $this->orderItemRepository->get('13');
        // dd($orderItem);

        return view('addIssue', [
            'order' => $order,
            'stock' => $stock,
            'product' => $product,
            'employee' => $employee,
            'productMaterial' => $productMaterial,
        ]);
    }
    
    public function ajaxPM(Request $request){
        $productId = $request->input('productId');
        $productMaterial = $this->productMaterialRepository->get($productId);
        return response()->json(['data' => $productMaterial]);
    }

    public function ajaxPT(Request $request){
        $orderId = $request->input('orderId');
        $orderItem = $this->orderItemRepository->get($orderId);
        return response()->json(['data' => $orderItem]);
    }

    public function store(StockRequest $request){
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $ptid = $request->input('product_type_id');
        $quantities = $request->input('quantity');
        $mid = $request->input('material_id');
        // dd($validatedData);
        $getId = $this->stockRepository->store($validatedData);
        dd($getId);
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
        $pid = $request->input('purchase_item_id');
        $quantities = $request->input('quantity');
        $inspections = $request->input('inspection_status');
        $this->receiveMaterialRepository->delete($id);
        $this->receiveRepository->update($id, $request->input());
        $this->storeRM($id, $pid, $quantities, $inspections);
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
