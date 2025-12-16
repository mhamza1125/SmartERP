<?php

namespace App\Repositories;

use App\Models\ReceiveMaterial;
use App\Models\ReturnMaterial;
use Illuminate\Support\Facades\DB;

class ReceiveMaterialRepository implements GlobalInterface
{
    public function all()
    {
        return ReceiveMaterial::all();
    }

    // This is for Material Purchase
    public function get($id)
    {
        return ReceiveMaterial::where('receive_materials.receive_id', $id)
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('material_no', 'materials.name', 'heads.name as uname', 'receive_materials.*')
            ->get();
    }

    // This is for Product Purchase
    public function get2($id)
    {
        return ReceiveMaterial::where('receive_materials.receive_id', $id)
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->select('products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname', 'receive_materials.*')
            ->get();
    }

    public function rSum($id)
    {
        // Subquery for received quantities
        $receivedSubquery = ReceiveMaterial::selectRaw('purchase_item_id, SUM(quantity) as total_received')
            ->groupBy('purchase_item_id');

        // Subquery for returned quantities
        $returnedSubquery = ReturnMaterial::selectRaw('receive_materials.purchase_item_id, SUM(return_materials.quantity) as total_returned')
            ->join('receive_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->groupBy('receive_materials.purchase_item_id');

        // Main query
        return DB::table('purchase_items')->where('purchase_items.purchase_id', $id)
            ->joinSub($receivedSubquery, 'received', function ($join) {
                $join->on('purchase_items.purchase_item_id', '=', 'received.purchase_item_id');
            })
            ->leftJoinSub($returnedSubquery, 'returned', function ($join) {
                $join->on('purchase_items.purchase_item_id', '=', 'returned.purchase_item_id');
            })
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->selectRaw('purchase_items.purchase_item_id, purchase_items.quantity, materials.material_no, materials.name, heads.name as hname, orders.job_no, COALESCE(received.total_received, 0) as rqty, COALESCE(returned.total_returned, 0) as rqty2')
            ->get();

        // Used by PurchaseInfo Recieving / Return Sum, Working Good but showing error when single receiving have multiple returns
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
            ->leftJoin('return_materials', function ($join) {
                $join->on('return_materials.receive_material_id', '=', 'receive_materials.receive_material_id');
            })
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->groupBy('receive_materials.purchase_item_id')
            ->selectRaw('purchase_items.purchase_item_id, purchase_items.quantity, materials.material_no, materials.name,
            heads.name as hname, orders.job_no, SUM(receive_materials.quantity) AS rqty, SUM(return_materials.quantity) AS rqty2')
            ->get();

        // Total Receive Old
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->groupBy('receive_materials.purchase_item_id')
            ->selectRaw('purchase_items.purchase_item_id, purchase_items.quantity, materials.material_no, materials.name,
            heads.name as hname, orders.job_no, SUM(receive_materials.quantity) AS rqty')
            ->get();
    }

    public function rSum2($id) // Used b Product Purchase
    {
        // Subquery for received quantities
        $receivedSubquery = ReceiveMaterial::selectRaw('purchase_item_id, SUM(quantity) as total_received')
            ->groupBy('purchase_item_id');

        // Subquery for returned quantities
        $returnedSubquery = ReturnMaterial::selectRaw('receive_materials.purchase_item_id, SUM(return_materials.quantity) as total_returned')
            ->join('receive_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->groupBy('receive_materials.purchase_item_id');

        // Main query
        return DB::table('purchase_items')->where('purchase_items.purchase_id', $id)
            ->joinSub($receivedSubquery, 'received', function ($join) {
                $join->on('purchase_items.purchase_item_id', '=', 'received.purchase_item_id');
            })
            ->leftJoinSub($returnedSubquery, 'returned', function ($join) {
                $join->on('purchase_items.purchase_item_id', '=', 'returned.purchase_item_id');
            })
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->selectRaw('purchase_items.purchase_item_id, purchase_items.quantity,products.name, products.article_no, heads.name as hname, shead.name as sname, orders.job_no, COALESCE(received.total_received, 0) as rqty, COALESCE(returned.total_returned, 0) as rqty2')
            ->get();
    }

    public function rSumProduct($id)
    {
        // Subquery for received quantities
        $receivedSubquery = ReceiveMaterial::selectRaw('purchase_item_id, SUM(quantity) as total_received')
            ->groupBy('purchase_item_id');

        // Subquery for returned quantities
        $returnedSubquery = ReturnMaterial::selectRaw('receive_materials.purchase_item_id, SUM(return_materials.quantity) as total_returned')
            ->join('receive_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->groupBy('receive_materials.purchase_item_id');

        // Main query
        return DB::table('purchase_items')->where('purchase_items.purchase_id', $id)
            ->joinSub($receivedSubquery, 'received', function ($join) {
                $join->on('purchase_items.purchase_item_id', '=', 'received.purchase_item_id');
            })
            ->leftJoinSub($returnedSubquery, 'returned', function ($join) {
                $join->on('purchase_items.purchase_item_id', '=', 'returned.purchase_item_id');
            })
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')

            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->selectRaw('purchase_items.purchase_item_id, purchase_items.quantity, products.name, products.article_no, heads.name as hname, shead.name as sname, orders.job_no, COALESCE(received.total_received, 0) as rqty, COALESCE(returned.total_returned, 0) as rqty2')
            ->get();
        }

    public function rAll($id)
    {
        // Used by PurchaseInfo
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
            ->join('receives', 'receives.receive_id', 'receive_materials.receive_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('receive_materials.*', 'purchase_items.quantity', 'materials.material_no', 'materials.name',
                'heads.name as hname', 'receive_materials.quantity as rqty', 'receive_materials.created_at',
                'receive_material_id', 'receives.receive_no', 'purchase_items.purchase_item_id')
            ->get();
    }

    public function rAll2($id) // For Product Purchase
    {
        // Used by PurchaseInfo
        return ReceiveMaterial::where('purchase_items.purchase_id', $id)
            ->join('receives', 'receives.receive_id', 'receive_materials.receive_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id') 
            ->select('receive_materials.*', 'purchase_items.quantity', 'receive_materials.quantity as rqty', 'receive_materials.created_at', 'receive_material_id', 'receives.receive_no', 'purchase_items.purchase_item_id', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname', 'receive_materials.*')
            ->get();
    }

    public function times($id)
    {
        // Used By Purchase
        return ReceiveMaterial::where('receives.purchase_id', $id)
            ->join('receives', 'receives.receive_id', '=', 'receive_materials.receive_id')
            ->groupBy('receives.receive_id')
            ->select('receives.receive_id', 'receives.receive_no', 'receives.receive_date')->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = ReceiveMaterial::create($data);

        return $store->receive_material_id;
    }

    public function update($id, array $data)
    {
        $existingItems = ReceiveMaterial::where('receive_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (! in_array($existingItem->purchase_item_id, $data['purchase_item_id'])) {
                $existingItem->delete();
            }
        }

        foreach ($data['quantity'] as $key => $quantity) {
            $pid = $data['purchase_item_id'][$key] ?? null;
            $idate = $data['inspection_date'][$key] ?? null;
            $pqty = $data['pending_qty'][$key] ?? null;
            $aqty = $data['approved_qty'][$key] ?? null;
            $rqty = $data['rejected_qty'][$key] ?? null;

            $receiveMaterial = [
                'receive_id' => $id,
                'purchase_item_id' => $pid,
                'quantity' => $quantity,
                'pending_qty' => $pqty,
                'approved_qty' => $aqty,
                'rejected_qty' => $rqty,
                'inspection_date' => $idate,
            ];
            $receive = ReceiveMaterial::where('receive_id', $id)
                ->where('purchase_item_id', $pid)
                ->first();
            if ($receive) {
                $receive->update($receiveMaterial);
            } else {
                $receiveMaterial['created_by'] = auth()->id();
                $store = ReceiveMaterial::create($receiveMaterial);
            }
        }
    }

    public function delete($id)
    {
    }
}
