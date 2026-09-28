<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnRequest;
use App\Models\ReceiveMaterial;
use App\Models\Returns;
use App\Models\ReturnMaterial;
use App\Repositories\ReceiveMaterialRepository;
use App\Repositories\ReceiveRepository;
use App\Repositories\ReturnMaterialRepository;
use App\Repositories\ReturnRepository;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    protected $returnRepository;

    protected $receiveRepository;

    protected $receiveMaterialRepository;

    protected $returnMaterialRepository;

    public function __construct(
        ReturnRepository $returnRepository,
        ReceiveRepository $receiveRepository,
        ReceiveMaterialRepository $receiveMaterialRepository,
        ReturnMaterialRepository $returnMaterialRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->returnRepository = $returnRepository;
        $this->receiveRepository = $receiveRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
        $this->returnMaterialRepository = $returnMaterialRepository;
    }

    public function index()
    {
        $this->authorize('access', Returns::class);
        $return = $this->returnRepository->all();
        // $receive = $this->receiveRepository->pending();
        return view('return', [
            'return' => $return,
            // 'receive' => $receive,
        ]);
    }

    public function create($id)
    {
        $this->authorize('create', Returns::class);
        $receive = $this->receiveRepository->get($id);
        $returned = $this->returnRepository->returned($id);
        $count = $this->returnRepository->refNo($id);
        if($receive['purchase_type'] == 'material'){
            $receiveMaterial = $this->receiveMaterialRepository->get($id);
        }else{
            $receiveMaterial = $this->receiveMaterialRepository->get2($id);
        }
        $combined = $receiveMaterial->map(function ($item) use ($returned) {
            $returnedItem = $returned->firstWhere('receive_material_id', $item->receive_material_id);
            $item->rqty = $returnedItem->quantity ?? 0;

            return $item;
        });

        return view('addReturn', [
            'count' => $count,
            'receive' => $receive,
            'received' => $returned,
            'combined' => $combined,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    public function store(ReturnRequest $request)
    {
        $this->authorize('create', Returns::class);
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $rid = $request->input('receive_material_id');
        $quantities = $request->input('quantity');
        $remarks = $request->input('remarks');

        $validationError = $this->validateReturnQuantities($rid, $quantities);
        if ($validationError) {
            return redirect()->back()->with(['fails' => $validationError])->withInput();
        }

        $getId = $this->returnRepository->store($validatedData);
        $this->storeRM($getId, $rid, $quantities, $remarks);

        return redirect()->route('return.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Returns::class);
        $return = $this->returnRepository->get($id);
        if($return['purchase_type'] == 'material'){
            $returnMaterial = $this->returnMaterialRepository->get($id);
        } else {
            $returnMaterial = $this->returnMaterialRepository->get2($id);
        }

        return view('returnInfo', [
            'return' => $return,
            'returnMaterial' => $returnMaterial,
        ]);
    }

    /**
     * Print return information
     */
    public function printReturn($id)
    {
        $this->authorize('show', Returns::class);
        $return = $this->returnRepository->get($id);
        if($return['purchase_type'] == 'material'){
            $returnMaterial = $this->returnMaterialRepository->get($id);
        } else {
            $returnMaterial = $this->returnMaterialRepository->get2($id);
        }

        return view('print.return', [
            'return' => $return,
            'returnMaterial' => $returnMaterial,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('edit', Returns::class);
        $return = $this->returnRepository->get($id);
        $date = $this->receiveRepository->get($return['receive_id']);
        if($return['purchase_type'] == 'material'){
            $returnMaterial = $this->returnMaterialRepository->get($id);
            $receiveMaterial = $this->receiveMaterialRepository->get($return['receive_id']);
        } else {
            $returnMaterial = $this->returnMaterialRepository->get2($id);
            $receiveMaterial = $this->receiveMaterialRepository->get2($return['receive_id']);
        }

        return view('editReturn', [
            'date' => $date,
            'return' => $return,
            'returnMaterial' => $returnMaterial,
            'receiveMaterial' => $receiveMaterial,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Returns::class);
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }

        $rid = $request->input('receive_material_id');
        $quantities = $request->input('quantity');
        $validationError = $this->validateReturnQuantities($rid, $quantities, $id);
        if ($validationError) {
            return redirect()->back()->with(['fails' => $validationError])->withInput();
        }

        $this->returnRepository->update($id, $request->input());
        $this->returnMaterialRepository->update($id, $request->input());

        return redirect()->route('return.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(ReturnMaterial $return)
    {
    }

    private function storeRM($getId, $rids, $quantities, $remarks)
    {
        foreach ($quantities as $key => $quantity) {
            $id = $rids[$key] ?? null;
            $remark = $remarks[$key] ?? null;
            $returnMaterial = [
                'return_id' => $getId,
                'receive_material_id' => $id,
                'quantity' => $quantity,
                'remarks' => $remark,
            ];
            $this->returnMaterialRepository->store($returnMaterial);
        }
    }

    /**
     * Ensure each return quantity does not exceed what was actually approved on
     * receiving, minus whatever has already been returned against that same
     * receive line. This blocks two related problems: returning against a
     * receive line that is still pending/rejected (approved_qty = 0, so nothing
     * is returnable), and returning more than remains outstanding, either of
     * which would silently understate the computed stock balance since stock
     * totals are approved_qty minus returned quantity.
     *
     * @param  int|null  $excludeReturnId  when editing an existing return, exclude its own
     *                                     rows from the "already returned" total
     * @return string|null  an error message, or null when all quantities are valid
     */
    private function validateReturnQuantities(array $receiveMaterialIds, array $quantities, $excludeReturnId = null)
    {
        foreach ($quantities as $key => $quantity) {
            if ($quantity <= 0) {
                continue;
            }

            $receiveMaterialId = $receiveMaterialIds[$key] ?? null;
            $receiveMaterial = ReceiveMaterial::find($receiveMaterialId);
            if (! $receiveMaterial) {
                return 'One or more selected receive items could not be found.';
            }

            $alreadyReturnedQuery = ReturnMaterial::where('receive_material_id', $receiveMaterialId);
            if ($excludeReturnId) {
                $alreadyReturnedQuery->where('return_id', '!=', $excludeReturnId);
            }
            $alreadyReturned = $alreadyReturnedQuery->sum('quantity');
            $returnable = $receiveMaterial->approved_qty - $alreadyReturned;

            if ($quantity > $returnable) {
                return 'Return quantity exceeds the approved and not-yet-returned quantity for one or more items (only approved receive quantities can be returned).';
            }
        }

        return null;
    }
}
