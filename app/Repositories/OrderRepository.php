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
        // Adding / Editing Purchases
        return Order::where('order_status', '<', '6')
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
        // Get non-completed/non-delivered orders for multi-order delivery
        return Order::where('orders.customer_id', $customerId)
            ->where('order_status', '<', 6) // Not completed
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
                    1 => 'Pending',
                    2 => 'Confirmed',
                    3 => 'In Production',
                    4 => 'Ready',
                    5 => 'Partial Delivery',
                    6 => 'Completed'
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
