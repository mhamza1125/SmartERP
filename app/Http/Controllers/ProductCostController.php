<?php

namespace App\Http\Controllers;

use App\Models\ProductCost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\VendorRepository;
use App\Repositories\ProductRepository;
use App\Repositories\EmployeeRepository;
use App\Http\Requests\ProductCostRequest;
use App\Repositories\ProductCostRepository;
use App\Repositories\ProductTypeRepository;

class ProductCostController extends Controller
{
    protected $headRepository;
    protected $productRepository;
    protected $employeeRepository;
    protected $vendorRepository;
    protected $productCostRepository;
    protected $productTypeRepository;

    public function __construct(
        HeadRepository $headRepository,
        ProductRepository $productRepository,
        EmployeeRepository $employeeRepository,
        VendorRepository $vendorRepository,
        ProductCostRepository $productCostRepository, 
        ProductTypeRepository $productTypeRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->productRepository = $productRepository;
        $this->employeeRepository = $employeeRepository;
        $this->vendorRepository = $vendorRepository;
        $this->productCostRepository = $productCostRepository;
        $this->productTypeRepository = $productTypeRepository;
    }

    public function index(){
        $productCost = $this->productCostRepository->all();
        return view('productCost', [
            'productCost' => $productCost,
        ]); 
    }

    public function create(){
        $head = $this->headRepository->get('14');
        $product = $this->productRepository->cost();
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->all();
        return view('addProductCost', [
            'head' => $head,
            'product' => $product,
            'employee' => $employee,
            'vendor' => $vendor,
        ]);
    }

    public function store(ProductCostRequest $request){
        $validatedData = $request->validated();
        if (!$request->has('amount')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $products = $request->input('product_type_id');
        $amounts = $request->input('amount');
        $heads = $request->input('head_id');
        $tids = $request->input('table_id');
        $tnames = $request->input('table_name');
        $this->storePC($products, $amounts, $heads, $tnames, $tids);
        return redirect()->route('productCost.show', $products)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $productType = $this->productTypeRepository->get($id);
        $productCost = $this->productCostRepository->get($id);
        return view('productCostInfo', [
            'productType' => $productType,
            'productCost' => $productCost,
        ]);
    }
    
    public function edit($id){
        $head = $this->headRepository->get('14');
        $productType = $this->productTypeRepository->get($id);
        $productCost = $this->productCostRepository->get($id);
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->all();
        return view('editProductCost', [
            'head' => $head,
            'productType' => $productType,
            'productCost' => $productCost,
            'employee' => $employee,
            'vendor' => $vendor,
        ]);
    }

    public function update(Request $request, $id){
        if (!$request->has('amount')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->productCostRepository->update($id, $request->input());
        return redirect()->route('productCost.show', $id)->with('success', 'Record Updated Successfully');
    }
    
    public function destroy(Purchase $purchase){}

    private function storePC($products, $amounts, $heads, $tnames, $tids){
        foreach ($amounts as $key => $amount) {
            $head = $heads[$key] ?? null;
            $table_id = $tids[$key] ?? null;
            $table_name = $tnames[$key] ?? null;
            $productCost = [
                'product_type_id' => $products,
                'amount' => $amount,
                'head_id' => $head,
                'table_id' => $table_id,
                'table_name' => $table_name,
            ];
            $this->productCostRepository->store($productCost);
        }
    }
}
