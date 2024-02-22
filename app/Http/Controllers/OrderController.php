<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ImageRepository;
use App\Repositories\OrderRepository;
use App\Http\Requests\OrderRequest;
use App\Repositories\ProductRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\ProductTypeRepository;

class OrderController extends Controller
{
    protected $imageRepository;
    protected $orderRepository;
    protected $productRepository;
    protected $customerRepository;
    protected $orderItemRepository;
    protected $productTypeRepository;

    public function __construct(
        ImageRepository $imageRepository,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CustomerRepository $customerRepository,
        OrderItemRepository $orderItemRepository, 
        ProductTypeRepository $productTypeRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->imageRepository = $imageRepository;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productTypeRepository = $productTypeRepository;
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
        if (!$request->has('quantity')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        $products = $request->input('product_type_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');
        
        $getId = $this->orderRepository->store($validatedData);

        $this->storePI($getId, $products, $prices, $quantities);

        return redirect()->route('order.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $order = $this->orderRepository->get($id);
        return view('orderInfo', [
            'order' => $order,
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
        $this->storePI($id, $products, $prices, $quantities);
        return redirect()->route('order.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Purchase $purchase){}

    private function storePI($getId, $products, $prices, $quantities)
    {
        foreach ($prices as $key => $price) {
            $product = $products[$key] ?? null;
            $quantity = $quantities[$key] ?? null;
            $total = $price * $quantity;
            $purchaseItem = [
                'order_id' => $getId,
                'product_type_id' => $product,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $this->orderItemRepository->store($purchaseItem);
        }
    }
}
