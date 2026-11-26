<?php

namespace App\Http\Controllers;

use App\Exceptions\DocumentLockedException;
use App\Http\Requests\ReceiveRequest;
use App\Http\Requests\ReceiveUpdateRequest;
use App\Models\Receive;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\ReceiveMaterialRepository;
use App\Repositories\ReceiveRepository;

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
        $this->authorize('access', Receive::class);
        $purchase = $this->purchaseRepository->all();
        $receive = $this->receiveRepository->all();

        return view('receive', [
            'purchase' => $purchase,
            'receive' => $receive,
        ]);
    }

    public function create($id)
    {
        $this->authorize('create', Receive::class);
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
        $this->authorize('create', Receive::class);
        $validatedData = $request->validated();
        if (array_sum($validatedData['quantity']) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        try {
            // One transaction: purchase lines locked, over-receipt refused (ReceiveRepository::saveReceipt)
            $getId = $this->receiveRepository->saveReceipt(null, $validatedData);
        } catch (DocumentLockedException $e) {
            return redirect()->back()->with(['fails' => $e->getMessage()])->withInput();
        }

        return redirect()->route('receive.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Receive::class);
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
        $this->authorize('show', Receive::class);
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
        $this->authorize('edit', Receive::class);
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

    public function update(ReceiveUpdateRequest $request, $id)
    {
        $this->authorize('edit', Receive::class);
        $validatedData = $request->validated();
        if (array_sum($validatedData['quantity']) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        try {
            // Explicit fields only; returned / used lines cannot be lowered or dropped (D4)
            $this->receiveRepository->saveReceipt((int) $id, $validatedData);
        } catch (DocumentLockedException $e) {
            return redirect()->back()->with(['fails' => $e->getMessage()])->withInput();
        }

        return redirect()->route('receive.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function updateStatus($id, $status)
    {
        $this->authorize('edit', Receive::class);
        // 0 Pending, 1 Checked (the only receive statuses); POST only
        abort_unless(in_array((string) $status, ['0', '1'], true), 404);
        $receiveStatus = ['receive_status' => (int) $status];
        $this->receiveRepository->update($id, $receiveStatus);

        return redirect()->route('receive')->with('success', 'Status Updated Successfully');
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

    /**
     * What deleting this record would affect (JSON for the delete button's confirmation).
     */
    public function deleteCheck($id)
    {
        $this->authorize('delete', Receive::class);

        return $this->deletionCheckResponse($this->receiveRepository->deletionImpact($id));
    }

    /**
     * Deletes the record (refused while other records depend on it; see deletionImpact()).
     */
    public function destroy($id)
    {
        $this->authorize('delete', Receive::class);

        return $this->runDeletion(
            $this->receiveRepository->deletionImpact($id),
            fn () => $this->receiveRepository->delete($id),
            route('receive')
        );
    }
}
