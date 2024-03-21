<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Http\Requests\ProductRequest;
use App\Repositories\ImageRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductTypeRepository;

class ProductController extends Controller
{
    protected $headRepository;
    protected $imageRepository;
    protected $productRepository;
    protected $categoryRepository;
    protected $productTypeRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        ProductRepository $productRepository, 
        CategoryRepository $categoryRepository, 
        ProductTypeRepository $productTypeRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productTypeRepository = $productTypeRepository;
    }

    public function index(){
        $product = $this->productRepository->all();
        return view('product', [
            'product' => $product,
        ]); 
    }

    public function create(){
        $category = $this->categoryRepository->all();
        $size = $this->headRepository->get('1');
        $unit = $this->headRepository->get('4');
        return view('addproduct', [
            'category' => $category,
            'size' => $size,
            'unit' => $unit,
        ]);
    }

    public function store(ProductRequest $request){
        $validatedData = $request->validated();
        $getId = $this->productRepository->store($validatedData);
        foreach($request->input('size_id') as $size_id){
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
        return redirect()->route('product.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $product = $this->productRepository->get($id);
        $size = $this->productTypeRepository->active($id);
        $image = $this->imageRepository->image('products', $id);
        $file = $this->imageRepository->file('products', $id);
        return view('productInfo', [
            'product' => $product,
            'size' => $size,
            'image' => $image,
            'file' => $file,
        ]);
    }
    
    public function edit(Product $id){
        $category = $this->categoryRepository->all();
        $productType = $this->productTypeRepository->active($id->product_id);
        $size = $this->headRepository->get('1');
        $unit = $this->headRepository->get('4');
        return view('editproduct', [
            'size' => $size,
            'unit' => $unit,
            'product' => $id,
            'category' => $category,
            'productType' => $productType,
        ]);
    }

    public function update(Request $request, $id){
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
    
    public function destroy(product $product){}
}
