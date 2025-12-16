<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository implements GlobalInterface
{
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
            ->select('orders.*', 'customers.*')
            ->first();
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
        return Order::where('orders.customer_id', $customerId)
            ->where('order_status', '=', 2) // Only Confirmed orders
            ->whereNotExists(function ($query) {
                $query->select('deliveries.delivery_id')
                      ->from('deliveries')
                      ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
                      ->whereColumn('stocks.order_id', 'orders.order_id')
                      ->where('deliveries.delivery_status', '>=', 2); // Not delivered
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
        $store = Order::create($data);

        return $store->order_id;
    }

    public function update($id, array $data)
    {
        $update = Order::findOrFail($id);
        $update->update($data);

        return $update->order_id;
    }

    public function delete($id)
    {
    }
}
