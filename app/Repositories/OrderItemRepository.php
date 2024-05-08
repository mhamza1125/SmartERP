<?php

namespace App\Repositories;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemRepository implements GlobalInterface {
    
    public function all(){
        return OrderItem::all();
    }

    public function get($id){
        return OrderItem::where('order_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
        ->join('heads as shead', 'shead.head_id', '=', 'order_items.product_stage_id')
        ->select('order_items.*', 'product_types.*', 'products.name', 'products.article_no', 'heads.name as hname', 'uhead.name as uname', 'shead.name as sname')
        ->orderBy('product_types.product_id')
        ->orderBy('product_types.size_id')
        ->get();
    }

    public function estimate($id){
        return OrderItem::where('order_id', $id)
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

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = OrderItem::create($data);
        return $store->order_item_id;
    }

    public function update($id, array $data) {
        $existingItems = OrderItem::where('order_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem->product_type_id, $data['product_type_id']) || !in_array($existingItem->product_stage_id, $data['product_stage_id'])) {
                $existingItem->delete();
            }
        }
        foreach ($data['quantity'] as $key => $quantity) {
            $product = $data['product_type_id'][$key] ?? null;
            $stage = $data['product_stage_id'][$key] ?? null;
            $price = $data['price'][$key] ?? null;
            $total = $data['total'][$key] ?? null;
            $orderItem = [
                'order_id' => $id,
                'product_type_id' => $product,
                'product_stage_id' => $stage,
                'price' => $price,
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

    public function delete($id){}
}
