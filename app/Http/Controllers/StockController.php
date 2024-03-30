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
use App\Repositories\VendorRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\ProductCostRepository;
use App\Repositories\ProductMaterialRepository;
use App\Repositories\ReceiveMaterialRepository;

class StockController extends Controller
{
    protected $orderRepository;
    protected $stockRepository;
    protected $headRepository;
    protected $vendorRepository;
    protected $employeeRepository;
    protected $stockItemRepository;
    protected $orderItemRepository;
    protected $productCostRepository;
    protected $receiveMaterialRepository;
    protected $productMaterialRepository;

    public function __construct(
        HeadRepository $headRepository,  
        OrderRepository $orderRepository,  
        StockRepository $stockRepository,  
        EmployeeRepository $employeeRepository,  
        VendorRepository $vendorRepository,  
        OrderItemRepository $orderItemRepository,  
        StockItemRepository $stockItemRepository,  
        ProductCostRepository $productCostRepository,  
        ReceiveMaterialRepository $receiveMaterialRepository,  
        ProductMaterialRepository $productMaterialRepository,  
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->employeeRepository = $employeeRepository;
        $this->vendorRepository = $vendorRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productCostRepository = $productCostRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
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

    public function ajaxPM(Request $request){
        // Ajax Product Material
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
    
    public function ajaxPC(Request $request){
        // Ajax Product Cost
        $productId = $request->input('productId');
        $productCost = $this->productCostRepository->get($productId);
        return response()->json(['data' => $productCost]);
    }

    // ==================================================
    // ==================== Issuance ====================
    // ==================================================

    public function issue(){
        // All Issuance
        $issue = $this->stockRepository->issue();
        return view('issue', [
            'issue' => $issue,
        ]); 
    }
    
    public function create(){
        // Add Issuance
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $order = $this->orderRepository->active();
        $count = $this->stockRepository->refNo();
        return view('addIssue', [
            'count' => $count,
            'order' => $order,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            'vendor' => $vendor,
        ]);
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
        $works = $request->input('work_logs');
        $getId = $this->stockRepository->store($validatedData);
        $this->storeSI($getId, $ptid, $mid, $quantities, $stages, $works);
        if(!$request->has('issue_id')){
            return redirect()->route('stock.show', $getId)->with('success', 'Record Inserted Successfully');
        } else {
            return redirect()->route('rstock.show', $getId)->with('success', 'Record Inserted Successfully');
        }
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

    public function edit(Stock $id){
        // Edit Issuance
        $issueItem = $this->stockItemRepository->get($id->stock_id);
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
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
            'vendor' => $vendor,
        ]);
    }
    
    public function update(Request $request, $id){
        // Update Issuance
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->stockRepository->update($id, $request->input());
        $this->stockItemRepository->update($id, $request->input());
        if(!$request->has('receive_issue_id')){
            return redirect()->route('stock.show', $id)->with('success', 'Record Updated Successfully');
        } else {
            return redirect()->route('rstock.show', $id)->with('success', 'Record Updated Successfully');
        }
    }

    // ==================================================
    // ==============-= Receive Issuance ========-=======
    // ==================================================

    public function rIssue(){
        // All Receive Issuance
        $receive = $this->stockRepository->receive();
        return view('receiveIssue', [
            'receive' => $receive,
        ]); 
    }

    public function rCreate($id){
        // Receive Issuance
        $head = $this->headRepository->get('12');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);
        $count = $this->stockRepository->refNo2($id);
        return view('addReceiveIssue', [
            'count' => $count,
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
            'issueItemUnique' => $issueItem,
        ]);
    }
    
    public function rShow($id){
        // Show Receive Issuance
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);
        return view('receiveIssueInfo', [
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    public function rEdit($id){
        // Edit Receive Issuance
        $head = $this->headRepository->get('12');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($issue['receive_issue_id']);
        $receiveItem = $this->stockItemRepository->get($id);
        $workLog = $this->stockItemRepository->workLog($id);
        return view('editReceiveIssue', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
            'receiveItem' => $receiveItem,
            'issueItemUnique' => $issueItem,
            'workLog' => $workLog,
        ]);
    }
    
    public function destroy(Stock $stock){}

    private function storeSI($getId, $ptids, $mids, $quantities, $stages, $works){
        // Store Issuance Items
        foreach ($quantities as $key => $quantity) {
            $ptid = $ptids[$key] ?? null;
            $mid = $mids[$key] ?? null;
            $stage = $stages[$key] ?? null;
            $work = $works[$key] ?? 0;
            $stockItem = [
                'stock_id' => $getId,
                'product_type_id' => $ptid,
                'material_id' => $mid,
                'quantity' => $quantity,
                'stage_id' => $stage,
                'work_logs' => $work,
            ];
            $this->stockItemRepository->store($stockItem);
        }
    }
}
