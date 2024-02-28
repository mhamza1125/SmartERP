<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use App\Http\Requests\OrderRequest;
use App\Repositories\ProductRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderItemRepository;

class OrderController extends Controller
{
    protected $orderRepository;
    protected $productRepository;
    protected $customerRepository;
    protected $orderItemRepository;

    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CustomerRepository $customerRepository,
        OrderItemRepository $orderItemRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function index(){
        $order = $this->orderRepository->all();
        return view('order', [
            'order' => $order,
        ]); 
    }

    public function create(){
        $product = $this->productRepository->active();
        $customer = $this->customerRepository->all();
        return view('addOrder', [
            'product' => $product,
            'customer' => $customer,
        ]);
    }

    public function store(OrderRequest $request){
        $validatedData = $request->validated();
        if (!$request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $products = $request->input('product_type_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');
        $getId = $this->orderRepository->store($validatedData);
        $this->storeOI($getId, $products, $prices, $quantities);

        return redirect()->route('order.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $order = $this->orderRepository->get($id);
        $orderItem = $this->orderItemRepository->get($id);
        return view('orderInfo', [
            'order' => $order,
            'orderItem' => $orderItem,
        ]);
    }
    
    public function edit(Order $id){
        $customer = $this->customerRepository->all();
        $product = $this->productRepository->active();
        $orderItem = $this->orderItemRepository->get($id->order_id);
        return view('editOrder', [
            'order' => $id,
            'customer' => $customer,
            'product' => $product,
            'orderItem' => $orderItem,
        ]);
    }

    public function update(Request $request, $id){
        if (!$request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $products = $request->input('product_type_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');
        $this->orderItemRepository->delete($id);
        $this->orderRepository->update($id, $request->input());
        $this->storeOI($id, $products, $prices, $quantities);
        return redirect()->route('order.show', $id)->with('success', 'Record Updated Successfully');    
    }

    public function updateStatus($id, $status){
        $orderStatus = ['order_status' => $status];        
        $this->orderRepository->update($id, $orderStatus);
        return redirect()->route('order')->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Purchase $purchase){}

    private function storeOI($getId, $products, $prices, $quantities){
        foreach ($prices as $key => $price) {
            $product = $products[$key] ?? null;
            $quantity = $quantities[$key] ?? null;
            $total = $price * $quantity;
            $orderItem = [
                'order_id' => $getId,
                'product_type_id' => $product,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $this->orderItemRepository->store($orderItem);
        }
    }
}
