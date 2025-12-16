<?php

namespace App\Repositories;

use App\Models\PackingList;
use App\Models\PackingCarton;
use App\Models\PackingCartonItem;
use Illuminate\Support\Facades\DB;

class PackingListRepository implements GlobalInterface
{
    public function all()
    {
        return PackingList::join('deliveries', 'deliveries.delivery_id', '=', 'packing_lists.delivery_id')
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'packing_lists.order_id')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select('packing_lists.*', 'stocks.stock_no', 'orders.order_no', 'customers.fname', 'customers.lname')
            ->orderBy('packing_lists.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        return PackingList::where('packing_lists.packing_list_id', $id)
            ->join('deliveries', 'deliveries.delivery_id', '=', 'packing_lists.delivery_id')
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'packing_lists.order_id')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select('packing_lists.*', 'stocks.stock_no', 'stocks.stock_date', 'orders.order_no', 'orders.job_no', 'customers.fname', 'customers.lname', 'customers.customer_no')
            ->first();
    }

    public function getByDelivery($deliveryId)
    {
        return PackingList::where('delivery_id', $deliveryId)->first();
    }

    public function getByOrder($orderId)
    {
        return PackingList::where('packing_lists.order_id', $orderId)
            ->join('deliveries', 'deliveries.delivery_id', '=', 'packing_lists.delivery_id')
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'packing_lists.order_id')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->select('packing_lists.*', 'stocks.stock_no', 'stocks.stock_date', 'orders.order_no', 'orders.job_no', 'customers.fname', 'customers.lname', 'customers.customer_no')
            ->first();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = PackingList::create($data);

        return $store->packing_list_id;
    }

    public function update($id, array $data)
    {
        $update = PackingList::findOrFail($id);
        $update->update($data);
    }

    public function delete($id)
    {
        PackingList::findOrFail($id)->delete();
    }

    public function getCartons($packingListId)
    {
        return PackingCarton::where('packing_list_id', $packingListId)
            ->orderBy('carton_from')
            ->get();
    }

    public function getCartonItems($packingCartonId)
    {
        return PackingCartonItem::where('packing_carton_items.packing_carton_id', $packingCartonId)
            ->join('products', 'products.product_id', '=', 'packing_carton_items.product_id')
            ->select('packing_carton_items.*', 'products.name', 'products.article_no')
            ->get();
    }

    public function storeCarton(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = PackingCarton::create($data);

        return $store->packing_carton_id;
    }

    public function storeCartonItem(array $data)
    {
        $data['created_by'] = auth()->id();
        PackingCartonItem::create($data);
    }

    public function deleteCartons($packingListId)
    {
        PackingCarton::where('packing_list_id', $packingListId)->delete();
    }
}

