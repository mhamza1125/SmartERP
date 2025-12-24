<?php

namespace App\Repositories;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemRepository implements GlobalInterface
{
    public function all()
    {
        return OrderItem::all();
    }

    public function get($id)
    {
        return OrderItem::where('order_items.order_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->join('heads as shead', 'shead.head_id', '=', 'order_items.product_stage_id')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('heads as chead', 'chead.head_id', '=', 'customers.currency_id')
            ->leftJoin('product_materials', function($join) {
                $join->on('product_materials.product_type_id', '=', 'order_items.product_type_id')
                     ->whereIn('product_materials.material_id', function($query) {
                         // Get packing box material IDs (material_type_id = 61)
                         $query->select('material_id')
                               ->from('materials')
                               ->where('material_type_id', 61);
                     });
            })
            ->select(
                'order_items.*',
                'product_types.*',
                'products.name as pname',
                'products.article_no',
                'products.hs_code',
                'heads.name as name',
                'uhead.name as uname',
                'shead.name as sname',
                'chead.name as cname',
                'product_materials.quantity as bqty'
            )
            ->selectRaw('CEIL(order_items.quantity * COALESCE(product_materials.quantity, 0)) as box_quantity')
            ->orderBy('product_types.product_id')
            ->orderBy('product_types.size_id')
            ->get();
    }

    public function estimate($id) // Requred Material Against Order
    {return OrderItem::where('order_id', $id)
            ->join('product_materials', 'product_materials.product_type_id', '=', 'order_items.product_type_id')
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('*', 'materials.material_id', 'heads.name as hname', 'materials.name', 'vendors.fname', 'vendor_no')
            ->selectRaw('CEIL(SUM(CEIL(order_items.quantity * product_materials.quantity))) as total_qty')
            ->groupBy('materials.material_id')
            ->orderBy('materials.vendor_id')
            ->orderBy('materials.material_id')
            ->get();

        // Separate Material Required for Each Order Item
        return OrderItem::where('order_id', $id)
            ->join('product_materials', 'product_materials.product_type_id', '=', 'order_items.product_type_id')
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('*', 'heads.name as hname', 'materials.name', 'product_materials.material_id', 'vendors.fname', 'vendor_no')
            ->selectRaw('CEIL(SUM(CEIL(order_items.quantity * product_materials.quantity))) as total_qty')
            ->groupBy('order_items.order_item_id')
            ->groupBy('product_materials.material_id')
            ->orderBy('materials.vendor_id')
            ->orderBy('materials.material_id')
            ->get();

        // Old Working Queery (False Record for Boxes)
        return OrderItem::where('order_id', $id)
            ->join('product_materials', 'product_materials.product_type_id', '=', 'order_items.product_type_id')
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('*', 'heads.name as hname', 'materials.name', 'product_materials.material_id', DB::raw('SUM(order_items.quantity * product_materials.quantity) as total_qty'), 'vendors.fname', 'vendor_no')
            ->groupBy('product_materials.material_id')
            ->orderBy('materials.vendor_id')
            ->orderBy('materials.material_id')->get();
    }

    public function estimateMaterial($orderId, $materialId)
    {
        // Required Material Against Complete Order, Issuance
        $totalQty = OrderItem::where('order_id', $orderId)
            ->join('product_materials', 'product_materials.product_type_id', '=', 'order_items.product_type_id')
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->selectRaw('CEIL(SUM(CEIL(order_items.quantity * product_materials.quantity))) as total_qty')
            ->groupBy('materials.material_id')
            ->where('materials.material_id', $materialId)
            ->orderBy('materials.vendor_id')
            ->orderBy('materials.material_id')
            ->first();

        // Already Issued Material
        $issuedQty = \DB::table('stock_items')->where('stocks.order_id', $orderId)
            ->join('stocks', 'stocks.stock_id', 'stock_items.stock_id')
            ->join('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->select(DB::raw('SUM(stock_items.quantity) as issued_qty'))
            ->where('stock_items.material_id', $materialId)
            ->groupBy('stock_items.material_id')
            ->first();

        $totalQty = $totalQty->total_qty ?? '0';
        $issuedQty = $issuedQty->issued_qty ?? '0';

        $return = $totalQty.'  |  '.$issuedQty.'  |  '.$totalQty - $issuedQty;

        return $return;
    }

    public function estimateAMaterial($orderId, $productId, $materialId)
    {
        // Required Material Against Order's Article, Issuance
        $pid = OrderItem::where('order_id', $orderId)
            ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
            ->where('order_items.product_type_id', $productId)
            ->select('product_types.product_id')->first();
        $pid = $pid['product_id'];

        $totalQty = OrderItem::where('order_id', $orderId)
            ->join('product_materials', 'product_materials.product_type_id', '=', 'order_items.product_type_id')
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->selectRaw('CEIL(SUM(CEIL(order_items.quantity * product_materials.quantity))) as total_qty')
            ->groupBy('materials.material_id')
            ->where('materials.material_id', $materialId)
            ->where('product_types.product_id', $pid)
        // ->where('order_items.product_type_id', $productId)
            ->orderBy('materials.vendor_id')
            ->orderBy('materials.material_id')
            ->first();

        // Already Issued Material
        $issuedQty = \DB::table('stock_items')->where('stocks.order_id', $orderId)
            ->join('stocks', 'stocks.stock_id', 'stock_items.stock_id')
            ->join('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->select(DB::raw('SUM(stock_items.quantity) as issued_qty'))
            ->where('stock_items.material_id', $materialId)
        // ->where('stock_items.product_type_id', $productId)
            ->where('product_types.product_id', $pid)
            ->groupBy('stock_items.material_id')
            ->first();

        $totalQty = $totalQty->total_qty ?? '0';
        $issuedQty = $issuedQty->issued_qty ?? '0';

        $return = $totalQty.'  |  '.$issuedQty.'  |  '.$totalQty - $issuedQty;

        return $return;
    }

    public function estimateATMaterial($orderId, $productId, $materialId)
    {
        // Required Material Against Order's Article Type, Issuance
        $totalQty = OrderItem::where('order_id', $orderId)
            ->join('product_materials', 'product_materials.product_type_id', '=', 'order_items.product_type_id')
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->selectRaw('CEIL(SUM(CEIL(order_items.quantity * product_materials.quantity))) as total_qty')
            ->groupBy('materials.material_id')
            ->where('materials.material_id', $materialId)
            ->where('order_items.product_type_id', $productId)
            ->orderBy('materials.vendor_id')
            ->orderBy('materials.material_id')
            ->first();

        // Already Issued Material
        $issuedQty = \DB::table('stock_items')->where('stocks.order_id', $orderId)
            ->join('stocks', 'stocks.stock_id', 'stock_items.stock_id')
            ->join('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->select(DB::raw('SUM(stock_items.quantity) as issued_qty'))
            ->where('stock_items.material_id', $materialId)
            ->where('stock_items.product_type_id', $productId)
            ->groupBy('stock_items.material_id')
            ->first();

        $totalQty = $totalQty->total_qty ?? '0';
        $issuedQty = $issuedQty->issued_qty ?? '0';

        $return = $totalQty.'  |  '.$issuedQty.'  |  '.$totalQty - $issuedQty;

        return $return;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = OrderItem::create($data);

        return $store->order_item_id;
    }

    public function update($id, array $data)
    {
        $existingItems = OrderItem::where('order_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (! in_array($existingItem->product_type_id, $data['product_type_id']) || ! in_array($existingItem->product_stage_id, $data['product_stage_id'])) {
                $existingItem->delete();
            }
        }
        foreach ($data['quantity'] as $key => $quantity) {
            $product = $data['product_type_id'][$key] ?? null;
            $stage = $data['product_stage_id'][$key] ?? null;
            $price = $data['price'][$key] ?? null;
            $price2 = $data['price2'][$key] ?? null;
            $total = $data['total'][$key] ?? null;
            $orderItem = [
                'order_id' => $id,
                'product_type_id' => $product,
                'product_stage_id' => $stage,
                'price' => $price,
                'price2' => $price2,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $order = OrderItem::where('order_id', $id)
                ->where('product_type_id', $product)
                ->where('product_stage_id', $stage)
                ->first();
            if ($order) {
                $order->update($orderItem);
            } else {
                $orderItem['created_by'] = auth()->id();
                $store = OrderItem::create($orderItem);
            }
        }
    }

    public function delete($id)
    {
    }
}
