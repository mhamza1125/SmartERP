<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\ProductTypeRepository;
use App\Http\Requests\ProductMaterialRequest;
use App\Repositories\ProductMaterialRepository;

class ProductMaterialController extends Controller
{
    protected $productRepository;

    protected $materialRepository;

    protected $productTypeRepository;

    protected $productMaterialRepository;

    public function __construct(
        ProductRepository $productRepository,
        MaterialRepository $materialRepository,
        ProductTypeRepository $productTypeRepository,
        ProductMaterialRepository $productMaterialRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->productRepository = $productRepository;
        $this->materialRepository = $materialRepository;
        $this->productTypeRepository = $productTypeRepository;
        $this->productMaterialRepository = $productMaterialRepository;
    }

    public function index()
    {
        $this->authorize('access', Product::class);
        $product = $this->productRepository->active();
        $productMaterial = $this->productMaterialRepository->all();

        return view('productMaterial', [
            'product' => $product,
            'productMaterial' => $productMaterial,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);
    }

    public function create2($id)
    {
        $this->authorize('create', Product::class);
        $product = $this->productRepository->get($id);
        $productType = $this->productRepository->material($id);
        $material = $this->materialRepository->getMaterial($product['material_id']);
        $mbox = $this->materialRepository->getBox();

        return view('addProductMaterial', [
            'product' => $product,
            'mbox' => $mbox,
            'productType' => $productType,
            'material' => $material,
        ]);
    }

    public function store(ProductMaterialRequest $request)
    {
        $validatedData = $request->validated();
        if (! $request->has('quantity')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        $getId = $request->input('product_type_id');
        $products = $request->input('material_id');
        $quantities = $request->input('quantity');
        $this->storePM($getId, $products, $quantities);

        return redirect()->route('productMaterial.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Product::class);
        $productType = $this->productTypeRepository->get($id);
        $productMaterial = $this->productMaterialRepository->get($id);

        return view('productMaterialInfo', [
            'productType' => $productType,
            'productMaterial' => $productMaterial,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('edit', Product::class);
        $productType = $this->productTypeRepository->get($id);
        $material = $this->materialRepository->getMaterial($productType['material_id']);
        $productMaterial = $this->productMaterialRepository->get($id);
        $mbox = $this->materialRepository->getBox();

        return view('editProductMaterial', [
            'mbox' => $mbox,
            'material' => $material,
            'productType' => $productType,
            'productMaterial' => $productMaterial,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (! $request->has('quantity')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->productMaterialRepository->update($id, $request->input());

        return redirect()->route('productMaterial.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(Purchase $purchase)
    {
        $this->authorize('delete', Product::class);
    }

    private function storePM($getId, $products, $quantities)
    {
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
