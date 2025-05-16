<?php

namespace App\Repositories;

use App\Models\MProcess;
use App\Models\PurchaseItem;
use App\Models\StockItem;

class MProcessRepository implements GlobalInterface
{
    public function all()
    {
        return PurchaseItem::all();
    }

    public function get($id)
    {
        return PurchaseItem::where('purchase_items.purchase_id', $id)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('mprocess', 'mprocess.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->join('materials as pmaterials', 'pmaterials.material_id', '=', 'mprocess.before_mid')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->join('heads as pheads', 'pheads.head_id', '=', 'pmaterials.unit_id')
            ->select('purchase_items.*', 'mprocess.*', 'materials.material_id', 'materials.name', 'materials.material_no', 'heads.name as hname', 'pmaterials.name as pname', 'pmaterials.material_no as pmaterial_no', 'pheads.name as phname', 'pmaterials.material_id as pmaterial_id')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = MProcess::create($data);

        return $store->purchase_item_id;
    }

    public function update($id, array $data)
    {
    }

    public function delete($id)
    {
        $mprocess = MProcess::where('purchase_id', $id)->get();
        foreach ($mprocess as $item) {
            StockItem::where('stock_item_id', $item->stock_item_id)->delete();
        }
        PurchaseItem::where('purchase_id', $id)->delete();
        MProcess::where('purchase_id', $id)->delete();
    }
}
