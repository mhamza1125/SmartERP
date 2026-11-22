<?php

namespace App\Http\Controllers;

use App\Exceptions\StockBatchException;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\StockAdjustment;
use App\Repositories\MaterialRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\RejectionRepository;
use App\Repositories\StockAdjustmentRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Production > Stock Adjustments: increase / decrease material or product stock. */
class StockAdjustmentController extends Controller
{
    public function __construct(protected StockAdjustmentRepository $repository)
    {
        $this->middleware(['auth', 'all']);
    }

    public function index()
    {
        $this->authorize('access', StockAdjustment::class);

        return view('stockAdjustment', ['adjustments' => $this->repository->all()]);
    }

    public function create(MaterialRepository $materials, ProductRepository $products, OrderRepository $orders, RejectionRepository $rejection)
    {
        $this->authorize('create', StockAdjustment::class);
        $productTypes = $products->activeTypes();

        return view('addStockAdjustment', [
            'stockNo' => $this->repository->refNo(),
            'materials' => $materials->all(),
            'productTypes' => $productTypes,
            // stages per product size for the Stage / Rejected At selects
            'stagesByType' => $rejection->stagesForTypes($productTypes),
            // rejection stock per rejected-at stage, shown next to the Rejected At choice
            'rejectionStock' => $rejection->generalBuckets(),
            'orders' => $orders->active(),
        ]);
    }

    public function store(StockAdjustmentRequest $request)
    {
        $this->authorize('create', StockAdjustment::class);

        try {
            $id = $this->repository->store($request->validated());
        } catch (ValidationException $e) {
            throw $e;
        } catch (StockBatchException $e) {
            return back()->withInput()->with('fails', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Stock adjustment could not be saved: '.$e->getMessage());

            return back()->withInput()->with('fails', 'The adjustment could not be saved. Nothing was changed.');
        }

        return redirect()->route('stockAdjustment.show', $id)->with('success', 'Stock adjustment saved');
    }

    public function show($id)
    {
        $this->authorize('show', StockAdjustment::class);

        return view('stockAdjustmentInfo', [
            'adjustment' => $this->repository->get($id),
            'lines' => $this->repository->lines($id),
        ]);
    }

    public function deleteCheck($id)
    {
        $this->authorize('delete', StockAdjustment::class);

        return $this->deletionCheckResponse($this->repository->deletionImpact($id));
    }

    public function destroy($id)
    {
        $this->authorize('delete', StockAdjustment::class);

        return $this->runDeletion(
            $this->repository->deletionImpact($id),
            fn () => $this->repository->delete($id),
            route('stockAdjustment')
        );
    }
}
