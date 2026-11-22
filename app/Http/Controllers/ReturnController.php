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
use App\Repositories\RejectionRepository;
use App\Exceptions\StockBatchException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        ] + $this->stageOptions($receive['purchase_type'], $receiveMaterial));
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

        try {
            $getId = DB::transaction(function () use ($request, $validatedData, $rid, $quantities, $remarks) {
                $choices = $this->returnRepository->stageChoices($request->input());
                $getId = $this->returnRepository->store($validatedData);
                $this->storeRM($getId, $rid, $quantities, $remarks, $choices);
                $this->returnRepository->syncStageCorrections($getId);

                return $getId;
            });
        } catch (StockBatchException $e) {
            return redirect()->back()->with(['fails' => $e->getMessage()])->withInput();
        }

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
        ] + $this->stageOptions($return['purchase_type'], $receiveMaterial, (int) $id));
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

        try {
            DB::transaction(function () use ($request, $id) {
                $data = $request->input();
                $data['stage_choice'] = $this->returnRepository->stageChoices($data);
                // the old stage corrections are linked to the lines, so they go before the lines change
                $this->returnRepository->purgeStageCorrections((int) $id);
                $this->returnRepository->update($id, $data);
                $this->returnMaterialRepository->update($id, $data);
                $this->returnRepository->syncStageCorrections((int) $id);
            });
        } catch (StockBatchException $e) {
            return redirect()->back()->with(['fails' => $e->getMessage()])->withInput();
        }

        return redirect()->route('return.show', $id)->with('success', 'Record Updated Successfully');
    }


    private function storeRM($getId, $rids, $quantities, $remarks, array $choices = [])
    {
        foreach ($quantities as $key => $quantity) {
            $id = $rids[$key] ?? null;
            $remark = $remarks[$key] ?? null;
            $returnMaterial = [
                'return_id' => $getId,
                'receive_material_id' => $id,
                'quantity' => $quantity,
                'stage_id' => $choices[$key]['stage_id'] ?? null,
                'rejected_stage_id' => $choices[$key]['rejected_stage_id'] ?? null,
                'remarks' => $remark,
            ];
            $this->returnMaterialRepository->store($returnMaterial);
        }
    }

    /**
     * Return From choices of a product return form: per product size its stages, and the rejection
     * stock it holds per rejected-at stage (general stock; a return being edited gives its own back).
     */
    private function stageOptions(string $purchaseType, $receiveLines, ?int $returnId = null): array
    {
        if ($purchaseType === 'material') {
            return ['productStages' => [], 'rejectionStock' => []];
        }
        $rejection = app(RejectionRepository::class);
        $productTypeIds = DB::table('receive_materials')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->whereIn('receive_materials.receive_material_id', collect($receiveLines)->pluck('receive_material_id'))
            ->pluck('purchase_items.product_type_id', 'receive_materials.receive_material_id');
        $exclude = $returnId ? DB::table('stocks')->where('table_name', \App\Models\Stock::TABLE_PURCHASE_RETURN)
            ->whereIn('source_id', DB::table('return_materials')->where('return_id', $returnId)->select('return_material_id'))
            ->pluck('stock_id')->all() : [];

        return [
            'lineProductType' => $productTypeIds->all(),
            'productStages' => $productTypeIds->unique()->mapWithKeys(fn ($pt) => [$pt => $rejection->rejectableStages((int) $pt)])->all(),
            'rejectionStock' => $rejection->generalBuckets($productTypeIds->unique()->values()->all(), $exclude),
        ];
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

    /**
     * What deleting this record would affect (JSON for the delete button's confirmation).
     */
    public function deleteCheck($id)
    {
        $this->authorize('delete', Returns::class);

        return $this->deletionCheckResponse($this->returnRepository->deletionImpact($id));
    }

    /**
     * Deletes the record (refused while other records depend on it; see deletionImpact()).
     */
    public function destroy($id)
    {
        $this->authorize('delete', Returns::class);

        return $this->runDeletion(
            $this->returnRepository->deletionImpact($id),
            fn () => $this->returnRepository->delete($id),
            route('return')
        );
    }
}
