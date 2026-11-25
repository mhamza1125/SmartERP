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

    /**
     * Dashboard "Orders in Progress": every confirmed (running) order, nearest due date first
     * (no due date last), with its number of lines and total quantity.
     */
    public function inProgress()
    {
        return Order::join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->where('orders.order_status', Order::STATUS_CONFIRMED)
            ->select('orders.order_id', 'orders.order_no', 'orders.job_no', 'orders.order_date', 'orders.due_date',
                'customers.customer_no', 'customers.fname', 'customers.lname')
            ->selectSub(DB::table('order_items')->whereColumn('order_items.order_id', 'orders.order_id')->selectRaw('COUNT(*)'), 'item_count')
            ->selectSub(DB::table('order_items')->whereColumn('order_items.order_id', 'orders.order_id')->selectRaw('COALESCE(SUM(quantity), 0)'), 'total_qty')
            ->orderByRaw('orders.due_date IS NULL')
            ->orderBy('orders.due_date')
            ->orderBy('orders.job_no')
            ->get();
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

    /** Delivered quantity per order line, "product_type_id|stage_id" => qty (deliveries made for this order). */
    public function deliveredByLine(int $orderId): array
    {
        return DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stocks.stock_type', 2)
            ->where('stocks.stock_status', \App\Models\Stock::STATUS_DELIVERY)
            ->where('stock_items.material_id', 0)
            ->where(fn ($q) => $q->where('stock_items.order_id', $orderId)
                ->orWhere(fn ($q) => $q->whereNull('stock_items.order_id')->where('stocks.order_id', $orderId)))
            ->groupBy('stock_items.product_type_id', 'stock_items.stage_id')
            ->selectRaw('CONCAT(stock_items.product_type_id, "|", stock_items.stage_id) as line_key, SUM(stock_items.quantity) as qty')
            ->pluck('qty', 'line_key')
            ->map(fn ($qty) => (float) $qty)
            ->filter(fn ($qty) => $qty > 0.0001)
            ->all();
    }

    /**
     * F-40: delivered (all deliveries of the order, the one being saved included) may not exceed the
     * ordered quantity of an order line. The delivery form limits this in the browser only.
     *
     * @throws \App\Exceptions\DocumentLockedException
     */
    public function assertNotOverDelivered(int $orderId): void
    {
        $ordered = DB::table('order_items')->where('order_id', $orderId)
            ->groupBy('product_type_id', 'product_stage_id')
            ->selectRaw('CONCAT(product_type_id, "|", product_stage_id) as line_key, SUM(quantity) as qty')
            ->pluck('qty', 'line_key');
        foreach ($this->deliveredByLine($orderId) as $key => $delivered) {
            if (! $ordered->has($key) || $delivered <= (float) $ordered[$key] + 0.0001) {
                continue; // lines not on this order are not judged here
            }
            [$pt, $stage] = array_map('intval', explode('|', $key));
            $fmt = fn ($q) => rtrim(rtrim(number_format((float) $q, 2, '.', ','), '0'), '.');
            throw new \App\Exceptions\DocumentLockedException(
                app(StockBatchRepository::class)->productLabel($pt, $stage).": ordered {$fmt($ordered[$key])}, this would make the delivered total {$fmt($delivered)}. Delivering more than was ordered is not allowed."
            );
        }
    }

    public function hasDeliveries(int $orderId): bool
    {
        return $this->deliveredByLine($orderId) !== [];
    }

    /** Stock reserved for the order (batches with a positive balance). */
    public function hasReservedStock(int $orderId): bool
    {
        return app(StockBatchRepository::class)->balances(['order_id' => $orderId])->contains(fn ($row) => (float) $row->balance > 0.0001);
    }

    /**
     * D4: once delivered, an order keeps its customer, and each delivered line keeps its price,
     * at least the delivered quantity and its place on the order. Harmless fields stay editable.
     *
     * @param  array  $lines  product_type_id[], product_stage_id[], quantity[], price[]
     *
     * @throws \App\Exceptions\DocumentLockedException
     */
    public function assertDeliveredLinesKept(Order $order, int $customerId, array $lines): void
    {
        $delivered = $this->deliveredByLine((int) $order->order_id);
        if ($delivered === []) {
            return;
        }
        $askAdmin = \App\Exceptions\DocumentLockedException::ASK_ADMIN;
        if ((int) $order->customer_id !== $customerId) {
            throw new \App\Exceptions\DocumentLockedException('This order has deliveries, so its customer cannot change.'.$askAdmin);
        }

        $new = [];
        foreach ((array) ($lines['quantity'] ?? []) as $key => $quantity) {
            $new[(int) $lines['product_type_id'][$key].'|'.(int) $lines['product_stage_id'][$key]] = ['quantity' => (float) $quantity, 'price' => (float) $lines['price'][$key]];
        }
        $items = DB::table('order_items')->where('order_id', $order->order_id)->get()
            ->keyBy(fn ($item) => (int) $item->product_type_id.'|'.(int) $item->product_stage_id);
        $stockItems = app(StockBatchRepository::class);

        foreach ($delivered as $key => $qty) {
            $item = $items->get($key);
            if (! $item) {
                continue; // delivered against another order line / legacy row: nothing on this order to protect
            }
            [$pt, $stage] = array_map('intval', explode('|', $key));
            $label = $stockItems->productLabel($pt, $stage);
            $line = $new[$key] ?? null;
            $qtyText = rtrim(rtrim(number_format($qty, 2, '.', ','), '0'), '.');
            $problem = match (true) {
                $line === null => 'cannot be removed',
                abs($line['price'] - (float) $item->price) > 0.0001 => 'cannot change its price',
                $line['quantity'] < $qty - 0.0001 => 'cannot be ordered below what was delivered',
                default => null,
            };
            if ($problem) {
                throw new \App\Exceptions\DocumentLockedException("{$label}: {$qtyText} already delivered, so this line {$problem}.".$askAdmin);
            }
        }
    }

    /**
     * D5: refuses a status change that is not allowed (see Order::TRANSITIONS). Same status = no change.
     *
     * @throws \App\Exceptions\DocumentLockedException
     */
    public function assertStatusChange(Order $order, int $to, ?\App\Models\User $user): void
    {
        $from = (int) $order->order_status;
        if ($from === $to) {
            return;
        }
        $labels = Order::STATUS_LABELS;
        if (! isset($labels[$to])) {
            throw new \App\Exceptions\DocumentLockedException('Unknown order status.');
        }
        $reopen = $to !== Order::STATUS_CANCELLED && (Order::SEQUENCE[$to] ?? 0) < (Order::SEQUENCE[$from] ?? 0);

        if ($reopen) {
            if (! $user || ! $user->can('reopen', Order::class)) {
                throw new \App\Exceptions\DocumentLockedException("Only an admin can move an order back from {$labels[$from]} to {$labels[$to]} (re-open).");
            }
        } elseif (! in_array($to, Order::TRANSITIONS[$from] ?? [], true)) {
            throw new \App\Exceptions\DocumentLockedException("An order cannot go from {$labels[$from]} to {$labels[$to]}.");
        }

        if (in_array($to, [Order::STATUS_DISPATCHED, Order::STATUS_DELIVERED], true) && ! $this->hasDeliveries((int) $order->order_id)) {
            throw new \App\Exceptions\DocumentLockedException("Nothing has been delivered on this order yet, so it cannot be {$labels[$to]}.");
        }
        if ($to === Order::STATUS_CANCELLED && ($this->hasDeliveries((int) $order->order_id) || $this->hasReservedStock((int) $order->order_id))) {
            throw new \App\Exceptions\DocumentLockedException('This order has delivered or reserved stock, so it cannot be cancelled.');
        }
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
