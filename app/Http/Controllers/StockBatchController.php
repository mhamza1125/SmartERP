<?php

namespace App\Http\Controllers;

use App\Exceptions\StockBatchException;
use App\Http\Requests\StockBatchReserveRequest;
use App\Http\Requests\StockBatchSplitRequest;
use App\Models\StockBatch;
use App\Repositories\OrderRepository;
use App\Repositories\StockBatchRepository;
use Illuminate\Http\Request;

/**
 * Batches of product stock: where each lot came from, which order it is reserved for,
 * its movement history, and changing / splitting reservations.
 */
class StockBatchController extends Controller
{
    public function __construct(
        protected StockBatchRepository $stockBatchRepository,
        protected OrderRepository $orderRepository,
    ) {
        $this->middleware(['auth', 'all']);
    }

    public function index(Request $request)
    {
        $this->authorize('access', StockBatch::class);

        $withEmpty = $request->boolean('all');

        return view('batches', [
            'batches' => $this->stockBatchRepository->listing($withEmpty),
            'withEmpty' => $withEmpty,
        ]);
    }

    public function show($id)
    {
        $this->authorize('show', StockBatch::class);

        return view('batchInfo', [
            'batch' => $this->stockBatchRepository->detail($id),
            'orders' => $this->orderRepository->all(),
        ]);
    }

    public function reserve(StockBatchReserveRequest $request, $id)
    {
        $data = $request->validated();
        $batch = $this->stockBatchRepository->reserve($id, $data['order_id'] ?? null, $data['note'] ?? null);

        return redirect()->route('batch.show', $id)->with('success', $batch->order_id
            ? 'Batch reserved for the selected order.'
            : 'Batch is now free stock.');
    }

    public function split(StockBatchSplitRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $child = $this->stockBatchRepository->split(
                $id, $data['stage_id'], (float) $data['quantity'], $data['order_id'] ?? null, $data['transfer_date'], $data['note'] ?? null
            );
        } catch (StockBatchException $e) {
            return redirect()->back()->withInput()->with('fails', $e->getMessage());
        }

        return redirect()->route('batch.show', $child->batch_id)->with('success', "Split into batch {$child->batch_no}.");
    }
}
