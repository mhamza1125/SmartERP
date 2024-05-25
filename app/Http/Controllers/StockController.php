<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use App\Http\Requests\StockRequest;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\VendorRepository;
use App\Repositories\MachineRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\ProductCostRepository;
use App\Repositories\ProductMaterialRepository;

class StockController extends Controller
{
    protected $headRepository;
    protected $imageRepository;
    protected $orderRepository;
    protected $stockRepository;
    protected $vendorRepository;
    protected $machineRepository;
    protected $materialRepository;
    protected $employeeRepository;
    protected $stockItemRepository;
    protected $orderItemRepository;
    protected $productCostRepository;
    protected $productMaterialRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        StockRepository $stockRepository,  
        OrderRepository $orderRepository,  
        VendorRepository $vendorRepository,  
        MachineRepository $machineRepository,  
        MaterialRepository $materialRepository,
        EmployeeRepository $employeeRepository,
        OrderItemRepository $orderItemRepository,  
        StockItemRepository $stockItemRepository,  
        ProductCostRepository $productCostRepository,  
        ProductMaterialRepository $productMaterialRepository,  
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->vendorRepository = $vendorRepository;
        $this->machineRepository = $machineRepository;
        $this->materialRepository = $materialRepository;
        $this->employeeRepository = $employeeRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productCostRepository = $productCostRepository;
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

    public function wages(){
        // Employee / Vendor Wages
        $wages = $this->stockRepository->wages();
        $employeeWages = $wages['employees'];
        $vendorWages = $wages['vendors'];
        foreach (['employeeWages', 'vendorWages'] as $key) {
            foreach ($$key as &$item) {
                $item = (object)$item;
            }
        }
        return view('wages', [
            'employeeWages' => $employeeWages,
            'vendorWages' => $vendorWages,
        ]); 
    }

    public function wShow(Request $request, $id){
        // Show Monthly & Filtered Wages
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        if(!empty($dfrom) && !empty($dto)){
            $wages = $this->stockRepository->wagesInfoFilter($issue, $dfrom, $dto);
        }else{
            $wages = $this->stockRepository->wagesInfo($issue);
        }
        $totalWages = $wages->sum('total_wages');
    
        return view('wagesInfo', [
            'head' => $head,
            'issue' => $issue,
            'wages' => $wages,
            'totalWages' => $totalWages,
            'dfrom' => $dfrom,
            'dto' => $dto,
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

    public function ajaxPS(Request $request){
        // Ajax Product Stage
        $productId = $request->input('productId');
        $pstage = $this->headRepository->getStageAjax($productId);
        return response()->json(['data' => $pstage]);
    }
    
    public function ajaxPC(Request $request){
        // Ajax Product Cost
        $productId = $request->input('productId');
        $productCost = $this->productCostRepository->pcost($productId);
        return response()->json(['data' => $productCost]);
    }

    public function ajaxMQty(Request $request){
        // Ajax Material Qty against Order
        $orderId = $request->input('orderId');
        $materialId = $request->input('materialId');
        $estimate = $this->orderItemRepository->estimateMaterial($orderId, $materialId);
        return response()->json(['data' => $estimate]);
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

    public function issue2(){
        // All Issuance
        $issue = $this->stockRepository->issueMaterial();
        return view('issueMaterial', [
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
        $stage = $this->headRepository->get('12');
        return view('addIssue', [
            'count' => $count,
            'order' => $order,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            'vendor' => $vendor,
            'stage' => $stage,
        ]);
    }

    public function create2(){
        // Add Machine Material Issuance
        $employee = $this->employeeRepository->wages();
        // $vendor = $this->vendorRepository->worker();
        $material = $this->materialRepository->machine();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $machine = $this->machineRepository->all();
        $count = $this->stockRepository->refNo();
        return view('addIMM', [
            'count' => $count,
            'machine' => $machine,
            'material' => $material,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            // 'vendor' => $vendor,
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
        if($request->has('issue_id')){ // Add Receive Issuance
            $this->stockRepository->update($request->input('issue_id'), ['stock_status' => $request->input('stock_status')]);
        }
        $getId = $this->stockRepository->store($validatedData);
        $this->storeSI($getId, $ptid, $mid, $quantities, $stages, $works, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'issuance', 'stocks', $getId);        
            }
        }
        if ($request->has('machine_id')){
            return redirect()->route('mstock.show', $getId)->with('success', 'Record Inserted Successfully');
        } elseif (!$request->has('issue_id')){
            return redirect()->route('stock.show', $getId)->with('success', 'Record Inserted Successfully');
        } else {
            return redirect()->route('rstock.show', $getId)->with('success', 'Record Inserted Successfully');
        }
    }
    
    public function show($id){
        // Show Issuance
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getAvg($id);
        $issueAll = $this->stockItemRepository->getAll($id);
        $issueSum = $this->stockItemRepository->getSum($id);
        $totalTimes = $this->stockItemRepository->times($id);
        return view('issueInfo', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
            'issueAll' => $issueAll,
            'issueSum' => $issueSum,
            'totalTimes' => $totalTimes,
            'count' => $totalTimes->count(),
        ]);
    }

    public function show2($id){
        // Show Machine Material Issuance
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getMM($id);
        $image = $this->imageRepository->image('stocks', $id);
        return view('issueMMInfo', [
            'image' => $image,
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    public function dailyIssue(Request $request){
        // Daily / Filtered Issuance
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $tname = $request->input('table_name');
        $oid = $request->input('order_id');
        $tid = $request->input('employee_id');
        $order = $this->orderRepository->all();
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        if(!empty($dfrom) && !empty($dto)){
            $issueItem = $this->stockItemRepository->dailyIssueFilter($dfrom, $dto, $tname, $tid, $oid);
        }else{
            $issueItem = $this->stockItemRepository->dailyIssue();
        }
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                $key = $item->product_type_id . '|' . $item->ifname;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }
        return view('dailyIssue', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oid' => $oid,
            'tid' => $tid,
            'tname' => $tname,
            'order' => $order,
            'vendor' => $vendor,
            'average' => $average,
            'employee' => $employee,
            'issueItem' => $issueItem,
        ]);
    }

    public function dailyReceive(Request $request){
        // Daily / Filtered Issuance
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $tname = $request->input('table_name');
        $oid = $request->input('order_id');
        $tid = $request->input('employee_id');
        $order = $this->orderRepository->all();
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        if(!empty($dfrom) && !empty($dto)){
            $issueItem = $this->stockItemRepository->dailyReceiveFilter($dfrom, $dto, $tname, $tid, $oid);
        }else{
            $issueItem = $this->stockItemRepository->dailyReceive();
        }
        return view('dailyReceive', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oid' => $oid,
            'tid' => $tid,
            'tname' => $tname,
            'order' => $order,
            'vendor' => $vendor,
            'employee' => $employee,
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
        $stage = $this->headRepository->get('12');
        return view('editIssue', [
            'issue' => $id,
            'issueItem' => $issueItem,
            'order' => $order,
            'stage' => $stage,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            'vendor' => $vendor,
        ]);
    }

    public function edit2(Stock $id){
        // Edit Machine Material Issuance
        $issueItem = $this->stockItemRepository->getMM($id->stock_id);
        $employee = $this->employeeRepository->wages();
        // $vendor = $this->vendorRepository->worker();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $material = $this->materialRepository->machine();
        $machine = $this->machineRepository->all();
        return view('editIMM', [
            'issue' => $id,
            'issueItem' => $issueItem,
            'machine' => $machine,
            'material' => $material,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            // 'vendor' => $vendor,
        ]);
    }
    
    public function update(Request $request, $id){
        // Update Issuance
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->stockRepository->update($id, $request->input());
        $issueId = $request->input('issue_id');
        if($request->has('issue_id')){
            $this->stockRepository->update($id, ['stock_status' => $request->input('stock_status')]);
        }
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'issuance', 'stocks', $id);        
            }
        }
        $this->stockItemRepository->update($id, $request->input());
        if ($request->has('machine_id')){
            return redirect()->route('mstock.show', $id)->with('success', 'Record Updated Successfully');
        } elseif (!$request->has('issue_id')){
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
        $head = $this->headRepository->get('12');
        $receive = $this->stockRepository->receive();
        $issue = $this->stockRepository->receiveIssue();
        return view('receiveIssue', [
            'head' => $head,
            'issue' => $issue,
            'receive' => $receive,
        ]); 
    }

    public function rCreate($id){
        // Receive Issuance
        $head = $this->headRepository->get('12');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getAvg($id);
        $count = $this->stockRepository->refNo2($id);
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                // $key = $item->article_no . '|' . $item->sname;
                $key = $item->product_type_id;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }
        return view('addReceiveIssue', [
            'count' => $count,
            'head' => $head,
            'issue' => $issue,
            'average' => $average,
            'issueItem' => $issueItem,
            'issueItemUnique' => $issueItem,
        ]);
    }
    
    public function rShow($id){
        // Show Receive Issuance
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);
        return view('receiveIssueInfo', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    public function rEdit($id){
        // Edit Receive Issuance
        $head = $this->headRepository->get('12');
        $issue = $this->stockRepository->get($id);
        $date = $this->stockRepository->get($issue['issue_id']);
        $issueItem = $this->stockItemRepository->getAvg($issue['issue_id']);
        $receiveItem = $this->stockItemRepository->get($id);
        $workLog = $this->stockItemRepository->workLog($id);
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                // $key = $item->article_no . '|' . $item->sname;
                $key = $item->product_type_id;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }
        return view('editReceiveIssue', [
            'head' => $head,
            'date' => $date,
            'issue' => $issue,
            'average' => $average,
            'issueItem' => $issueItem,
            'receiveItem' => $receiveItem,
            'issueItemUnique' => $issueItem,
            'workLog' => $workLog,
        ]);
    }
    
    public function destroy(Stock $stock){}

    private function storeSI($getId, $ptids, $mids, $quantities, $stages, $works, $all){
        // Store Issuance Items
        $tid = $all['employee_id'];
        $tname = $all['table_name'];
        foreach ($quantities as $key => $quantity) {
            $ptid = $ptids[$key] ?? 0;
            $mid = $mids[$key] ?? 0;
            $stage = $stages[$key] ?? 0;
            $work = $works[$key] ?? 0;
            $wages = $work ? $this->productCostRepository->wages($ptid, $work, $tid, $tname) : '0';
            $stockItem = [
                'stock_id' => $getId,
                'product_type_id' => $ptid,
                'material_id' => $mid,
                'quantity' => $quantity,
                'stage_id' => $stage,
                'work_logs' => $work,
                'work_wages' => $wages,
            ];
            $this->stockItemRepository->store($stockItem);
        }
    }
}
