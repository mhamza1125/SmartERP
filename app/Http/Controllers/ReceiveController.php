<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiveRequest;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\ReceiveMaterialRepository;
use App\Repositories\ReceiveRepository;
use Illuminate\Http\Request;

class ReceiveController extends Controller
{
    protected $receiveRepository;

    protected $purchaseRepository;

    protected $purchaseItemRepository;

    protected $receiveMaterialRepository;

    public function __construct(
        ReceiveRepository $receiveRepository,
        PurchaseRepository $purchaseRepository,
        PurchaseItemRepository $purchaseItemRepository,
        ReceiveMaterialRepository $receiveMaterialRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->receiveRepository = $receiveRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
    }

    public function index()
    {
        $purchase = $this->purchaseRepository->all();
        $receive = $this->receiveRepository->all();

        return view('receive', [
            'purchase' => $purchase,
            'receive' => $receive,
        ]);
    }

    public function create($id)
    {
        $purchase = $this->purchaseRepository->get($id);
        if($purchase['purchase_type'] == 'product'){
            $purchaseItem = $this->purchaseItemRepository->receiveProducts($id);
        }else{
            // For material and mProcess
            $purchaseItem = $this->purchaseItemRepository->receive($id);
        }
        $count = $this->receiveRepository->refNo($id);

        return view('addReceive', [
            'count' => $count,
            'purchase' => $purchase,
            'purchaseItem' => $purchaseItem,
        ]);
    }

    public function store(ReceiveRequest $request)
    {
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $pid = $request->input('purchase_item_id');
        $quantities = $request->input('quantity');
        $pending = $request->input('pending_qty');
        $approved = $request->input('approved_qty');
        $rejected = $request->input('rejected_qty');
        $inspections = $request->input('inspection_status');
        $idates = $request->input('inspection_date');
        $getId = $this->receiveRepository->store($validatedData);
        $this->storeRM($getId, $pid, $quantities, $inspections, $idates, $pending, $approved, $rejected);

        return redirect()->route('receive.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $receive = $this->receiveRepository->get($id);
        if($receive['purchase_type'] == 'product'){
            $receiveMaterial = $this->receiveMaterialRepository->get2($id);
        } else {
            // For material and mProcess
            $receiveMaterial = $this->receiveMaterialRepository->get($id);
        }

        return view('receiveInfo', [
            'receive' => $receive,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    /**
     * Print receive information
     */
    public function printReceive($id)
    {
        $receive = $this->receiveRepository->get($id);
        if($receive['purchase_type'] == 'product'){
            $receiveMaterial = $this->receiveMaterialRepository->get2($id);
        } else {
            // For material and mProcess
            $receiveMaterial = $this->receiveMaterialRepository->get($id);
        }

        return view('print.receive', [
            'receive' => $receive,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    public function edit($id)
    {
        $receive = $this->receiveRepository->get($id);
        $date = $this->purchaseRepository->get($receive['purchase_id']);
        if($receive['purchase_type'] == 'product'){
            $receiveMaterial = $this->receiveMaterialRepository->get2($id);
            $purchaseItem = $this->purchaseItemRepository->editReceive2($receive['purchase_id'], $id);
        }else{
            // For material and mProcess
            $receiveMaterial = $this->receiveMaterialRepository->get($id);
            $purchaseItem = $this->purchaseItemRepository->editReceive($receive['purchase_id'], $id);
        }
        return view('editReceive', [
            'date' => $date,
            'receive' => $receive,
            'purchaseItem' => $purchaseItem,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->receiveRepository->update($id, $request->input());
        $this->receiveMaterialRepository->update($id, $request->input());

        return redirect()->route('receive.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function updateStatus($id, $status)
    {
        $receiveStatus = ['receive_status' => $status];
        $this->receiveRepository->update($id, $receiveStatus);

        return redirect()->route('receive')->with('success', 'Status Updated Successfully');
    }

    public function destroy(ReceiveMaterial $receive)
    {
    }

    private function storeRM($getId, $pids, $quantities, $inspections, $idates, $pending, $approved, $rejected)
    {
        foreach ($quantities as $key => $quantity) {
            $pid = $pids[$key] ?? null;
            $status = $inspections[$key] ?? null;
            $idate = $idates[$key] ?? null;
            $pqty = $pending[$key] ?? 0;
            $aqty = $approved[$key] ?? 0;
            $rqty = $rejected[$key] ?? 0;
            $receiveMaterial = [
                'receive_id' => $getId,
                'purchase_item_id' => $pid,
                'quantity' => $quantity,
                'inspection_status' => $status,
                'inspection_date' => $idate,
                'pending_qty' => $pqty,
                'approved_qty' => $aqty,
                'rejected_qty' => $rqty,
            ];
            $this->receiveMaterialRepository->store($receiveMaterial);
        }
    }
}
