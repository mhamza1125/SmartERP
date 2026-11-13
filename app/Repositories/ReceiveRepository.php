<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Exceptions\DeletionBlockedException;
use App\Models\StockBatch;
use App\Models\Receive;
use Carbon\Carbon;

class ReceiveRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Receive::join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->join('receive_materials', 'receive_materials.receive_id', 'receives.receive_id')
            ->select('receives.*', 'vendors.fname', 'purchases.purchase_no')
            ->groupBy('receive_materials.receive_id')
            ->selectRaw('SUM(receive_materials.pending_qty) as pqty')
            ->orderBy('receives.created_at', 'desc')->get();

        // Without Status of Pending / Checked etc
        return Receive::join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('receives.*', 'vendors.fname', 'purchases.purchase_no')
            ->orderBy('receives.created_at', 'desc')->get();
    }

    public function get($id)
    {
        return Receive::where('receives.receive_id', $id)
            ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('stocks', 'stocks.stock_id', '=', 'receives.receive_id')
            ->select('receives.*', 'purchases.*', 'vendors.*', 'orders.job_no', 'receives.description as desc', 'stocks.stock_status')
            ->first();
    }

    public function pending()
    {
        // Used to Add Return
        return Receive::where('receives.receive_status', '0')
            ->join('purchases', 'purchases.purchase_id', '=', 'receives.purchase_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->select('receives.*', 'vendors.fname', 'purchases.purchase_no')
            ->orderBy('receives.created_at', 'desc')->get();
    }

    public function refNo($id)
    {
        $yearMonth = Carbon::now()->format('ym');
        $count = Receive::where('purchase_id', $id)->count();

        return 'R'.$count + 1;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Receive::create($data);

        return $store->receive_id;
    }

    public function update($id, array $data)
    {
        $update = Receive::findOrFail($id);
        $update->update($data);

        return $update->receive_id;
    }

    /**
     * A receiving can be deleted only while nothing was returned against it and its approved
     * quantity is still in stock (not issued, delivered or used since).
     */
    public function deletionImpact($id): DeletionImpact
    {
        $receive = Receive::findOrFail($id);
        $impact = DeletionImpact::for('Receiving '.$receive->receive_no);
        $lineIds = DB::table('receive_materials')->where('receive_id', $id)->pluck('receive_material_id')->all();

        $impact->blockIf($this->refCount('returns', 'receive_id', $id), 'purchase return', null, $this->refExamples('returns', 'receive_id', $id, 'return_no'));
        foreach (app(StockItemRepository::class)->stockInRemovalShortfalls([], $lineIds) as $shortfall) {
            $impact->block('stock already used: '.$shortfall);
        }

        $impact->cascadeIf(count($lineIds), 'received line');
        $impact->note('The approved quantity leaves stock and the vendor ledger no longer shows this receiving.');

        return $impact;
    }

    public function delete($id)
    {
        $receive = Receive::findOrFail($id);
        $lines = DB::table('receive_materials')->where('receive_id', $id)->lockForUpdate()->get(['receive_material_id', 'purchase_item_id']);

        // Checked again under lock: stock may have been used since the user confirmed
        $shortfalls = app(StockItemRepository::class)->stockInRemovalShortfalls([], $lines->pluck('receive_material_id')->all());
        if ($shortfalls) {
            throw new DeletionBlockedException('Receiving '.$receive->receive_no.' cannot be deleted: '.implode('; ', $shortfalls));
        }

        DB::table('receive_materials')->where('receive_id', $id)->delete();
        app(StockBatchRepository::class)->deleteUnused(StockBatch::SOURCE_PURCHASE, $lines->pluck('purchase_item_id')->unique()->all());

        return $receive->delete();
    }
}
