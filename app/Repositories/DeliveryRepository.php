<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Models\Delivery;

class DeliveryRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        $deliveries = Delivery::where('stocks.stock_status', '3') // Delivery
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->orderBy('stocks.stock_date', 'desc')
            ->get();

        // Check for multi-order deliveries by checking if the stock_no contains multiple order references
        // Multi-order deliveries typically have stock_no patterns that indicate multiple orders
        foreach ($deliveries as $delivery) {
            // Simple heuristic: check if stock_no contains patterns suggesting multi-order
            // or check if there are multiple distinct product_type_id + stage_id combinations
            // that suggest items from different orders
            $distinctItemTypes = \DB::table('stock_items')
                ->where('stock_id', $delivery->stock_id)
                ->distinct()
                ->count(\DB::raw('CONCAT(product_type_id, "_", stage_id)'));

            // Check if stock_no contains pipe-separated order IDs (new format: order_id1|order_id2|order_id3)
            $hasMultiOrderPattern = strpos($delivery->stock_no, '|') !== false;

            // Fallback: Check for old patterns (for backward compatibility)
            if (! $hasMultiOrderPattern) {
                $hasMultiOrderPattern = strpos($delivery->stock_no, ',') !== false ||
                                       strpos($delivery->stock_no, 'Multi') !== false ||
                                       $distinctItemTypes > 5; // Threshold for multi-order detection
            }

            $delivery->is_multi_order = $hasMultiOrderPattern;
        }

        return $deliveries;
    }

    public function get($id)
    {
        return Delivery::where('deliveries.delivery_id', $id)
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->join('customers', 'customers.customer_id', 'orders.customer_id')
            ->select('*', 'stocks.description')
            ->first();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();

        // Auto-generate delivery_no if not provided
        if (empty($data['delivery_no'])) {
            $data['delivery_no'] = Delivery::generateDeliveryNo();
        }

        $data = $this->normalizeFreight($data);
        $store = Delivery::create($data);

        return $store->delivery_id;
    }

    public function update($id, array $data)
    {
        $update = Delivery::findOrFail($id);
        $update->update($this->normalizeFreight($data));

        return $update->delivery_id;
    }

    /**
     * A delivery can be deleted only while no delivery return was made against it. Its lines
     * (and their batch allocations), packing list, vehicles / boxes and delivery expense entries
     * go with it; the delivered quantity goes back into stock.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $delivery = Delivery::findOrFail($id);
        $impact = DeletionImpact::for('Delivery '.($delivery->delivery_no ?: '#'.$id));

        $impact->blockIf($this->refCount('delivery_returns', 'delivery_id', $id), 'delivery return', null, $this->refExamples('delivery_returns', 'delivery_id', $id, 'return_no'));

        $impact->cascadeIf($this->refCount('stock_items', 'stock_id', $delivery->stock_id), 'delivered line');
        $impact->cascadeIf($this->refCount('packing_lists', 'delivery_id', $id), 'packing list');
        $impact->cascadeIf($this->refCount('delivery_boxes', 'delivery_id', $id), 'vehicle / box entry', 'vehicle / box entries');
        $impact->cascadeIf($this->refCount('transactions', 'order_id', $id, fn ($q) => $q->where('transaction_type', 'deliveryExpense')), 'delivery expense entry', 'delivery expense entries');
        $impact->note('The delivered quantity goes back into stock (to the batches it came from) and leaves the customer ledger.');
        $impact->note('The order status is not changed; update it on the order if needed.');

        return $impact;
    }

    public function delete($id)
    {
        $delivery = Delivery::findOrFail($id);

        app(PackingListRepository::class)->deleteForDelivery($id);
        DB::table('delivery_boxes')->where('delivery_id', $id)->delete();
        DB::table('transactions')->where('transaction_type', 'deliveryExpense')->where('order_id', $id)->delete();
        $delivery->delete();

        return app(StockRepository::class)->purge([$delivery->stock_id]);
    }

    /**
     * Freight charges are never negative; blank means none.
     */
    private function normalizeFreight(array $data): array
    {
        if (array_key_exists('freight_charges', $data)) {
            $data['freight_charges'] = max(0, round((float) ($data['freight_charges'] ?: 0), 2));
        }

        return $data;
    }
}
