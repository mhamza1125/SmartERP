<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\HeadRepository;
use App\Http\Requests\ProductRequest;
use App\Repositories\ImageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\ProductCostRepository;
use App\Repositories\ProductTypeRepository;
use App\Repositories\ProductMaterialRepository;

class ProductController extends Controller
{
    protected $headRepository;

    protected $imageRepository;

    protected $productRepository;

    protected $categoryRepository;

    protected $materialRepository;

    protected $stockItemRepository;

    protected $productTypeRepository;

    protected $productCostRepository;

    protected $productMaterialRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        ProductRepository $productRepository,
        StockItemRepository $stockItemRepository,
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
        $this->stockItemRepository = $stockItemRepository;
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

        // Store product types (sizes)
        $productTypeIds = [];
        foreach ($request->input('size_id') as $size_id) {
            $productType = ['product_id' => $getId, 'size_id' => $size_id];
            $productTypeId = $this->productTypeRepository->store($productType);
            $productTypeIds[] = $productTypeId;
        }

        // Create size and stage-specific opening stock entries
        $openingStockSizeIds = $request->input('opening_stock_size_id', []);
        $openingStockStageIds = $request->input('opening_stock_stage_id', []);
        $openingStockQuantities = $request->input('opening_stock_quantity', []);

        if (!empty($openingStockSizeIds) && !empty($openingStockStageIds) && !empty($openingStockQuantities)) {
            // Create a mapping of size_id to product_type_id
            $sizeToProductTypeMap = [];
            foreach ($productTypeIds as $productTypeId) {
                $productType = DB::table('product_types')->where('product_type_id', $productTypeId)->first();
                if ($productType) {
                    $sizeToProductTypeMap[$productType->size_id] = $productTypeId;
                }
            }

            foreach ($openingStockSizeIds as $index => $sizeId) {
                $stageId = $openingStockStageIds[$index] ?? null;
                $quantity = $openingStockQuantities[$index] ?? 0;
                $productTypeId = $sizeToProductTypeMap[$sizeId] ?? null;

                if ($sizeId && $stageId && $quantity > 0 && $productTypeId) {
                    $stockItem = [
                        'stock_id' => '1', // Opening stock ID
                        'product_type_id' => $productTypeId,
                        'material_id' => '0',
                        'quantity' => $quantity,
                        'stage_id' => $stageId,
                        'work_logs' => '0',
                        'work_wages' => '0',
                    ];
                    $this->stockItemRepository->store($stockItem);
                }
            }
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
        $openingStock = $this->productRepository->getOpeningStock($id);

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
            'openingStock' => $openingStock,
        ]);
    }

    /**
     * Print product information
     */
    public function printProduct($id)
    {
        $this->authorize('show', Product::class);
        $product = $this->productRepository->get($id);
        $size = $this->productTypeRepository->active($id);
        $pcost = $this->productCostRepository->get($id);
        $pWages = $pcost->where('table_name', 'general')->sum('amount');
        $totalMaterial = $this->productMaterialRepository->times($id);
        $getMaterial = $this->productMaterialRepository->getAll($id);
        $material = $this->materialRepository->getMaterial($product['material_id']);
        $stage = $this->headRepository->getStage($product['stage_ids']);
        $openingStock = $this->productRepository->getOpeningStock($id);

        return view('print.product', [
            'product' => $product,
            'size' => $size,
            'pcost' => $pcost,
            'pWages' => $pWages,
            'totalMaterial' => $totalMaterial,
            'countMaterial' => $totalMaterial->count(),
            'getMaterial' => $getMaterial,
            'material' => $material,
            'stage' => $stage,
            'openingStock' => $openingStock,
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
        $openingStock = $this->productRepository->getOpeningStock($id->product_id);

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
            'openingStock' => $openingStock,
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

        // Update stage-specific opening stock
        $this->updateStageSpecificOpeningStock($getId, $request);

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

    private function updateStageSpecificOpeningStock($productId, $request)
    {
        // Delete existing opening stock entries for this product
        $productTypeIds = $this->productTypeRepository->active($productId)->pluck('product_type_id');

        foreach ($productTypeIds as $productTypeId) {
            $this->stockItemRepository->deleteOpeningStock($productTypeId);
        }

        // Create new size and stage-specific opening stock entries
        $openingStockSizeIds = $request->input('opening_stock_size_id', []);
        $openingStockStageIds = $request->input('opening_stock_stage_id', []);
        $openingStockQuantities = $request->input('opening_stock_quantity', []);

        if (!empty($openingStockSizeIds) && !empty($openingStockStageIds) && !empty($openingStockQuantities)) {
            // Create a mapping of size_id to product_type_id
            $sizeToProductTypeMap = [];
            foreach ($productTypeIds as $productTypeId) {
                $productType = DB::table('product_types')->where('product_type_id', $productTypeId)->first();
                if ($productType) {
                    $sizeToProductTypeMap[$productType->size_id] = $productTypeId;
                }
            }

            foreach ($openingStockSizeIds as $index => $sizeId) {
                $stageId = $openingStockStageIds[$index] ?? null;
                $quantity = $openingStockQuantities[$index] ?? 0;
                $productTypeId = $sizeToProductTypeMap[$sizeId] ?? null;

                if ($sizeId && $stageId && $quantity > 0 && $productTypeId) {
                    $stockItem = [
                        'stock_id' => '1', // Opening stock ID
                        'product_type_id' => $productTypeId,
                        'material_id' => '0',
                        'quantity' => $quantity,
                        'stage_id' => $stageId,
                        'work_logs' => '0',
                        'work_wages' => '0',
                    ];
                    $this->stockItemRepository->store($stockItem);
                }
            }
        }
    }

    public function destroy(product $product)
    {
        $this->authorize('delete', Product::class);
    }
}
