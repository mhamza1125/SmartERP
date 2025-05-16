<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Repositories\CustomerRepository;
use App\Repositories\HeadRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\StockItemRepository;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $headRepository;

    protected $orderRepository;

    protected $productRepository;

    protected $customerRepository;

    protected $orderItemRepository;

    protected $stockItemRepository;

    protected $purchaseItemRepository;

    public function __construct(
        HeadRepository $headRepository,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CustomerRepository $customerRepository,
        OrderItemRepository $orderItemRepository,
        StockItemRepository $stockItemRepository,
        PurchaseItemRepository $purchaseItemRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
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
        $products = $request->input('product_type_id');
        $stages = $request->input('product_stage_id');
        $prices = $request->input('price');
        $prices2 = $request->input('price2');
        $heads = $request->input('head_id');
        $exchanges = $request->input('exchange');
        $quantities = $request->input('quantity');
        $getId = $this->orderRepository->store($validatedData);
        $this->storeOI($getId, $products, $stages, $prices, $prices2, $quantities, $heads, $exchanges);

        return redirect()->route('order.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Order::class);
        $order = $this->orderRepository->get($id);
        $orderItem = $this->orderItemRepository->get($id);

        return view('orderInfo', [
            'order' => $order,
            'orderItem' => $orderItem,
        ]);
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

        return view('orderStatus', [
            'order' => $order,
            'stock' => $stock,
        ]);
    }

    public function edit(Order $id)
    {
        $this->authorize('edit', Order::class);
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
        $this->orderRepository->update($id, $request->input());
        $this->orderItemRepository->update($id, $request->input());

        return redirect()->route('order.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function updateStatus($id, $status)
    {
        $orderStatus = ['order_status' => $status];
        $this->orderRepository->update($id, $orderStatus);

        return redirect()->route('order')->with('success', 'Status Updated Successfully');
    }

    public function destroy(Purchase $purchase)
    {
    }

    private function storeOI($getId, $products, $stages, $prices, $prices2, $quantities, $heads, $exchanges)
    {
        foreach ($prices as $key => $price) {
            $product = $products[$key] ?? null;
            $stage = $stages[$key] ?? null;
            $quantity = $quantities[$key] ?? null;
            $price2 = $prices2[$key] ?? null;
            $head_id = $heads[$key] ?? null;
            $exchange = $exchanges[$key] ?? null;
            $total = $price * $quantity;
            $orderItem = [
                'order_id' => $getId,
                'product_type_id' => $product,
                'product_stage_id' => $stage,
                'price' => $price,
                'price2' => $price2,
                'head_id' => $head_id,
                'exchange' => $exchange,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $this->orderItemRepository->store($orderItem);
        }
    }
}
