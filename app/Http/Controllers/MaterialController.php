<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\MaterialRequest;
use App\Repositories\VendorRepository;
use App\Repositories\MaterialRepository;

class MaterialController extends Controller
{
    protected $materialRepository;
    protected $headRepository;
    protected $vendorRepository;
    protected $imageRepository;

    public function __construct(
        MaterialRepository $materialRepository, 
        HeadRepository $headRepository,
        VendorRepository $vendorRepository,
        ImageRepository $imageRepository,
    ){
        $this->middleware(['auth', 'all']);
        $this->materialRepository = $materialRepository;
        $this->headRepository = $headRepository;
        $this->vendorRepository = $vendorRepository;
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
        $vendor = $this->vendorRepository->all();
        $refNo = $this->materialRepository->refNo();
        return view('addmaterial', [
            'material' => $material,
            'vendor' => $vendor,
            'refNo' => $refNo,
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
        return redirect()->route('material.show', $getId)->with('success', 'Record Inserted Successfully');    
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
        $vendor = $this->vendorRepository->all();
        return view('editMaterial', [
            'material' => $id,
            'materialType' => $material,
            'unit' => $unit,
            'vendor' => $vendor,
        ]);
    }

    public function detail(Request $request){
        // Materail Ledger
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $mid = $request->input('material_id');
        $material = $this->materialRepository->all();
        if(!empty($dfrom) && !empty($dto)){
            $materialItem = $this->materialRepository->ledgerFilter($dfrom, $dto, $mid);
        }else{
            $materialItem = $this->materialRepository->ledger();
        }
        // dd($materialItem);
        return view('materialDetail', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'mid' => $mid,
            'material' => $material,
            'materialItem' => $materialItem,
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
