<?php

namespace App\Repositories;

use App\Models\ReturnMaterial;

class ReturnMaterialRepository implements GlobalInterface
{
    public function all()
    {
        return ReturnMaterial::all();
    }

    public function get($id)
    {
        return ReturnMaterial::where('returns.return_id', $id)
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('materials.*', 'heads.name as hname', 'return_materials.*')
            ->get();
    }

    public function get2($id) // For Product Return
    {
        return ReturnMaterial::where('returns.return_id', $id)
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->select('heads.name as hname', 'return_materials.*', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname')
            ->get();
    }

    public function rAll($id)
    {
        // Used by PurchaseInfo
        return ReturnMaterial::where('purchase_items.purchase_id', $id)
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->join('receive_materials', function ($join) {
                $join->on('receive_materials.receive_material_id', '=', 'return_materials.receive_material_id');
            })
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('purchase_items.quantity', 'materials.material_no', 'materials.name',
                'heads.name as hname', 'return_materials.quantity as rqty', 'return_materials.created_at',
                'return_material_id', 'returns.return_no', 'purchase_items.purchase_item_id', 'return_materials.remarks')
            ->get();
    }

    public function rAll2($id)
    {
        // Used by PurchaseInfo for Product Purchase
        return ReturnMaterial::where('purchase_items.purchase_id', $id)
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->join('receive_materials', function ($join) {
                $join->on('receive_materials.receive_material_id', '=', 'return_materials.receive_material_id');
            })
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->select('purchase_items.quantity', 'return_materials.quantity as rqty', 'return_materials.created_at', 'return_material_id', 'returns.return_no', 'purchase_items.purchase_item_id',  'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname', 'receive_materials.*', 'return_materials.remarks')
            ->get();
    }

    public function times($id)
    {
        // Used By Purchase
        return ReturnMaterial::where('receives.purchase_id', $id)
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->join('receives', 'receives.receive_id', '=', 'returns.receive_id')
            ->groupBy('returns.return_id')
            ->select('returns.return_id', 'return_no')->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = ReturnMaterial::create($data);

        return $store->return_material_id;
    }

    public function update($id, array $data)
    {
        $existingItems = ReturnMaterial::where('return_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (! in_array($existingItem->receive_material_id, $data['receive_material_id'])) {
                $existingItem->delete();
            }
        }
        foreach ($data['quantity'] as $key => $quantity) {
            $rid = $data['receive_material_id'][$key] ?? null;
            $remarks = $data['remarks'][$key] ?? null;

            $returnMaterial = [
                'return_id' => $id,
                'receive_material_id' => $rid,
                'quantity' => $quantity,
                'remarks' => $remarks,
            ];
            $return = ReturnMaterial::where('return_id', $id)
                ->where('receive_material_id', $rid)
                ->first();
            if ($return) {
                $return->update($returnMaterial);
            } else {
                $returnMaterial['created_by'] = auth()->id();
                $store = returnMaterial::create($returnMaterial);
            }
        }
    }

    public function delete($id)
    {
    }
}
