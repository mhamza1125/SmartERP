<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Http\Requests\PurchaseRequest;
use App\Repositories\VendorRepository;
use App\Repositories\MProcessRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\ReturnMaterialRepository;
use App\Repositories\ReceiveMaterialRepository;

class MProcessController extends Controller
{
    protected $orderRepository;
    protected $vendorRepository;
    protected $mprocessRepository;
    protected $materialRepository;
    protected $purchaseRepository;
    protected $stockItemRepository;
    protected $transactionRepository;
    protected $purchaseItemRepository;
    protected $returnMaterialRepository;
    protected $receiveMaterialRepository;

    public function __construct(
        OrderRepository $orderRepository,
        VendorRepository $vendorRepository, 
        MProcessRepository $mprocessRepository, 
        PurchaseRepository $purchaseRepository, 
        MaterialRepository $materialRepository, 
        StockItemRepository $stockItemRepository, 
        TransactionRepository $transactionRepository, 
        PurchaseItemRepository $purchaseItemRepository, 
        ReturnMaterialRepository $returnMaterialRepository, 
        ReceiveMaterialRepository $receiveMaterialRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->orderRepository = $orderRepository;
        $this->vendorRepository = $vendorRepository;
        $this->mprocessRepository = $mprocessRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->materialRepository = $materialRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->transactionRepository = $transactionRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->returnMaterialRepository = $returnMaterialRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
    }

    public function index(){
        $purchase = $this->purchaseRepository->mprocess();
        return view('mprocess', [
            'purchase' => $purchase,
        ]); 
    }

    public function create(){
        $order = $this->orderRepository->active();
        // $vendor = $this->vendorRepository->all();
        $vendor = $this->vendorRepository->vendor();
        $material = $this->materialRepository->all();
        $count = $this->purchaseRepository->refNo();
        return view('addMProcess', [
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
        $getId = $this->purchaseRepository->store($validatedData);
        $this->storeAll($getId, $validatedData);
        return redirect()->route('mprocess.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $purchase = $this->purchaseRepository->get($id);
        $purchaseItem = $this->mprocessRepository->get($id);
        $receiveSum = $this->receiveMaterialRepository->rSum($id);
        $receiveAll = $this->receiveMaterialRepository->rAll($id);
        $receiveTimes = $this->receiveMaterialRepository->times($id);
        $returnAll = $this->returnMaterialRepository->rAll($id);
        $returnTimes = $this->returnMaterialRepository->times($id);
        $transaction = $this->transactionRepository->getPPayment($id);
        return view('purchaseInfo', [
            'process' => '1',
            'purchase' => $purchase,
            'transaction' => $transaction,
            'purchaseItem' => $purchaseItem,
            'receiveSum' => $receiveSum,
            'receiveAll' => $receiveAll,
            'receiveTimes' => $receiveTimes,
            'count' => $receiveTimes->count(),
            'returnAll' => $returnAll,
            'returnTimes' => $returnTimes,
            'count2' => $returnTimes->count(),
        ]);
    }
    
    public function edit(Purchase $id){
        $order = $this->orderRepository->active();
        $vendor = $this->vendorRepository->all();
        $material = $this->materialRepository->all();
        $purchaseItem = $this->mprocessRepository->get($id->purchase_id);

        return view('editMProcess', [
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
        $this->mprocessRepository->delete($id);
        $this->storeAll($id, $request->input());
        return redirect()->route('mprocess.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Purchase $purchase){}

    private function storeAll($getId, $validatedData){
        $amaterials = $validatedData['amaterial_id'];
        $bmaterials = $validatedData['bmaterial_id'];
        $aquantities = $validatedData['aquantity'];
        $bquantities = $validatedData['bquantity'];
        $prices = $validatedData['price'];

        foreach ($prices as $key => $price) {
            $amaterial = $amaterials[$key] ?? null;
            $bmaterial = $bmaterials[$key] ?? null;
            $aquantity = $aquantities[$key] ?? null;
            $bquantity = $bquantities[$key] ?? null;
            $total = $price * $bquantity;
            
            $purchaseItem = [
                'purchase_id' => $getId,
                'material_id' => $bmaterial,
                'price' => $price,
                'quantity' => $bquantity,
                'total' => $total,
            ];
            
            $stockItem = [
                'stock_id' => '0',
                'product_type_id' => '0',
                'material_id' => $amaterial,
                'quantity' => $aquantity,
                'stage_id' => '0',
                'work_logs' => '0',
                'work_wages' => '0',
            ];

            $pid = $this->purchaseItemRepository->store($purchaseItem);
            $sid = $this->stockItemRepository->store($stockItem);

            $mprocess = [
                'purchase_id' => $getId,
                'purchase_item_id' => $pid,
                'stock_item_id' => $sid,
                'before_mid' => $amaterial,
                'before_qty' => $aquantity,
            ];

            $this->mprocessRepository->store($mprocess);
            // Direct Insertion - Not Used
            $stock = [ 
                // 'stock_id' => '0', 'issue_id' => NULL, 'issue_for' => '0', 'stock_no' => 'I24000000', 'order_id' => '0', 'machine_id' => NULL, 'table_name' => 'mprocess', 'employee_id' => '0', 'stock_type' => '2', 'stock_date' => '2024-06-01', 'stock_status' => '5', 'description' => '',
            ];
        }
    }
}
