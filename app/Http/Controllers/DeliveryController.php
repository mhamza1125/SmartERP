<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\BankRepository;
use App\Repositories\HeadRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Http\Requests\DeliveryRequest;
use App\Repositories\DeliveryRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\DeliveryBoxRepository;
use App\Repositories\TransactionRepository;

class DeliveryController extends Controller
{
    protected $headRepository;
    protected $bankRepository;
    protected $orderRepository;
    protected $stockRepository;
    protected $deliveryRepository;
    protected $stockItemRepository;
    protected $deliveryBoxRepository;
    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        BankRepository $bankRepository,
        OrderRepository $orderRepository,
        StockRepository $stockRepository,
        DeliveryRepository $deliveryRepository,
        StockItemRepository $stockItemRepository, 
        DeliveryBoxRepository $deliveryBoxRepository,
        TransactionRepository $transactionRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->bankRepository = $bankRepository;
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->deliveryBoxRepository = $deliveryBoxRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index(){
        $delivery = $this->deliveryRepository->all();
        return view('delivery', [
            'delivery' => $delivery,
        ]); 
    }

    public function create($id){}
    
    public function create2($id){
        $order = $this->orderRepository->get($id);
        $stock = $this->stockItemRepository->orderDelivery($id);
        $vehicle = $this->stockItemRepository->stockVehicle($id);
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');
        return view('addDelivery', [
            'bank' => $bank,
            'expense' => $expense,
            'order' => $order,
            'stock' => $stock,
            'vehicle' => $vehicle,
        ]);
    }

    public function store(DeliveryRequest $request){
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        // Stock Items / Delivery Items / Container Vehicles
        $ptid = $request->input('product_type_id');
        $quantities = $request->input('quantity');
        $mid = $request->input('material_id');
        $stages = $request->input('stage_id');
        $getId = $this->stockRepository->store($validatedData);
        $validatedData['stock_id'] = $getId;
        // Transaction / Expense
        $heads = $request->input('payee_id');
        $banks = $request->input('bank_id');
        $debits = $request->input('debit');
        $remarks = $request->input('remarks');
        // Delivery Boxes / Vehicles
        $vehicles = $request->input('vehicle_no');
        $rowQtys = $request->input('rowQty');
        $totalQtys = $request->input('totalQty');
        // Order Status
        $orderStatus = ['order_status' => $request->input('order_status')];
        // Insertion to DB
        $get = $this->deliveryRepository->store($validatedData);
        $validatedData['delivery_id'] = $get;
        $this->orderRepository->update($request->input('order_id'), $orderStatus);
        $this->storeSI($getId, $ptid, $mid, $quantities, $stages); // Delivery Items
        $this->storeEI($validatedData, $heads, $banks, $debits, $remarks); // Expenses
        $this->storeDB($validatedData, $vehicles, $rowQtys, $totalQtys); // Delivery Boxes
        return redirect()->route('delivery.show', $get)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $delivery = $this->deliveryRepository->get($id);
        $deliveryItem = $this->stockItemRepository->delivery($id);
        $transaction = $this->transactionRepository->delivery($id);
        $deliveryBox = $this->deliveryBoxRepository->get($id);
        return view('deliveryInfo', [
            'delivery' => $delivery,
            'deliveryBox' => $deliveryBox,
            'transaction' => $transaction,
            'deliveryItem' => $deliveryItem,
        ]);
    }
    
    public function edit($id){
        $order = $this->deliveryRepository->get($id); // Delivery
        $stock = $this->stockItemRepository->orderDelivery($order['order_id']);
        $vehicle = $this->stockItemRepository->stockVehicle($order['order_id']);
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');
        $deliveryItem = $this->stockItemRepository->delivery($id);
        $deliveryBox = $this->deliveryBoxRepository->get($id);
        $transaction = $this->transactionRepository->delivery($id);
        return view('editDelivery', [
            'bank' => $bank,
            'expense' => $expense,
            'order' => $order,
            'stock' => $stock,
            'vehicle' => $vehicle,
            'deliveryBox' => $deliveryBox,
            'deliveryItem' => $deliveryItem,
            'transaction' => $transaction,
        ]); 
    }

    public function update(Request $request, $id){
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->transactionRepository->updateDE($id, $request->input());
        $this->stockRepository->update($request->input('stock_id'), $request->input());
        $this->deliveryRepository->update($id, $request->input());
        $orderStatus = ['order_status' => $request->input('order_status')];
        $this->orderRepository->update($request->input('order_id'), $orderStatus);
        $this->deliveryBoxRepository->delete($id);
        $this->storeDB($id, $request->input('vehicle_no'),
                $request->input('rowQty'), $request->input('totalQty'));
        $this->stockItemRepository->update($request->input('stock_id'), $request->input());
        return redirect()->route('delivery.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function updateStatus($id, $status){
        $deliveryStatus = ['delivery_status' => $status];        
        $this->deliveryRepository->update($id, $deliveryStatus);
        return redirect()->route('delivery')->with('success', 'Status Updated Successfully');    
    }
    
    public function destroy(Purchase $purchase){}

    private function storeSI($getId, $ptids, $mids, $quantities, $stages){
        // Store Delivery Items
        foreach ($quantities as $key => $quantity) {
            if($quantity > 0){
                $ptid = $ptids[$key] ?? null;
                $mid = $mids[$key] ?? null;
                $stage = $stages[$key] ?? null;
                $stockItem = [
                    'stock_id' => $getId,
                    'product_type_id' => $ptid,
                    'material_id' => $mid,
                    'quantity' => $quantity,
                    'stage_id' => $stage,
                    'work_logs' => '0',
                    'work_wages' => '0',
                ];
                $this->stockItemRepository->store($stockItem);
            }
        }
    }
    
    private function storeEI($validatedData, $heads, $banks, $debits, $remarks){
        // Store Delivery Expense
        if(!empty($debits)){
            foreach ($debits as $key => $debit) {
                $head = $heads[$key] ?? null;
                $bank = $banks[$key] ?? null;
                $remark = $remarks[$key] ?? null;
                $transaction = [
                    'transaction_to' => 'expense',
                    'transaction_date' => $validatedData['stock_date'],
                    'transaction_type' => 'deliveryExpense',
                    'order_id' => $validatedData['delivery_id'],
                    'bank_id' => $bank,
                    'debit' => $debit,
                    'payee_id' => $head,
                    'payee_bank_id' => '0',
                    'description' => $remark,
                ];
                $this->transactionRepository->store($transaction);
            }
        }        
    }

    private function storeDB($validatedData, $vehicles, $rowQtys, $totalQtys){
        // Store Delivery Boxes
        if(!empty($rowQtys)){
            foreach ($rowQtys as $key => $rowQty) {
                $totalQty = $totalQtys[$key] ?? null;
                $vehicle = $vehicles[$key] ?? null;
                $dBoxes = [
                    'delivery_id' => $validatedData['delivery_id'] ?? $validatedData,
                    'vehicle_no' => $vehicle,
                    'rowQty' => $rowQty,
                    'totalQty' => $totalQty,
                ];
                $this->deliveryBoxRepository->store($dBoxes);
            }
        }
    }
}
