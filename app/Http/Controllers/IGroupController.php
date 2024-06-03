<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\IGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\IGroupRequest;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\IGroupRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\IGroupItemRepository;

class IGroupController extends Controller
{
    protected $orderRepository;
    protected $stockRepository;
    protected $igroupRepository;
    protected $materialRepository;
    protected $igroupItemRepository;

    public function __construct(
        StockRepository $stockRepository,  
        IGroupRepository $igroupRepository,  
        OrderRepository $orderRepository,  
        MaterialRepository $materialRepository,
        IGroupItemRepository $igroupItemRepository, 
    ){
        $this->middleware(['auth', 'all']);
        $this->orderRepository = $orderRepository;
        $this->igroupRepository = $igroupRepository;
        $this->stockRepository = $stockRepository;
        $this->materialRepository = $materialRepository;
        $this->igroupItemRepository = $igroupItemRepository;
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
