<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\IGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\IGroupRequest;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\IGroupRepository;
use App\Repositories\VendorRepository;
use App\Repositories\MachineRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\IGroupItemRepository;
use App\Repositories\ProductCostRepository;
use App\Repositories\ProductMaterialRepository;

class IGroupController extends Controller
{
    protected $headRepository;
    protected $imageRepository;
    protected $orderRepository;
    protected $stockRepository;
    protected $igroupRepository;
    protected $vendorRepository;
    protected $machineRepository;
    protected $materialRepository;
    protected $employeeRepository;
    protected $stockItemRepository;
    protected $igroupItemRepository;
    protected $orderItemRepository;
    protected $productCostRepository;
    protected $productMaterialRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        StockRepository $stockRepository,  
        IGroupRepository $igroupRepository,  
        OrderRepository $orderRepository,  
        VendorRepository $vendorRepository,  
        MachineRepository $machineRepository,  
        MaterialRepository $materialRepository,
        EmployeeRepository $employeeRepository,
        OrderItemRepository $orderItemRepository,  
        StockItemRepository $stockItemRepository,  
        IGroupItemRepository $igroupItemRepository,  
        ProductCostRepository $productCostRepository,  
        ProductMaterialRepository $productMaterialRepository,  
    ){
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->orderRepository = $orderRepository;
        $this->igroupRepository = $igroupRepository;
        $this->stockRepository = $stockRepository;
        $this->vendorRepository = $vendorRepository;
        $this->machineRepository = $machineRepository;
        $this->materialRepository = $materialRepository;
        $this->employeeRepository = $employeeRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->igroupItemRepository = $igroupItemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productCostRepository = $productCostRepository;
        $this->productMaterialRepository = $productMaterialRepository;
    }

    public function index(){
        $igroup = $this->igroupRepository->all();
        return view('igroup', [
            'igroup' => $igroup,
        ]); 
    }

    public function create(){
        $order = $this->orderRepository->active();
        return view('addIGroup', [
            'order' => $order,
        ]);
    }

    public function store(IGroupRequest $request){
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $ptid = $request->input('product_type_id');
        $quantities = $request->input('quantity');
        $mid = $request->input('material_id');
        $stages = $request->input('stage_id');
        $getId = $this->igroupRepository->store($validatedData);
        $this->storeSI($getId, $ptid, $mid, $quantities, $stages);
        return redirect()->route('igroup.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id){
        $igroup = $this->igroupRepository->get($id);
        $igroupItem = $this->igroupItemRepository->get($id);
        return view('igroupInfo', [
            'igroup' => $igroup,
            'igroupItem' => $igroupItem,
        ]);
    }

    public function edit(IGroup $id){
        $order = $this->orderRepository->active();
        $igroup = $this->igroupRepository->get($id);
        $igroupItem = $this->igroupItemRepository->get($id->igroup_id);
        return view('editIGroup', [
            'igroup' => $id,
            'order' => $order,
            'igroupItem' => $igroupItem,
        ]);
    }
    
    public function update(Request $request, $id){
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->igroupRepository->update($id, $request->input());
        $this->igroupItemRepository->update($id, $request->input());
        return redirect()->route('igroup.show', $id)->with('success', 'Record Updated Successfully');
    }
    
    public function destroy(Stock $stock){}

    private function storeSI($getId, $ptids, $mids, $quantities, $stages){
        foreach ($quantities as $key => $quantity) {
            $ptid = $ptids[$key] ?? 0;
            $mid = $mids[$key] ?? 0;
            $stage = $stages[$key] ?? 0;
            $stockItem = [
                'igroup_id' => $getId,
                'product_type_id' => $ptid,
                'material_id' => $mid,
                'quantity' => $quantity,
                'stage_id' => $stage,
            ];
            $this->igroupItemRepository->store($stockItem);
        }
    }
}
