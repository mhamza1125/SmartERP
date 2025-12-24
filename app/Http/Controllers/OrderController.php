<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Repositories\BankRepository;
use App\Repositories\HeadRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $headRepository;

    protected $orderRepository;

    protected $bankRepository;

    protected $productRepository;

    protected $customerRepository;

    protected $orderItemRepository;

    protected $stockItemRepository;

    protected $purchaseItemRepository;

    protected $companyRepository;

    public function __construct(
        HeadRepository $headRepository,
        BankRepository $bankRepository,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CustomerRepository $customerRepository,
        OrderItemRepository $orderItemRepository,
        StockItemRepository $stockItemRepository,
        PurchaseItemRepository $purchaseItemRepository,
        CompanyRepository $companyRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->bankRepository = $bankRepository;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->companyRepository = $companyRepository;
    }

    public function index()
    {
        $this->authorize('access', Order::class);
        $order = $this->orderRepository->all();

        return view('order', [
            'order' => $order,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Order::class);
        $product = $this->productRepository->activeTypes();
        $customer = $this->customerRepository->all();
        $head = $this->headRepository->get('16');

        return view('addOrder', [
            'head' => $head,
            'product' => $product,
            'customer' => $customer,
        ]);
    }

    public function store(OrderRequest $request)
    {
        $validatedData = $request->validated();
        if (! $request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        // Generate dynamic job number
        $validatedData['job_no'] = $this->generateJobNumber($validatedData['customer_id']);

        $products = $request->input('product_type_id');
        $stages = $request->input('product_stage_id');
        $prices = $request->input('price');
        $prices2 = $request->input('price2');
        $quantities = $request->input('quantity');
        $getId = $this->orderRepository->store($validatedData);
        $this->storeOI($getId, $products, $stages, $prices, $prices2, $quantities);

        return redirect()->route('order.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    private function generateJobNumber($customerId)
    {
        // Get customer number
        $customer = DB::table('customers')->where('customer_id', $customerId)->first();
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        $customerNumber = $customer->customer_no; // e.g., "CU0001"
        $currentYear = date('y'); // e.g., "25" for 2025

        // Count existing orders for this customer in current year
        $orderCount = DB::table('orders')
            ->where('customer_id', $customerId)
            ->whereYear('created_at', date('Y'))
            ->count();

        $nextCount = str_pad($orderCount + 1, 3, '0', STR_PAD_LEFT); // e.g., "001"

        return $customerNumber . '/J' . $currentYear . '-' . $nextCount;
    }

    public function show($id)
    {
        $this->authorize('show', Order::class);
        $order = $this->orderRepository->get($id);
        $orderItem = $this->orderItemRepository->get($id);
        $banks = $this->bankRepository->self();
        $packingList = $this->getPackingList($id);

        // Get all PTCs (Production Tracking Cards) for this order
        $ptcs = DB::table('stocks')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->where('stocks.order_id', $id)
            ->where('stocks.is_ptc_master', 1)
            ->select('stocks.*', 'orders.order_status')
            ->orderBy('stocks.stock_no')
            ->get();

        return view('orderInfo', [
            'order' => $order,
            'orderItem' => $orderItem,
            'banks' => $banks,
            'packingList' => $packingList,
            'ptcs' => $ptcs,
        ]);
    }

    /**
     * Print order information
     */
    public function printOrder($id)
    {
        $this->authorize('show', Order::class);
        $order = $this->orderRepository->get($id);
        $orderItem = $this->orderItemRepository->get($id);
        $banks = $this->bankRepository->self();
        $packingList = $this->getPackingList($id);

        return view('print.order', [
            'order' => $order,
            'orderItem' => $orderItem,
            'banks' => $banks,
            'packingList' => $packingList,
        ]);
    }

    /**
     * Print production order
     */
    public function printProduction($id)
    {
        $this->authorize('show', Order::class);
        $order = $this->orderRepository->get($id);
        $orderItem = $this->orderItemRepository->get($id);

        // Fetch stock data for each product using the same logic as the /stock page
        $stockData = [];

        // Get unique product_type_ids from order items
        $productTypeIds = $orderItem->pluck('product_type_id')->unique();

        foreach ($productTypeIds as $productTypeId) {
            // Get stock data for this product type using the repository method
            // This uses the same calculation logic as the /stock page
            $allStockForProduct = $this->stockItemRepository->pStockByProductType($productTypeId);

            // Store stock by stage_id for easy lookup
            $stageStockMap = [];
            foreach ($allStockForProduct as $stock) {
                $stageStockMap[$stock->stage_id] = [
                    'stockIn' => $stock->stockIn ?? 0,
                    'stockOut' => $stock->stockOut ?? 0,
                ];
            }

            // For each order item with this product type, calculate finished and unfinished stock
            $itemsWithThisProduct = $orderItem->where('product_type_id', $productTypeId);
            foreach ($itemsWithThisProduct as $item) {
                $orderedStageId = $item->product_stage_id;

                // Finished Stock: Stock at the ordered stage only
                $finishedStockData = $stageStockMap[$orderedStageId] ?? ['stockIn' => 0, 'stockOut' => 0];
                $finishedStockQty = $finishedStockData['stockIn'] - $finishedStockData['stockOut'];

                // Unfinished Stock: Sum of stock at all OTHER stages (excluding the ordered stage)
                $unfinishedStockQty = 0;
                foreach ($stageStockMap as $stageId => $stockData) {
                    if ($stageId != $orderedStageId) {
                        $unfinishedStockQty += $stockData['stockIn'] - $stockData['stockOut'];
                    }
                }

                $key = $item->product_type_id . '_' . $item->product_stage_id;
                $stockData[$key] = [
                    'finished_stock' => max(0, $finishedStockQty), // Ensure non-negative
                    'unfinished_stock' => max(0, $unfinishedStockQty), // Ensure non-negative
                ];
            }
        }

        return view('print.order-production', [
            'order' => $order,
            'orderItem' => $orderItem,
            'stockData' => $stockData,
        ]);
    }

    /**
     * Print proforma invoice
     */
    public function printProforma($id)
    {
        $this->authorize('show', Order::class);
        $order = $this->orderRepository->get($id);
        $orderItem = $this->orderItemRepository->get($id);
        $company = $this->companyRepository->first();

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

        // Get optional HS Code parameter
        $hsCode = request()->input('hs_code');

        return view('print.order-proforma', [
            'order' => $order,
            'orderItem' => $orderItem,
            'bankDetails' => $bankDetails,
            'company' => $company,
            'hsCode' => $hsCode,
        ]);
    }

    private function getPackingList($orderId)
    {
        // Get order items with box quantity factors to calculate theoretical packing list
        $orderItems = DB::table('order_items')
            ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'order_items.product_stage_id')
            ->join('product_materials', function($join) {
                $join->on('product_materials.product_type_id', '=', 'order_items.product_type_id')
                     ->whereIn('product_materials.material_id', function($query) {
                         // Get packing box material IDs (material_type_id = 61)
                         $query->select('material_id')
                               ->from('materials')
                               ->where('material_type_id', 61);
                     });
            })
            ->where('order_items.order_id', $orderId)
            ->select(
                'products.name as product_name',
                'products.article_no',
                'heads.name as size_name',
                'shead.name as stage_name',
                'order_items.quantity as order_quantity',
                'product_materials.quantity as bqty'
            )
            ->get();

        // Calculate both product quantities and box quantities
        $packingData = [];
        $totalQuantity = 0;
        $totalBoxes = 0;

        foreach ($orderItems as $item) {
            $productKey = $item->article_no . ' - ' . $item->product_name . ' (Size: ' . $item->size_name . ', Stage: ' . $item->stage_name . ')';

            $boxQuantity = $item->order_quantity * $item->bqty;
            // Use ceil to get whole boxes needed (round up for partial boxes)
            $boxQuantity = ceil($boxQuantity);

            if (!isset($packingData[$productKey])) {
                $packingData[$productKey] = [
                    'quantity' => 0,
                    'boxes' => 0
                ];
            }

            $packingData[$productKey]['quantity'] += $item->order_quantity;
            $packingData[$productKey]['boxes'] += $boxQuantity;
            $totalQuantity += $item->order_quantity;
            $totalBoxes += $boxQuantity;
        }

        return [
            'items' => $packingData,
            'totalQuantity' => $totalQuantity,
            'totalBoxes' => $totalBoxes
        ];
    }

    public function estimate($id)
    {
        $this->authorize('show', Order::class);
        $order = $this->orderRepository->get($id);
        $stock = $this->stockItemRepository->stock();
        $freeStock = $this->stockItemRepository->freeStock();
        $estimate = $this->orderItemRepository->estimate($id);
        $stockArray = $stock->keyBy('material_id')->toArray();
        $purchase = $this->purchaseItemRepository->estimate($id);
        $purchaseArray = $purchase->keyBy('material_id')->toArray();

        return view('orderEstimate', [
            'order' => $order,
            'stock' => $stockArray,
            'estimate' => $estimate,
            'purchase' => $purchaseArray,
        ]);
    }

    public function status($id)
    {
        $this->authorize('show', Order::class);
        $order = $this->orderRepository->get($id);
        $stock = $this->stockItemRepository->orderStatus($id);
        $remainingItems = $this->stockItemRepository->orderRemainingItems($id);

        return view('orderStatus', [
            'order' => $order,
            'stock' => $stock,
            'remainingItems' => $remainingItems,
        ]);
    }

    public function edit(Order $id)
    {
        $this->authorize('edit', Order::class);

        // Prevent editing Dispatched (3), Delivered (4), or Cancelled (5) orders
        if (in_array($id->order_status, [3, 4, 5])) {
            return redirect()->route('order.show', $id->order_id)
                ->with('fails', 'Cannot edit orders with status: Dispatched, Delivered, or Cancelled');
        }

        $head = $this->headRepository->get('16');
        $customer = $this->customerRepository->all();
        $product = $this->productRepository->activeTypes();
        $orderItem = $this->orderItemRepository->get($id->order_id);

        return view('editOrder', [
            'order' => $id,
            'head' => $head,
            'customer' => $customer,
            'product' => $product,
            'orderItem' => $orderItem,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (! $request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        // Get current order to check if customer changed
        $currentOrder = Order::findOrFail($id);

        // Prevent updating Dispatched (3), Delivered (4), or Cancelled (5) orders
        if (in_array($currentOrder->order_status, [3, 4, 5])) {
            return redirect()->route('order.show', $id)
                ->with('fails', 'Cannot update orders with status: Dispatched, Delivered, or Cancelled');
        }

        $requestData = $request->input();

        // If customer changed, regenerate job number
        if ($currentOrder->customer_id != $requestData['customer_id']) {
            $requestData['job_no'] = $this->generateJobNumber($requestData['customer_id']);
        }

        $this->orderRepository->update($id, $requestData);
        $this->orderItemRepository->update($id, $request->input());

        return redirect()->route('order.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function generateJobNumberAjax(Request $request)
    {
        try {
            $customerId = $request->input('customer_id');
            $jobNumber = $this->generateJobNumber($customerId);

            return response()->json(['job_no' => $jobNumber]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus($id, $status)
    {
        // Get current order
        $currentOrder = Order::findOrFail($id);

        // Prevent status changes for Dispatched (3), Delivered (4), or Cancelled (5) orders
        if (in_array($currentOrder->order_status, [3, 4, 5])) {
            return redirect()->route('order')
                ->with('fails', 'Cannot change status of Dispatched, Delivered, or Cancelled orders');
        }

        $orderStatus = ['order_status' => $status];
        $this->orderRepository->update($id, $orderStatus);

        return redirect()->route('order')->with('success', 'Status Updated Successfully');
    }

    public function destroy(Purchase $purchase)
    {
    }

    private function storeOI($getId, $products, $stages, $prices, $prices2, $quantities)
    {
        foreach ($prices as $key => $price) {
            $product = $products[$key] ?? null;
            $stage = $stages[$key] ?? null;
            $quantity = $quantities[$key] ?? null;
            $price2 = $prices2[$key] ?? null;
            $total = $price * $quantity;
            $orderItem = [
                'order_id' => $getId,
                'product_type_id' => $product,
                'product_stage_id' => $stage,
                'price' => $price,
                'price2' => $price2,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $this->orderItemRepository->store($orderItem);
        }
    }
}
