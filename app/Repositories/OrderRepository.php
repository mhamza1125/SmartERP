<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Models\Stock;
use App\Models\Order;

class OrderRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Order::join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->orderBy('orders.created_at', 'desc')->get();
    }

    public function active()
    {
        // Only Confirmed orders (status = 2) for issuance/PTC dropdowns
        // Draft (1), Dispatched (3), Delivered (4), Cancelled (5) are excluded
        return Order::where('order_status', '=', '2')
            ->orderBy('orders.created_at', 'desc')->get();
    }

    public function get($id)
    {
        return Order::where('order_id', $id)
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            // customers.* also has a description column and would hide the order's own one
            ->select('orders.*', 'customers.*', 'orders.description as description', 'customers.description as customer_description')
            ->first();
    }

    /**
     * Descriptions of the given orders that have one, keyed by order no
     * (for documents that cover several orders, such as the commercial invoice).
     */
    public function descriptions(iterable $orderIds)
    {
        $ids = collect($orderIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return Order::whereIn('order_id', $ids)
            ->whereNotNull('description')
            ->orderBy('order_id')
            ->pluck('description', 'order_no')
            ->map(fn ($text) => trim(strip_tags((string) $text)))
            ->filter(fn ($text) => $text !== '');
    }

    public function getOrder($id)
    {
        // Used By Transaction AjaxOrder
        return Order::where('orders.customer_id', $id)
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select('orders.*', 'customers.*')
            ->orderBy('orders.created_at', 'desc')
            ->get();
    }

    public function getCustomerOrders($customerId)
    {
        // Get only Confirmed orders for multi-order delivery
        // Only Confirmed (2) orders can be used for delivery creation
        // Exclude orders that have been fully delivered (delivery_status = 4)
        return Order::where('orders.customer_id', $customerId)
            ->where('order_status', '=', 2) // Only Confirmed orders
            ->whereNotExists(function ($query) {
                $query->select('deliveries.delivery_id')
                      ->from('deliveries')
                      ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
                      ->whereColumn('stocks.order_id', 'orders.order_id')
                      ->where('deliveries.delivery_status', '=', 4); // Only exclude fully delivered orders
            })
            ->select('orders.order_id', 'orders.job_no', 'orders.order_date', 'orders.order_status')
            ->orderBy('orders.created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $statusMap = [
                    1 => 'Draft',
                    2 => 'Confirmed',
                    3 => 'Dispatched',
                    4 => 'Delivered',
                    5 => 'Cancelled'
                ];
                $order->status = $statusMap[$order->order_status] ?? 'Unknown';
                return $order;
            });
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();

        // Auto-generate order_no if not provided
        if (empty($data['order_no'])) {
            $data['order_no'] = Order::generateOrderNo();
        }

        $store = Order::create($data);

        return $store->order_id;
    }

    public function update($id, array $data)
    {
        $update = Order::findOrFail($id);
        $update->update($data);

        return $update->order_id;
    }

    /**
     * An order can be deleted only while no delivery, PTC / issuance, purchase, payment, packing
     * list or issuance group refers to it. Its lines go with it; stock batches reserved for it
     * become free stock.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $order = Order::findOrFail($id);
        $impact = DeletionImpact::for('Order '.$order->order_no);
        $deliveries = $this->deliveryStockIds($id);

        $impact->blockIf(count($deliveries), 'delivery', 'deliveries', DB::table('deliveries')->whereIn('stock_id', $deliveries)->limit(6)->pluck('delivery_no'));
        $production = fn ($q) => $q->where('stock_status', '!=', Stock::STATUS_DELIVERY)->where(fn ($w) => $w->whereNull('ptc_id')->orWhere('is_ptc_master', 1));
        $impact->blockIf($this->refCount('stocks', 'order_id', $id, $production), 'PTC / issuance', 'PTCs / issuances', $this->refExamples('stocks', 'order_id', $id, 'stock_no', $production));
        $impact->blockIf($this->refCount('purchases', 'order_id', $id), 'purchase', null, $this->refExamples('purchases', 'order_id', $id, 'purchase_no'));
        $impact->blockIf($this->refCount('transactions', 'order_id', $id, fn ($q) => $q->where('transaction_to', 'customer')), 'customer payment');
        $impact->blockIf($this->refCount('packing_lists', 'order_id', $id), 'packing list');
        $impact->blockIf($this->refCount('igroups', 'order_id', $id), 'issuance group', null, $this->refExamples('igroups', 'order_id', $id, 'igroup_no'));

        $impact->cascadeIf($this->refCount('order_items', 'order_id', $id), 'order line');
        $reserved = $this->refCount('stock_batches', 'order_id', $id);
        if ($reserved) {
            $impact->note($reserved.' stock batch'.($reserved === 1 ? ' reserved for this order becomes' : 'es reserved for this order become').' free stock.');
        }

        return $impact;
    }

    /**
     * Delivery stocks rows of an order: single-order deliveries, combined deliveries listing it
     * in stock_no ("3|5"), and deliveries with a line booked to it.
     */
    private function deliveryStockIds($orderId): array
    {
        return DB::table('stocks')
            ->where('stock_status', Stock::STATUS_DELIVERY)
            ->where(fn ($q) => $q->where('order_id', $orderId)
                ->orWhereRaw("FIND_IN_SET(?, REPLACE(stock_no, '|', ','))", [(string) $orderId])
                ->orWhereExists(fn ($e) => $e->from('stock_items')->whereColumn('stock_items.stock_id', 'stocks.stock_id')->where('stock_items.order_id', $orderId)))
            ->pluck('stock_id')
            ->all();
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        DB::table('stock_batches')->where('order_id', $id)->update(['order_id' => null, 'updated_at' => now()]);
        DB::table('order_items')->where('order_id', $id)->delete();

        return $order->delete();
    }
}
