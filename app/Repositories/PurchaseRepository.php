<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Models\StockBatch;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseRepository implements GlobalInterface
{
    use ChecksReferences;

    public function __construct(protected StockBatchRepository $stockBatchRepository) {}

    public function all()
    {
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
            ->leftJoin('mprocess', 'mprocess.purchase_id', '=', 'purchases.purchase_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendor_no',
                DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
            ->whereNull('mprocess.purchase_id')
            ->groupBy('purchases.purchase_id')
            ->orderBy('purchases.created_at', 'desc')
            ->get();

        // All Purchases with Status Check
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendor_no',
                DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
            ->groupBy('purchases.purchase_id')
            ->orderBy('purchases.created_at', 'desc')
            ->get();

        // Without Status of Pending / Checked etc
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname')
            ->orderBy('purchases.created_at', 'desc')->get();
    }

    /**
     * Dashboard "Open Purchase Orders": purchases (not material processing) with quantity still to
     * receive. Per line, received = received qty - returned qty (as on the receive form), capped at the
     * ordered qty; balance = ordered - received. Status: Pending (nothing received yet) or Partially
     * Received. Nearest required date first (none last).
     */
    public function openPurchases()
    {
        $received = 'COALESCE((SELECT SUM(rm.quantity) FROM receive_materials rm WHERE rm.purchase_item_id = purchase_items.purchase_item_id), 0)'
            .' - COALESCE((SELECT SUM(ret.quantity) FROM return_materials ret JOIN receive_materials rr ON rr.receive_material_id = ret.receive_material_id'
            .' WHERE rr.purchase_item_id = purchase_items.purchase_item_id), 0)';

        $lines = DB::table('purchase_items')
            ->select('purchase_items.purchase_id', 'purchase_items.quantity')
            ->selectRaw("GREATEST(LEAST($received, purchase_items.quantity), 0) as received");

        $totals = DB::query()->fromSub($lines, 'l')
            ->groupBy('l.purchase_id')
            ->select('l.purchase_id')
            ->selectRaw('COUNT(*) as item_count, SUM(l.quantity) as ordered_qty, SUM(l.received) as received_qty, SUM(l.quantity - l.received) as balance_qty');

        return Purchase::joinSub($totals, 't', 't.purchase_id', '=', 'purchases.purchase_id')
            ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('mprocess')->whereColumn('mprocess.purchase_id', 'purchases.purchase_id'))
            ->where('t.balance_qty', '>', 0)
            ->select('purchases.purchase_id', 'purchases.purchase_no', 'purchases.purchase_type', 'purchases.purchase_date', 'purchases.require_date',
                'vendors.vendor_no', 'vendors.fname', 'orders.job_no',
                't.item_count', 't.ordered_qty', 't.received_qty', 't.balance_qty')
            ->selectRaw("CASE WHEN t.received_qty > 0 THEN 'Partially Received' ELSE 'Pending' END as receive_status")
            ->orderByRaw('purchases.require_date IS NULL')
            ->orderBy('purchases.require_date')
            ->orderBy('purchases.purchase_no')
            ->get();
    }

    public function mprocess()
    {
        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
            ->leftJoin('mprocess', 'mprocess.purchase_id', '=', 'purchases.purchase_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendors.vendor_no',
                DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
            ->whereNotNull('mprocess.purchase_id')
            ->orderBy('purchases.created_at', 'desc')
            ->groupBy('purchases.purchase_id')
            ->get();

        return Purchase::leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
            ->leftJoin('mprocess', 'mprocess.purchase_id', '=', 'purchases.purchase_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendor_no',
                DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
            ->whereNotNull('mprocess.purchase_id')
            ->groupBy('purchases.purchase_id', 'orders.job_no', 'vendors.fname', 'vendor_no', 'receives.purchase_id')
            ->orderBy('purchases.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        // Getting Without Check
        return Purchase::where('purchase_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendors.address', 'vendors.phone1', 'vendors.vendor_no', 'vendors.vendor_type')
            ->first();

        // Checking If it is received or not (Don't know why it's removed by Augmnt)
        return Purchase::where('purchases.purchase_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('receives', 'receives.purchase_id', '=', 'purchases.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname', 'vendors.address', 'vendors.phone1',
                DB::raw('CASE WHEN receives.purchase_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
            ->groupBy('purchases.purchase_id')
            ->first();

    }

    public function getPurchase($id)
    {
        return Purchase::where('purchases.vendor_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('purchases.*', 'orders.job_no', 'vendors.fname')
            ->orderBy('purchases.created_at', 'desc')->get();
    }

    public function refNo()
    {
        $yearMonth = Carbon::now()->format('ym');
        $count = Purchase::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        $threeDigitNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return 'P'.$yearMonth.$threeDigitNumber;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Purchase::create($data);

        return $store->purchase_id;
    }

    public function update($id, array $data)
    {
        $update = Purchase::findOrFail($id);
        $update->update($data);
        // Received stock stays reserved for the purchase's (new) order
        if ($update->wasChanged('order_id')) {
            $this->stockBatchRepository->syncPurchaseOrder($id, $update->order_id);
        }

        return $update->purchase_id;
    }

    /**
     * A purchase (or material processing order) can be deleted only while nothing was received
     * against it and no vendor payment is linked to it. Its lines go with it; for material
     * processing, the material sent out comes back into stock.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $purchase = Purchase::findOrFail($id);
        $impact = DeletionImpact::for('Purchase '.$purchase->purchase_no);
        $itemIds = DB::table('purchase_items')->where('purchase_id', $id)->pluck('purchase_item_id')->all();

        $impact->blockIf($this->refCount('receives', 'purchase_id', $id), 'receiving', null, $this->refExamples('receives', 'purchase_id', $id, 'receive_no'));
        $impact->blockIf(
            $this->refCount('transactions', 'order_id', $id, fn ($q) => $q->where('transaction_to', 'vendor')->where('transaction_type', 'payment')),
            'vendor payment'
        );

        $impact->cascadeIf(count($itemIds), 'purchase line');
        $processed = $this->refCount('mprocess', 'purchase_id', $id);
        if ($processed) {
            $impact->cascadeIf($processed, 'material processing line');
            $impact->note('The material sent out for processing is returned to material stock.');
        }

        return $impact;
    }

    public function delete($id)
    {
        $purchase = Purchase::findOrFail($id);
        $itemIds = DB::table('purchase_items')->where('purchase_id', $id)->pluck('purchase_item_id')->all();

        // Material processing: the raw material stock-out rows created for each line
        $processStockIds = DB::table('mprocess')->join('stock_items', 'stock_items.stock_item_id', '=', 'mprocess.stock_item_id')
            ->where('mprocess.purchase_id', $id)->where('stock_items.stock_id', '>', 1)->pluck('stock_items.stock_id')->unique()->all();
        DB::table('mprocess')->where('purchase_id', $id)->delete();
        app(StockRepository::class)->purge($processStockIds);

        DB::table('purchase_items')->where('purchase_id', $id)->delete();
        app(StockBatchRepository::class)->deleteUnused(StockBatch::SOURCE_PURCHASE, $itemIds);

        return $purchase->delete();
    }
}
