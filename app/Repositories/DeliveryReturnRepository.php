<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Exceptions\DeletionBlockedException;
use App\Models\DeliveryReturn;

class DeliveryReturnRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return DeliveryReturn::join('deliveries', 'deliveries.delivery_id', '=', 'delivery_returns.delivery_id')
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'delivery_returns.*',
                'orders.order_no',
                'orders.job_no',
                'customers.fname',
                'customers.lname',
                'stocks.stock_no'
            )
            ->orderBy('delivery_returns.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        return DeliveryReturn::where('delivery_returns.delivery_return_id', $id)
            ->join('deliveries', 'deliveries.delivery_id', '=', 'delivery_returns.delivery_id')
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select(
                'delivery_returns.*',
                'deliveries.*',
                'orders.order_no',
                'orders.job_no',
                'customers.fname',
                'customers.lname',
                'stocks.stock_no',
                'delivery_returns.description as return_description'
            )
            ->first();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = DeliveryReturn::create($data);

        return $store->delivery_return_id;
    }

    public function update($id, array $data)
    {
        $update = DeliveryReturn::findOrFail($id);
        $update->update($data);

        return $update->delivery_return_id;
    }

    /**
     * A delivery return can be deleted only while the quantity it put back into stock is still
     * there. Its lines and stock entry go with it.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $return = DeliveryReturn::findOrFail($id);
        $impact = DeletionImpact::for('Delivery return '.$return->return_no);
        $stockIds = $this->returnStockIds($id);

        foreach (app(StockItemRepository::class)->stockInRemovalShortfalls($stockIds) as $shortfall) {
            $impact->block('returned stock already used: '.$shortfall);
        }
        $impact->cascadeIf($this->refCount('delivery_return_items', 'delivery_return_id', $id), 'returned line');
        $impact->note('The returned quantity leaves stock again and the delivery counts as fully delivered.');

        return $impact;
    }

    /**
     * Stock-in rows created for a delivery return (DeliveryReturnController::adjustStock).
     */
    private function returnStockIds($id): array
    {
        return DB::table('stocks')->where('table_name', 'delivery_returns')->where('source_id', $id)->pluck('stock_id')->all();
    }

    public function delete($id)
    {
        $return = DeliveryReturn::findOrFail($id);
        $stockIds = $this->returnStockIds($id);
        DB::table('stocks')->whereIn('stock_id', $stockIds)->lockForUpdate()->get();

        $shortfalls = app(StockItemRepository::class)->stockInRemovalShortfalls($stockIds);
        if ($shortfalls) {
            throw new DeletionBlockedException('Delivery return '.$return->return_no.' cannot be deleted: '.implode('; ', $shortfalls));
        }

        app(StockRepository::class)->purge($stockIds);
        DB::table('delivery_return_items')->where('delivery_return_id', $id)->delete();

        return $return->delete();
    }
}
