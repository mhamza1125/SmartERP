<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\BankRepository;
use App\Repositories\HeadRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Http\Requests\DeliveryRequest;
use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;
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

    protected $customerRepository;

    protected $deliveryRepository;

    protected $stockItemRepository;

    protected $deliveryBoxRepository;

    protected $transactionRepository;

    protected $companyRepository;

    public function __construct(
        HeadRepository $headRepository,
        BankRepository $bankRepository,
        OrderRepository $orderRepository,
        StockRepository $stockRepository,
        CustomerRepository $customerRepository,
        DeliveryRepository $deliveryRepository,
        StockItemRepository $stockItemRepository,
        DeliveryBoxRepository $deliveryBoxRepository,
        TransactionRepository $transactionRepository,
        CompanyRepository $companyRepository,
    ) {
        $this->middleware(['auth', 'all'])->except(['getCustomerOrders', 'getCustomerAddress']);
        $this->middleware('auth')->only(['getCustomerOrders', 'getCustomerAddress']);
        $this->headRepository = $headRepository;
        $this->bankRepository = $bankRepository;
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->customerRepository = $customerRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->deliveryBoxRepository = $deliveryBoxRepository;
        $this->transactionRepository = $transactionRepository;
        $this->companyRepository = $companyRepository;
    }

    public function index()
    {
        $this->authorize('access', Delivery::class);
        $delivery = $this->deliveryRepository->all();
        $customers = $this->customerRepository->all();

        return view('delivery', [
            'delivery' => $delivery,
            'customers' => $customers,
        ]);
    }

    public function create($id)
    {
        // This is GET method
        $this->authorize('create', Delivery::class);
    }

    public function create2($id = null)
    {
        $this->authorize('create', Delivery::class);

        // Check if this is a multi-order delivery request
        $customerId = request()->get('customer_id');
        $orderIds = request()->get('order_ids');
        $editDeliveryId = request()->get('edit_delivery_id');

        if ($customerId && $orderIds) {
            // Multi-order delivery (create or edit)
            return $this->handleMultiOrderCreate($customerId, $orderIds, $editDeliveryId);
        } elseif ($id) {
            // Single order delivery
            return $this->handleSingleOrderCreate($id);
        } else {
            return redirect()->route('delivery')->with('error', 'Invalid delivery request.');
        }
    }

    private function handleSingleOrderCreate($id)
    {
        $order = $this->orderRepository->get($id);
        $stock = $this->stockItemRepository->orderDelivery($id);
        $vehicle = $this->stockItemRepository->stockVehicle($id);
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');
        $company = $this->companyRepository->first();

        // Fetch customer data using the customer_id from the order (server-side)
        $customer = null;
        $customerId = is_array($order) ? ($order['customer_id'] ?? null) : ($order->customer_id ?? null);
        if ($customerId) {
            $customer = $this->customerRepository->get($customerId);
        }

        return view('addDelivery', [
            'bank' => $bank,
            'expense' => $expense,
            'order' => $order,
            'stock' => $stock,
            'vehicle' => $vehicle,
            'company' => $company,
            'customer' => $customer,
            'isMultiOrder' => false,
        ]);
    }

    private function handleMultiOrderCreate($customerId, $orderIdsString, $editDeliveryId = null)
    {
        $orderIds = explode(',', $orderIdsString);

        if (!$customerId || empty($orderIds)) {
            return redirect()->route('delivery')->with('error', 'Invalid customer or orders selected.');
        }

        // Get customer details
        $customer = $this->customerRepository->get($customerId);
        if (!$customer) {
            return redirect()->route('delivery')->with('error', 'Customer not found.');
        }

        // Get all selected orders
        $orders = [];
        $allOrderItems = [];

        foreach ($orderIds as $orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                $orders[] = $order;
                $orderItems = $this->stockItemRepository->orderDelivery($orderId);
                // Convert Collection to array before merging
                $allOrderItems = array_merge($allOrderItems, $orderItems->toArray());
            }
        }

        // Get other required data
        $bank = $this->bankRepository->self();
        $vehicle = $this->stockItemRepository->stockVehicle($orderIds[0]);
        $expense = $this->headRepository->get('7');
        $company = $this->companyRepository->first();

        // If this is an edit request, get existing delivery data
        $existingDelivery = null;
        $deliveryBox = null;
        $deliveryItem = null;
        $transaction = null;
        $relatedOrders = [];

        if ($editDeliveryId) {
            $existingDelivery = $this->deliveryRepository->get($editDeliveryId);
            $deliveryBox = $this->deliveryBoxRepository->get($editDeliveryId);
            $deliveryItem = $this->stockItemRepository->delivery($editDeliveryId);
            $transaction = $this->transactionRepository->delivery($editDeliveryId);
            $relatedOrders = $this->getRelatedOrdersForDelivery($existingDelivery);

            // Use editDelivery view for multi-order delivery edits
            return view('editDelivery', [
                'order' => $existingDelivery,
                'stock' => $allOrderItems,
                'bank' => $bank,
                'vehicle' => $vehicle,
                'expense' => $expense,
                'company' => $company,
                'deliveryBox' => $deliveryBox,
                'deliveryItem' => $deliveryItem,
                'transaction' => $transaction,
                'isMultiOrder' => true,
                'relatedOrders' => $relatedOrders,
            ]);
        }

        // Use addDelivery view for multi-order delivery creation
        return view('addDelivery', [
            'customer' => $customer,
            'orders' => $orders,
            'orderItems' => $allOrderItems,
            'bank' => $bank,
            'vehicle' => $vehicle,
            'expense' => $expense,
            'company' => $company,
            'isMultiOrder' => true,
            'orderIds' => $orderIdsString,
            'editMode' => false,
        ]);
    }

    public function store(DeliveryRequest $request)
    {
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        // Handle multi-order delivery stock number generation
        $orderIds = $request->input('order_ids');
        if ($orderIds) {
            // Multi-order delivery - generate special stock_no with multi-order pattern
            $orderIdArray = explode(',', $orderIds);
            $orderNumbers = [];

            foreach ($orderIdArray as $orderId) {
                $order = $this->orderRepository->get($orderId);
                if ($order) {
                    $orderNumbers[] = $order['order_no'];
                }
            }

            // Create multi-order stock number with comma-separated order numbers
            if (!empty($orderNumbers)) {
                $validatedData['stock_no'] = 'Multi-Order: ' . implode(', ', $orderNumbers);
            }
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

        // Handle both single and multi-order deliveries
        $orderIds = $request->input('order_ids');
        $selectedOrders = $request->input('selected_orders', []);

        if ($orderIds) {
            // Multi-order delivery
            $orderIdArray = explode(',', $orderIds);
            foreach ($orderIdArray as $orderId) {
                $this->orderRepository->update($orderId, $orderStatus);
            }
        } elseif (!empty($selectedOrders)) {
            // Multiple orders selected via checkboxes
            foreach ($selectedOrders as $orderId) {
                $this->orderRepository->update($orderId, $orderStatus);
            }
        } else {
            // Single order delivery (backward compatibility)
            $this->orderRepository->update($request->input('order_id'), $orderStatus);
        }

        $this->storeSI($getId, $ptid, $mid, $quantities, $stages); // Delivery Items
        $this->storeEI($validatedData, $heads, $banks, $debits, $remarks); // Expenses
        $this->storeDB($validatedData, $vehicles, $rowQtys, $totalQtys); // Delivery Boxes

        return redirect()->route('delivery.show', $get)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Delivery::class);
        $delivery = $this->deliveryRepository->get($id);
        $deliveryItem = $this->stockItemRepository->delivery($id);
        $transaction = $this->transactionRepository->delivery($id);
        $deliveryBox = $this->deliveryBoxRepository->get($id);
        $company = $this->companyRepository->first();
        $banks = $this->bankRepository->self();

        // Detect if this is a multi-order delivery
        $isMultiOrder = $this->isMultiOrderDelivery($delivery);

        // If it's a multi-order delivery, get additional information
        $relatedOrders = [];
        if ($isMultiOrder) {
            $relatedOrders = $this->getRelatedOrdersForDelivery($delivery);
        }

        // Check if this is an edit request from delivery list page
        if (request()->has('edit') && $isMultiOrder && count($relatedOrders) > 1) {
            // Redirect to multi-order edit URL
            $customerId = is_array($delivery) ? $delivery['customer_id'] : $delivery->customer_id;
            $orderIds = collect($relatedOrders)->pluck('order_id')->implode(',');
            return redirect()->route('delivery.add', [
                'customer_id' => $customerId,
                'order_ids' => $orderIds,
                'edit_delivery_id' => $id
            ]);
        }

        return view('deliveryInfo', [
            'delivery' => $delivery,
            'deliveryBox' => $deliveryBox,
            'transaction' => $transaction,
            'deliveryItem' => $deliveryItem,
            'company' => $company,
            'banks' => $banks,
            'isMultiOrder' => $isMultiOrder,
            'relatedOrders' => $relatedOrders,
            'customerId' => is_array($delivery) ? $delivery['customer_id'] : $delivery->customer_id,
            'deliveryId' => $id,
        ]);
    }

    /**
     * Print delivery information
     */
    public function printDelivery($id)
    {
        $this->authorize('show', Delivery::class);
        $delivery = $this->deliveryRepository->get($id);
        $deliveryItem = $this->stockItemRepository->delivery($id);
        $transaction = $this->transactionRepository->delivery($id);
        $deliveryBox = $this->deliveryBoxRepository->get($id);

        // Detect if this is a multi-order delivery
        $isMultiOrder = $this->isMultiOrderDelivery($delivery);

        // If it's a multi-order delivery, get additional information
        $relatedOrders = [];
        if ($isMultiOrder) {
            $relatedOrders = $this->getRelatedOrdersForDelivery($delivery);
        }

        return view('print.delivery', [
            'delivery' => $delivery,
            'deliveryBox' => $deliveryBox,
            'transaction' => $transaction,
            'deliveryItem' => $deliveryItem,
            'isMultiOrder' => $isMultiOrder,
            'relatedOrders' => $relatedOrders,
        ]);
    }

    /**
     * Print commercial invoice
     */
    public function printCommercial($id)
    {
        $this->authorize('show', Delivery::class);
        $delivery = $this->deliveryRepository->get($id);
        $deliveryItem = $this->stockItemRepository->delivery($id);
        $company = $this->companyRepository->first();

        // Get optional parameters from query string
        $hsCode = request()->input('hs_code');
        $sellingType = request()->input('selling_type');
        $uom = request()->input('uom');
        $statementOfOrigin = request()->input('statement_of_origin');

        // Get bank details if bank_id is provided in query parameter
        $bankDetails = null;
        if (request()->has('bank_id')) {
            $bankDetailsObj = DB::table('banks')
                ->where('bank_id', request()->input('bank_id'))
                ->first();
            // Convert stdClass object to array for view compatibility
            if ($bankDetailsObj) {
                $bankDetails = (array) $bankDetailsObj;
            }
        }

        // Get packing list for this delivery if it exists
        $packingList = null;
        $packingListData = DB::table('packing_lists')
            ->where('delivery_id', $id)
            ->first();
        
        if ($packingListData) {
            // Count the number of cartons/packages for this packing list
            $cartonCount = DB::table('packing_cartons')
                ->where('packing_list_id', $packingListData->packing_list_id)
                ->count();
            
            $packingList = [
                'packing_list_id' => $packingListData->packing_list_id,
                'carton_count' => $cartonCount,
            ];
        }

        // Get related orders for multi-order deliveries
        $isMultiOrder = $this->isMultiOrderDelivery($delivery);
        $relatedOrders = [];
        if ($isMultiOrder) {
            $relatedOrders = $this->getRelatedOrdersForDelivery($delivery);
        }

        return view('print.delivery-commercial', [
            'delivery' => $delivery,
            'deliveryItem' => $deliveryItem,
            'company' => $company,
            'bankDetails' => $bankDetails,
            'hsCode' => $hsCode,
            'sellingType' => $sellingType,
            'uom' => $uom,
            'statementOfOrigin' => $statementOfOrigin,
            'packingList' => $packingList,
            'isMultiOrder' => $isMultiOrder,
            'relatedOrders' => $relatedOrders,
        ]);
    }

    private function isMultiOrderDelivery($delivery)
    {
        if (!$delivery) return false;

        // Handle both array and object access
        $stockNo = is_array($delivery) ? ($delivery['stock_no'] ?? '') : ($delivery->stock_no ?? '');
        $stockId = is_array($delivery) ? ($delivery['stock_id'] ?? null) : ($delivery->stock_id ?? null);

        if (!$stockId) return false;

        // Check if stock_no contains patterns suggesting multi-order
        $hasMultiOrderPattern = strpos($stockNo, ',') !== false ||
                               strpos($stockNo, 'Multi') !== false ||
                               stripos($stockNo, 'combined') !== false;

        if ($hasMultiOrderPattern) return true;

        // Check if there are many distinct item types (suggesting multiple orders)
        $distinctItemTypes = \DB::table('stock_items')
            ->where('stock_id', $stockId)
            ->distinct()
            ->count(\DB::raw('CONCAT(product_type_id, "_", stage_id)'));

        // If there are many distinct item types, likely multi-order
        if ($distinctItemTypes > 3) return true;

        return false;
    }

    /**
     * Get related orders for a multi-order delivery
     */
    private function getRelatedOrdersForDelivery($delivery)
    {
        if (!$delivery) return [];

        // Handle both array and object access
        $customerId = is_array($delivery) ? ($delivery['customer_id'] ?? null) : ($delivery->customer_id ?? null);
        $stockDate = is_array($delivery) ? ($delivery['stock_date'] ?? null) : ($delivery->stock_date ?? null);
        $stockNo = is_array($delivery) ? ($delivery['stock_no'] ?? '') : ($delivery->stock_no ?? '');
        $stockId = is_array($delivery) ? ($delivery['stock_id'] ?? null) : ($delivery->stock_id ?? null);

        if (!$customerId || !$stockId) return [];

        try {
            // Try to find orders that might be related to this delivery
            // Method 1: Look for orders with similar dates and same customer
            $relatedOrders = \DB::table('orders')
                ->where('customer_id', $customerId)
                ->where('order_date', '>=', \Carbon\Carbon::parse($stockDate ?? now())->subDays(30))
                ->where('order_date', '<=', \Carbon\Carbon::parse($stockDate ?? now())->addDays(7))
                ->where('order_status', '!=', 'Cancelled')
                ->get();

            // Method 2: If stock_no contains multi-order patterns, try to extract order numbers
            if (strpos($stockNo, ',') !== false || stripos($stockNo, 'Multi-Order') !== false) {
                $orderNumbers = [];

                // Handle "Multi-Order: Order1, Order2" format
                if (stripos($stockNo, 'Multi-Order:') !== false) {
                    $orderPart = substr($stockNo, stripos($stockNo, ':') + 1);
                    $orderNumbers = explode(',', $orderPart);
                } else {
                    // Handle direct comma-separated format
                    $orderNumbers = explode(',', $stockNo);
                }

                $orderNumbers = array_map('trim', $orderNumbers);
                $orderNumbers = array_filter($orderNumbers); // Remove empty values

                if (!empty($orderNumbers)) {
                    $ordersFromStockNo = \DB::table('orders')
                        ->whereIn('order_no', $orderNumbers)
                        ->get();

                    if ($ordersFromStockNo->count() > 0) {
                        return $ordersFromStockNo->toArray();
                    }
                }
            }

            // Method 3: Look for orders that have stock items in the same stock_id
            $stockBasedOrders = \DB::table('orders')
                ->join('stocks', 'stocks.order_id', '=', 'orders.order_id')
                ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
                ->where('stock_items.stock_id', $stockId)
                ->select('orders.*')
                ->distinct()
                ->get();

            if ($stockBasedOrders->count() > 1) {
                return $stockBasedOrders->toArray();
            }

            // Return the most likely related orders
            return $relatedOrders->take(5)->toArray();

        } catch (\Exception $e) {
            $deliveryId = is_array($delivery) ? ($delivery['delivery_id'] ?? 'unknown') : ($delivery->delivery_id ?? 'unknown');
            \Log::error('Error getting related orders for delivery', [
                'delivery_id' => $deliveryId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function edit($id)
    {
        $this->authorize('edit', Delivery::class);
        $order = $this->deliveryRepository->get($id); // Delivery
        $stock = $this->stockItemRepository->orderDelivery($order['order_id']);
        $vehicle = $this->stockItemRepository->stockVehicle($order['order_id']);
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');
        $deliveryItem = $this->stockItemRepository->delivery($id);
        $deliveryBox = $this->deliveryBoxRepository->get($id);
        $transaction = $this->transactionRepository->delivery($id);
        $company = $this->companyRepository->first();

        // Detect if this is a multi-order delivery
        $isMultiOrder = $this->isMultiOrderDelivery($order);

        // If it's a multi-order delivery, get additional information
        $relatedOrders = [];
        if ($isMultiOrder) {
            $relatedOrders = $this->getRelatedOrdersForDelivery($order);
        }

        return view('editDelivery', [
            'bank' => $bank,
            'expense' => $expense,
            'order' => $order,
            'stock' => $stock,
            'vehicle' => $vehicle,
            'company' => $company,
            'deliveryBox' => $deliveryBox,
            'deliveryItem' => $deliveryItem,
            'transaction' => $transaction,
            'isMultiOrder' => $isMultiOrder,
            'relatedOrders' => $relatedOrders,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        // Get the delivery to check if it's multi-order
        $delivery = $this->deliveryRepository->get($id);
        $isMultiOrder = $this->isMultiOrderDelivery($delivery);

        $this->transactionRepository->updateDE($id, $request->input());
        $this->stockRepository->update($request->input('stock_id'), $request->input());
        $this->deliveryRepository->update($id, $request->input());
        $orderStatus = ['order_status' => $request->input('order_status')];

        // Handle both single and multi-order deliveries
        $orderIds = $request->input('order_ids');
        if ($orderIds) {
            // Multi-order delivery - update all related orders
            $orderIdArray = explode(',', $orderIds);
            foreach ($orderIdArray as $orderId) {
                $this->orderRepository->update($orderId, $orderStatus);
            }
        } else {
            // Single order delivery
            $this->orderRepository->update($request->input('order_id'), $orderStatus);
        }

        $this->deliveryBoxRepository->delete($id);
        $this->storeDB($id, $request->input('vehicle_no'),
            $request->input('rowQty'), $request->input('totalQty'));
        $this->stockItemRepository->update($request->input('stock_id'), $request->input());

        return redirect()->route('delivery.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function updateStatus($id, $status)
    {
        $deliveryStatus = ['delivery_status' => $status];
        $this->deliveryRepository->update($id, $deliveryStatus);

        return redirect()->route('delivery')->with('success', 'Status Updated Successfully');
    }

    public function destroy(Purchase $purchase)
    {
        $this->authorize('delete', Delivery::class);
    }

    private function storeSI($getId, $ptids, $mids, $quantities, $stages)
    {
        // Store Delivery Items
        foreach ($quantities as $key => $quantity) {
            if ($quantity > 0) {
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

    private function storeEI($validatedData, $heads, $banks, $debits, $remarks)
    {
        // Store Delivery Expense
        if (! empty($debits)) {
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

    private function storeDB($validatedData, $vehicles, $rowQtys, $totalQtys)
    {
        // Store Delivery Boxes
        if (! empty($rowQtys)) {
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

    /**
     * Get customer address for AJAX request (for auto-populating shipping address)
     */
    public function getCustomerAddress($customerId)
    {
        try {
            $customer = $this->customerRepository->get($customerId);
            if ($customer && isset($customer['address'])) {
                return response()->json(['address' => $customer['address']]);
            }
            return response()->json(['address' => '']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load customer address'], 500);
        }
    }

    /**
     * Get customer orders for AJAX request (for multi-order delivery modal)
     */
    public function getCustomerOrders($customerId)
    {
        try {
            // Get non-completed/non-delivered orders for the customer
            $orders = $this->orderRepository->getCustomerOrders($customerId);

            return response()->json(['data' => $orders]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load customer orders'], 500);
        }
    }
}
