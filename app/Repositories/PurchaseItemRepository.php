<?php

namespace App\Repositories;

use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseItemRepository implements GlobalInterface
{
    public function all()
    {
        return PurchaseItem::all();
    }

    // This is for Material Purchase
    public function get($id)
    {
        return PurchaseItem::where('purchase_id', $id)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('purchase_items.*', 'materials.name', 'materials.material_no', 'heads.name as uname')
            ->get();
    }

    // This is for Product Purchase
    public function get2($id)
    {
        return PurchaseItem::where('purchase_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->select('purchase_items.*', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname')
            ->orderBy('product_types.product_id')
            ->orderBy('product_types.size_id')
            ->get();
    }

    public function receive($id)
    {
        // Receive Purchase Items
        return PurchaseItem::where('purchase_id', $id)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->leftJoin('return_materials', 'return_materials.receive_material_id', 'receive_materials.receive_material_id')
            ->select('purchase_items.*', 'materials.name', 'materials.material_no')
            ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received, COALESCE(SUM(return_materials.quantity), 0) as returned')
            ->groupBy('purchase_items.purchase_item_id')
            ->get();

        // Receive Purchase Items Without Return Calculation
        return PurchaseItem::where('purchase_id', $id)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->select('purchase_items.*', 'materials.name', 'materials.material_no')
            ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received')
            ->groupBy('purchase_items.purchase_item_id')
            ->get();
    }

    public function receiveProducts($id)
    {
        // Receive Purchase Items
        return PurchaseItem::where('purchase_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->leftJoin('return_materials', 'return_materials.receive_material_id', 'receive_materials.receive_material_id')
            ->select('purchase_items.*', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname')
            ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received, COALESCE(SUM(return_materials.quantity), 0) as returned')
            ->groupBy('purchase_items.purchase_item_id')
            ->get();
    }

    public function estimate($id)
    {
        // Material Purchase Against Order
        return PurchaseItem::where('purchases.order_id', $id)
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('*', 'materials.material_id', 'heads.name as hname', 'materials.name', 'vendors.fname', 'vendor_no')
            ->selectRaw('CEIL(SUM(CEIL(purchase_items.quantity))) as total_qty')
            ->orderBy('materials.vendor_id')->orderBy('materials.material_id')
            ->groupBy('materials.material_id')
            ->get();
    }

    public function estimateMaterial($orderId, $materialId)
    {
        // Material Purchase Against Order, Purchase Order
        $result = DB::table('materials')
            ->select(
                DB::raw('GREATEST(0, COALESCE(total.total_qty, 0) - COALESCE(purchase.pqty, 0)) AS balance')
            )
            ->leftJoin(DB::raw("
                (
                    SELECT
                        product_materials.material_id,
                        CEIL(SUM(CEIL(order_items.quantity * product_materials.quantity))) AS total_qty
                    FROM order_items
                    JOIN product_materials ON product_materials.product_type_id = order_items.product_type_id
                    WHERE order_items.order_id = $orderId AND product_materials.material_id = $materialId
                    GROUP BY product_materials.material_id
                ) AS total"), 'materials.material_id', '=', 'total.material_id')
            ->leftJoin(DB::raw("
                (
                    SELECT
                        purchase_items.material_id,
                        CEIL(SUM(CEIL(purchase_items.quantity))) AS pqty
                    FROM purchase_items
                    JOIN purchases ON purchases.purchase_id = purchase_items.purchase_id
                    WHERE purchases.order_id = $orderId AND purchase_items.material_id = $materialId
                    GROUP BY purchase_items.material_id
                ) AS purchase"), 'materials.material_id', '=', 'purchase.material_id')
            ->where('materials.material_id', $materialId)
            ->first();

        return $result ? $result->balance : 0;
    }

    // This is for Material Purchase
    public function editReceive($pid, $rid)
    {
        return PurchaseItem::where('purchase_id', $pid)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->leftJoin('receive_materials', function ($join) use ($rid) {
                $join->on('receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                    ->where('receive_materials.receive_id', '!=', $rid);
            })
            ->select('purchase_items.*', 'materials.name', 'materials.material_no')
            ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received')
            ->groupBy('purchase_items.purchase_item_id')
            ->get();
    }
    
    // This is for Product Purchase
    public function editReceive2($pid, $rid)
    {
        return PurchaseItem::where('purchase_id', $pid)
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->leftJoin('receive_materials', function ($join) use ($rid) {
                $join->on('receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                    ->where('receive_materials.receive_id', '!=', $rid);
            })
            ->select('purchase_items.*', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname')
            ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received')
            ->groupBy('purchase_items.purchase_item_id')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = PurchaseItem::create($data);

        return $store->purchase_item_id;
    }

    public function update($id, array $data)
    {
        $existingItems = PurchaseItem::where('purchase_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (!in_array([$existingItem->material_id, $existingItem->product_type_id, $existingItem->product_stage_id], array_map(null, $data['material_id'], $data['product_type_id'], $data['product_stage_id']))) {
                $existingItem->delete();
            }
        }
        foreach ($data['quantity'] as $key => $quantity) {
            $price = $data['price'][$key] ?? null;
            $material = $data['material_id'][$key] ?? null;
            $product = $data['product_type_id'][$key] ?? null;
            $stage = $data['product_stage_id'][$key] ?? null;
            $total = $price * $quantity;
            $purchaseItem = [
                'purchase_id' => $id,
                'product_type_id' => $product,
                'product_stage_id' => $stage,
                'material_id' => $material,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $purchase = PurchaseItem::where('purchase_id', $id)
                ->where('material_id', $material)
                ->where('product_type_id', $product)
                ->where('product_stage_id', $stage)
                ->first();
            if ($purchase) {
                $purchase->update($purchaseItem);
            } else {
                $purchaseItem['created_by'] = auth()->id();
                $store = PurchaseItem::create($purchaseItem);
            }
            if (!$stage) DB::table('materials')->where('material_id', $material)->update(['cprice' => $price]);
        }
    }

    public function delete($id)
    {
    }
}
