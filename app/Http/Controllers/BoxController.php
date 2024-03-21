<?php

namespace App\Http\Controllers;

use App\Models\Box;
use Illuminate\Http\Request;
use App\Http\Requests\BoxRequest;
use App\Repositories\BoxRepository;
use App\Repositories\HeadRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ImageRepository;

class BoxController extends Controller
{
    protected $boxRepository;
    protected $headRepository;
    protected $imageRepository;

    public function __construct(
        BoxRepository $boxRepository,
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->boxRepository = $boxRepository;
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
    }

    public function index(){
        $box = $this->boxRepository->all();
        return view('box', [
            'box' => $box,
        ]); 
    }

    public function create(){
        $material = $this->headRepository->get('13');
        return view('addBox', [
            'material' => $material,
        ]);
    }

    public function store(BoxRequest $request){
        $validatedData = $request->validated();
        $getId = $this->boxRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'box', 'boxes', $getId);        
            }
        }
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $key => $file) {
                $title = $request->input('file_title')[$key] ?? null;
                $this->storeFile($file, 'box_file', 'boxes', $getId, $title);        
            }
        }
        return redirect()->route('box.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $box = $this->boxRepository->get($id);
        $image = $this->imageRepository->image('boxes', $id);
        $file = $this->imageRepository->file('boxes', $id);
        return view('boxInfo', [
            'box' => $box,
            'image' => $image,
            'file' => $file,
        ]);
    }
    
    public function edit(Box $id){
        $material = $this->headRepository->get('13');
        return view('editBox', [
            'box' => $id,
            'material' => $material,
        ]);
    }

    public function update(Request $request, $id){
        $getId = $this->boxRepository->update($id, $request->input());

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
        return redirect()->route('box.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(product $product){}
}
