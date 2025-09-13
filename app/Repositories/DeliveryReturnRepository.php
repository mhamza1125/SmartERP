<?php

namespace App\Repositories;

use App\Models\DeliveryReturn;

class DeliveryReturnRepository implements GlobalInterface
{
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

    public function delete($id)
    {
        $delete = DeliveryReturn::findOrFail($id);
        $delete->delete();
    }
}
