<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use App\Http\Requests\StockRequest;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\ProductMaterialRepository;
use App\Repositories\ReceiveMaterialRepository;

class StockController extends Controller
{
    protected $orderRepository;
    protected $stockRepository;
    protected $headRepository;
    protected $employeeRepository;
    protected $orderItemRepository;
    protected $receiveMaterialRepository;
    protected $stockItemRepository;
    protected $productMaterialRepository;

    public function __construct(
        OrderRepository $orderRepository,  
        StockRepository $stockRepository,  
        HeadRepository $headRepository,  
        EmployeeRepository $employeeRepository,  
        OrderItemRepository $orderItemRepository,  
        ReceiveMaterialRepository $receiveMaterialRepository,  
        StockItemRepository $stockItemRepository,  
        ProductMaterialRepository $productMaterialRepository,  
    ){
        $this->middleware(['auth', 'all']);
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->headRepository = $headRepository;
        $this->employeeRepository = $employeeRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->productMaterialRepository = $productMaterialRepository;
    }

    public function index(){
        // Available Stock
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        return view('stock', [
            'stock' => $stock,
            'pstock' => $pstock,
        ]); 
    }

    public function issue(){
        // All Issuance
        $issue = $this->stockRepository->issue();
        return view('issue', [
            'issue' => $issue,
        ]); 
    }

    public function rIssue(){
        // All Receive Issuance
        $receive = $this->stockRepository->receive();
        return view('receiveIssue', [
            'receive' => $receive,
        ]); 
    }

    public function create(){
        // Add Issuance
        $employee = $this->employeeRepository->wages();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $order = $this->orderRepository->active();
        return view('addIssue', [
            'order' => $order,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
        ]);
    }

    public function rCreate($id){
        // Receive Issuance
        $head = $this->headRepository->get('12');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);
        return view('addReceiveIssue', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
            'issueItemUnique' => $issueItem,
        ]);
    }
    
    public function ajaxPM(Request $request){
        $productId = $request->input('productId');
        $productMaterial = $this->productMaterialRepository->get($productId);
        $stockItem = $this->stockItemRepository->pStockGet($productId);
        return response()->json([
            'materials' => $productMaterial,
            'stockItems' => $stockItem
        ]);
    }

    public function ajaxPT(Request $request){
        // Ajax Product Type
        $orderId = $request->input('orderId');
        $orderItem = $this->orderItemRepository->get($orderId);
        return response()->json(['data' => $orderItem]);
    }

    public function store(StockRequest $request){
        // Store Issuance
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $ptid = $request->input('product_type_id');
        $quantities = $request->input('quantity');
        $mid = $request->input('material_id');
        $stages = $request->input('stage_id');
        $getId = $this->stockRepository->store($validatedData);
        $this->storeSI($getId, $ptid, $mid, $quantities, $stages);
        return redirect()->route('stock.add')->with('success', 'Record Inserted Successfully');
    }

    public function rStore(StockRequest $request){
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $ptid = $request->input('product_type_id');
        $mid = $request->input('material_id');
        $stages = $request->input('stage_id');
        $quantities = $request->input('quantity');
        $ptid = array_values(array_slice($ptid, 1));
        $mid = array_values(array_slice($mid, 1));
        $stages = array_values(array_slice($stages, 1));
        $getId = $this->stockRepository->store($validatedData);
        $this->storeSI($getId, $ptid, $mid, $quantities, $stages);
        return redirect()->route('stock.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        // Show Issuance
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);
        return view('issueInfo', [
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    public function rShow($id){
        // Show Issuance
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);
        return view('receiveIssueInfo', [
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }
    
    public function edit(Stock $id){
        // Edit Issuance
        $issueItem = $this->stockItemRepository->get($id->stock_id);
        $employee = $this->employeeRepository->wages();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $order = $this->orderRepository->active();
        return view('editIssue', [
            'issue' => $id,
            'issueItem' => $issueItem,
            'order' => $order,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, $id){
        // Update Issuance
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->stockRepository->update($id, $request->input());
        $this->stockItemRepository->update($id, $request->input());
        return redirect()->route('stock.show', $id)->with('success', 'Record Updated Successfully');
    }
    
    public function destroy(Stock $stock){}

    private function storeSI($getId, $ptids, $mids, $quantities, $stages){
        // Store Issuance Items
        foreach ($quantities as $key => $quantity) {
            $ptid = $ptids[$key] ?? null;
            $mid = $mids[$key] ?? null;
            $stage = $stages[$key] ?? '1';
            $stockItem = [
                'stock_id' => $getId,
                'product_type_id' => $ptid,
                'material_id' => $mid,
                'quantity' => $quantity,
                'stage_id' => $stage,
            ];
            $this->stockItemRepository->store($stockItem);
        }
    }
}
