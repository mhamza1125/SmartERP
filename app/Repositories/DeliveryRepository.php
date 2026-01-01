<?php

namespace App\Repositories;

use App\Models\Delivery;

class DeliveryRepository implements GlobalInterface
{
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
            if (!$hasMultiOrderPattern) {
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

        $store = Delivery::create($data);

        return $store->delivery_id;
    }

    public function update($id, array $data)
    {
        $update = Delivery::findOrFail($id);
        $update->update($data);

        return $update->delivery_id;
    }

    public function delete($id)
    {
    }
}
