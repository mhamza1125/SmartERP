<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Models\ProductMaterial;
use App\Repositories\BoxRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ProductRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\ProductBoxRepository;
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
    protected $productBoxRepository;

    public function __construct(
        BoxRepository $boxRepository,
        ProductRepository $productRepository,
        MaterialRepository $materialRepository, 
        ProductTypeRepository $productTypeRepository, 
        ProductMaterialRepository $productMaterialRepository,
        ProductBoxRepository $productBoxRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->boxRepository = $boxRepository;
        $this->productRepository = $productRepository;
        $this->materialRepository = $materialRepository;
        $this->productTypeRepository = $productTypeRepository;
        $this->productMaterialRepository = $productMaterialRepository;
        $this->productBoxRepository = $productBoxRepository;
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
        if (!$request->has('quantity')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        
        $getId = $request->input('product_type_id');
        $products = $request->input('material_id');
        $quantities = $request->input('quantity');
        $pbox = ['product_type_id' => $getId, 'box_id' => $request->input('box_id'), 'quantity' => $request->input('bqty')];
        $this->productBoxRepository->store($pbox);
        $this->storePM($getId, $products, $quantities);

        return redirect()->route('productMaterial.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $productType = $this->productTypeRepository->get($id);
        $productMaterial = $this->productMaterialRepository->get($id);
        $productBox = $this->productBoxRepository->get($id);
        return view('productMaterialInfo', [
            'productType' => $productType,
            'productMaterial' => $productMaterial,
            'productBox' => $productBox,
        ]);
    }
    
    public function edit($id){
        $material = $this->materialRepository->all();
        $productType = $this->productTypeRepository->get($id);
        $productMaterial = $this->productMaterialRepository->get($id);
        $box = $this->boxRepository->active();
        $pbox = $this->productBoxRepository->get($id);
        return view('editProductMaterial', [
            'box' => $box,
            'pbox' => $pbox,
            'material' => $material,
            'productType' => $productType,
            'productMaterial' => $productMaterial,
        ]);
    }

    public function update(Request $request, $id){
        if (!$request->has('quantity')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $pbox = ['product_type_id' => $id, 'box_id' => $request->input('box_id'), 'quantity' => $request->input('bqty')];
        $this->productBoxRepository->update($id, $pbox);
        $this->productMaterialRepository->update($id, $request->input());
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
