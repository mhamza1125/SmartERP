<?php

namespace App\Repositories;

use App\Models\IGroupItem;

class IGroupItemRepository implements GlobalInterface {

    public function all(){
        return IGroupItem::all();
    }

    public function get($id){
        return IGroupItem::where('igroup_items.igroup_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'igroup_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->leftJoin('materials', 'materials.material_id', '=', 'igroup_items.material_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
        ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'igroup_items.stage_id')
        ->select('igroup_items.*', 'products.*', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname')
        ->orderBy('product_id')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = IGroupItem::create($data);
        return $store->igroup_item_id;
    }

    public function update($id, array $data) {
        $existingItems = IGroupItem::where('igroup_id', $id)->get();
        // Assuming $data['product_type_id'] and $data['material_id'] are arrays of IDs.
        foreach ($existingItems as $existingItem) {
            // Check if combination does not exist in the provided data
            if (!in_array($existingItem->product_type_id, $data['product_type_id']) || !in_array($existingItem->material_id, $data['material_id']) || !in_array($existingItem->stage_id, $data['stage_id'])) {
                IGroupItem::where('igroup_item_id', $existingItem->igroup_item_id)->delete();
            }
        }
        // Assuming you have an array of product_type_ids and material_ids indexed similarly to quantities
        foreach ($data['quantity'] as $key => $quantity) {
            // Assuming you have these arrays in your $data and they are indexed accordingly
            $ptid = $data['product_type_id'][$key] ?? 0;
            $mid = $data['material_id'][$key] ?? 0;
            $sid = $data['stage_id'][$key] ?? 0;

            // Validate that both $ptid and $mid are not null
            if ($ptid !== null && $mid !== null && $sid !== null) {
                $stockItem = [
                    'igroup_id' => $id,
                    'product_type_id' => $ptid,
                    'material_id' => $mid,
                    'quantity' => $quantity,
                    'stage_id' => $sid,
                ];
                
                $stock = IGroupItem::where('igroup_id', $id)
                    ->where('product_type_id', $ptid)
                    ->where('material_id', $mid)
                    ->where('stage_id', $sid)
                    ->first();
                if ($stock) {
                    $stock->update($stockItem);
                } else {
                    if($quantity > 0){
                        $stockItem['created_by'] = auth()->id();
                        IGroupItem::create($stockItem);
                    }
                }
            }
        }
    }

    public function delete($id){}
}
