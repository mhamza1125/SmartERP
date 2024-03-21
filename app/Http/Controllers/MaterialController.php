<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\MaterialRequest;
use App\Repositories\MaterialRepository;

class MaterialController extends Controller
{
    protected $materialRepository;
    protected $headRepository;
    protected $imageRepository;

    public function __construct(
        MaterialRepository $materialRepository, 
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->materialRepository = $materialRepository;
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
    }

    public function index(){
        $material = $this->materialRepository->all();
        return view('material', [
            'material' => $material,
        ]); 
    }

    public function create(){
        $material = $this->headRepository->get('10');
        $unit = $this->headRepository->get('4');
        return view('addmaterial', [
            'material' => $material,
            'unit' => $unit,
        ]);
    }

    public function store(MaterialRequest $request){
        $validatedData = $request->validated();
        $getId = $this->materialRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'material', 'materials', $getId);        
            }
        }
        return redirect()->route('material.add')->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $material = $this->materialRepository->get($id);
        $image = $this->imageRepository->image('materials', $id);
        return view('materialInfo', [
            'material' => $material,
            'image' => $image,
        ]);
    }
    
    public function edit(Material $id){
        $material = $this->headRepository->get('10');
        $unit = $this->headRepository->get('4');
        return view('editmaterial', [
            'material' => $id,
            'materialType' => $material,
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, $id){
        $getId = $this->materialRepository->update($id, $request->input());      
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'material', 'materials', $getId);        
            }
        }
        return redirect()->route('material.show', $id)->with('success', 'Record Updated Successfully');    
    }
    
    public function destroy(Material $material){}
}
