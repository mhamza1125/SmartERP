<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Material;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\VendorRepository;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    protected $materialRepository;

    protected $headRepository;

    protected $vendorRepository;

    protected $imageRepository;

    protected $stockItemRepository;

    public function __construct(
        MaterialRepository $materialRepository,
        HeadRepository $headRepository,
        VendorRepository $vendorRepository,
        ImageRepository $imageRepository,
        StockItemRepository $stockItemRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->materialRepository = $materialRepository;
        $this->headRepository = $headRepository;
        $this->vendorRepository = $vendorRepository;
        $this->imageRepository = $imageRepository;
        $this->stockItemRepository = $stockItemRepository;
    }

    public function index()
    {
        $this->authorize('access', Material::class);
        $material = $this->materialRepository->all();

        return view('material', [
            'material' => $material,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Material::class);
        $material = $this->headRepository->get('10');
        $unit = $this->headRepository->get('4');
        $vendor = $this->vendorRepository->all();
        $refNo = $this->materialRepository->refNo();

        return view('addMaterial', [
            'material' => $material,
            'vendor' => $vendor,
            'refNo' => $refNo,
            'unit' => $unit,
        ]);
    }

    public function store(MaterialRequest $request)
    {
        $validatedData = $request->validated();
        $getId = $this->materialRepository->store($validatedData);
        $stockItem = [ // Opening Stock
            'stock_id' => '1',
            'product_type_id' => '0',
            'material_id' => $getId,
            'quantity' => $request->input('quantity'),
            'stage_id' => '0',
            'work_logs' => '0',
            'work_wages' => '0',
        ];
        $this->stockItemRepository->store($stockItem);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'material', 'materials', $getId);
            }
        }

        return redirect()->route('material.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Material::class);
        $material = $this->materialRepository->get($id);
        $image = $this->imageRepository->image('materials', $id);

        return view('materialInfo', [
            'material' => $material,
            'image' => $image,
        ]);
    }

    /**
     * Print material information
     */
    public function printMaterial($id)
    {
        $this->authorize('show', Material::class);
        $material = $this->materialRepository->get($id);
        $image = $this->imageRepository->image('materials', $id);

        return view('print.material', [
            'material' => $material,
            'image' => $image,
        ]);
    }

    // public function edit(Material $id){
    public function edit($id)
    {
        $this->authorize('edit', Material::class);
        $id = $this->materialRepository->get($id);
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

    public function detail(Request $request)
    {
        $this->authorize('show', Material::class);
        // Materail Ledger
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $mid = $request->input('material_id');
        $material = $this->materialRepository->all();
        if (! empty($dfrom) && ! empty($dto)) {
            $materialItem = $this->materialRepository->ledgerFilter($dfrom, $dto, $mid);
        } else {
            $materialItem = $this->materialRepository->ledger();
        }

        return view('materialDetail', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'mid' => $mid,
            'material' => $material,
            'materialItem' => $materialItem,
        ]);
    }

    /**
     * Print material detail report
     */
    public function printMaterialDetail(Request $request)
    {
        $this->authorize('show', Material::class);
        // Material Ledger
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $mid = $request->input('material_id');
        $material = $this->materialRepository->all();
        if (! empty($dfrom) && ! empty($dto)) {
            $materialItem = $this->materialRepository->ledgerFilter($dfrom, $dto, $mid);
        } else {
            $materialItem = $this->materialRepository->ledger();
        }

        return view('print.materialDetail', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'mid' => $mid,
            'material' => $material,
            'materialItem' => $materialItem,
        ]);
    }

    public function update(Request $request, $id)
    {
        $getId = $this->materialRepository->update($id, $request->input());
        $stockItem = [ // Opening Stock
            'stock_id' => '1',
            'product_type_id' => '0',
            'material_id' => $id,
            'quantity' => $request->input('quantity'),
            'stage_id' => '0',
            'work_logs' => '0',
            'work_wages' => '0',
        ];
        $this->stockItemRepository->updateStock($id, $stockItem);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'material', 'materials', $getId);
            }
        }

        return redirect()->route('material.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(Material $material)
    {
        $this->authorize('delete', Material::class);
    }
}
