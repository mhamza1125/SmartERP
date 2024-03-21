<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Models\ProductMaterial;
use App\Repositories\BoxRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\ProductTypeRepository;
use App\Http\Requests\ProductMaterialRequest;
use App\Repositories\ProductMaterialRepository;

class ProductMaterialController extends Controller
{
    protected $boxRepository;
    protected $productRepository;
    protected $materialRepository;
    protected $productTypeRepository;
    protected $productMaterialRepository;

    public function __construct(
        BoxRepository $boxRepository,
        ProductRepository $productRepository,
        MaterialRepository $materialRepository, 
        ProductTypeRepository $productTypeRepository, 
        ProductMaterialRepository $productMaterialRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->boxRepository = $boxRepository;
        $this->productRepository = $productRepository;
        $this->materialRepository = $materialRepository;
        $this->productTypeRepository = $productTypeRepository;

        $this->productMaterialRepository = $productMaterialRepository;
    }

    public function index(){
        $productMaterial = $this->productMaterialRepository->all();
        return view('productMaterial', [
            'productMaterial' => $productMaterial,
        ]); 
    }

    public function create(){
        $product = $this->productRepository->material();
        $material = $this->materialRepository->all();
        $box = $this->boxRepository->active();
        return view('addProductMaterial', [
            'box' => $box,
            'product' => $product,
            'material' => $material,
        ]);
    }

    public function store(ProductMaterialRequest $request){
        $validatedData = $request->validated();
        dd($validatedData);
        if (!$request->has('quantity')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        $getId = $request->input('product_type_id');
        $products = $request->input('material_id');
        $quantities = $request->input('quantity');
        $this->storePM($getId, $products, $quantities);

        return redirect()->route('productMaterial.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $productType = $this->productTypeRepository->get($id);
        $productMaterial = $this->productMaterialRepository->get($id);
        return view('productMaterialInfo', [
            'productType' => $productType,
            'productMaterial' => $productMaterial,
        ]);
    }
    
    public function edit($id){
        $productType = $this->productTypeRepository->get($id);
        $material = $this->materialRepository->all();
        $productMaterial = $this->productMaterialRepository->get($id);
        return view('editProductMaterial', [
            'productType' => $productType,
            'material' => $material,
            'productMaterial' => $productMaterial,
        ]);
    }

    public function update(Request $request, $id){
        if (!$request->has('quantity')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $products = $request->input('material_id');
        $quantities = $request->input('quantity');
        $this->productMaterialRepository->delete($id);
        $this->storePM($id, $products, $quantities);

        return redirect()->route('productMaterial.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Purchase $purchase){}

    private function storePM($getId, $products, $quantities){
        foreach ($quantities as $key => $quantity) {
            $product = $products[$key] ?? null;
            $productMaterial = [
                'product_type_id' => $getId,
                'material_id' => $product,
                'quantity' => $quantity,
            ];
            $this->productMaterialRepository->store($productMaterial);
        }
    }
}
