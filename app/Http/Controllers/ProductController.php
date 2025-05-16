<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\ProductCostRepository;
use App\Repositories\ProductMaterialRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductTypeRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $headRepository;

    protected $imageRepository;

    protected $productRepository;

    protected $categoryRepository;

    protected $materialRepository;

    protected $productTypeRepository;

    protected $productCostRepository;

    protected $productMaterialRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        MaterialRepository $materialRepository,
        ProductTypeRepository $productTypeRepository,
        ProductCostRepository $productCostRepository,
        ProductMaterialRepository $productMaterialRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->materialRepository = $materialRepository;
        $this->productTypeRepository = $productTypeRepository;
        $this->productCostRepository = $productCostRepository;
        $this->productMaterialRepository = $productMaterialRepository;
    }

    public function index()
    {
        $this->authorize('access', Product::class);
        $product = $this->productRepository->all();

        return view('product', [
            'product' => $product,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);
        $category = $this->categoryRepository->all();
        $size = $this->headRepository->get('1');
        $unit = $this->headRepository->get('4');
        $stage = $this->headRepository->get('12');
        $material = $this->materialRepository->all();

        return view('addProduct', [
            'category' => $category,
            'material' => $material,
            'stage' => $stage,
            'size' => $size,
            'unit' => $unit,
        ]);
    }

    public function store(ProductRequest $request)
    {
        $validatedData = $request->validated();
        $materialIds = $request->input('material_id');
        $validatedData['material_id'] = $materialIds ? implode('|', $materialIds) : '0';
        $stageIds = $request->input('stage_ids');
        $validatedData['stage_ids'] = $stageIds ? implode('|', $stageIds) : '0';
        $getId = $this->productRepository->store($validatedData);
        foreach ($request->input('size_id') as $size_id) {
            $productType = ['product_id' => $getId, 'size_id' => $size_id];
            $this->productTypeRepository->store($productType);
        }
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'product', 'products', $getId);
            }
        }
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $key => $file) {
                $title = $request->input('file_title')[$key] ?? null;
                $this->storeFile($file, 'product_file', 'products', $getId, $title);
            }
        }

        return redirect()->route('product.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Product::class);
        $product = $this->productRepository->get($id);
        $size = $this->productTypeRepository->active($id);
        $image = $this->imageRepository->image('products', $id);
        $file = $this->imageRepository->file('products', $id);
        $pcost = $this->productCostRepository->get($id);
        $pWages = $pcost->where('table_name', 'general')->sum('amount');
        $totalMaterial = $this->productMaterialRepository->times($id);
        $getMaterial = $this->productMaterialRepository->getAll($id);
        $material = $this->materialRepository->getMaterial($product['material_id']);
        $stage = $this->headRepository->getStage($product['stage_ids']);

        return view('productInfo', [
            'product' => $product,
            'size' => $size,
            'image' => $image,
            'file' => $file,
            'pcost' => $pcost,
            'pWages' => $pWages,
            'totalMaterial' => $totalMaterial,
            'countMaterial' => $totalMaterial->count(),
            'getMaterial' => $getMaterial,
            'material' => $material,
            'stage' => $stage,
        ]);
    }

    public function edit(Product $id)
    {
        $this->authorize('edit', Product::class);
        $category = $this->categoryRepository->all();
        $productType = $this->productTypeRepository->active($id->product_id);
        $size = $this->headRepository->get('1');
        $unit = $this->headRepository->get('4');
        $stage = $this->headRepository->get('12');
        $pstage = $this->headRepository->getStage($id['stage_ids']);
        $material = $this->materialRepository->all();
        $pmaterial = $this->materialRepository->getMaterial($id['material_id']);

        return view('editProduct', [
            'size' => $size,
            'unit' => $unit,
            'stage' => $stage,
            'pstage' => $pstage,
            'product' => $id,
            'category' => $category,
            'material' => $material,
            'pmaterial' => $pmaterial,
            'productType' => $productType,
        ]);
    }

    public function update(Request $request, $id)
    {
        $materialIds = $request->input('material_id');
        $this->productMaterialRepository->updateMaterial($id, $materialIds);
        $materialIds = $materialIds ? implode('|', $materialIds) : '0';
        $stageIds = $request->input('stage_ids');
        $stageIds = $stageIds ? implode('|', $stageIds) : '0';
        $request->merge(['material_id' => $materialIds, 'stage_ids' => $stageIds]);
        $getId = $this->productRepository->update($id, $request->input());
        $sizes = $request->input('size_id');
        $this->productTypeRepository->update($getId, $sizes);

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'product', 'products', $getId);
            }
        }
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $key => $file) {
                $title = $request->input('file_title')[$key] ?? null;
                $this->storeFile($file, 'product_file', 'products', $getId, $title);
            }
        }

        return redirect()->route('product.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(product $product)
    {
        $this->authorize('delete', Product::class);
    }
}
