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

    /**
     * Removes a processing order's lines (used when its lines are rewritten): the purchase lines,
     * the mprocess rows and the stock documents that sent the raw material out (header + line;
     * each line has its own header, which used to be left behind empty).
     */
    public function delete($id)
    {
        $mprocess = MProcess::where('purchase_id', $id)->get();
        $stockIds = StockItem::whereIn('stock_item_id', $mprocess->pluck('stock_item_id'))->pluck('stock_id')->all();
        foreach ($mprocess as $item) {
            StockItem::where('stock_item_id', $item->stock_item_id)->delete();
        }
        // Only the processing order's own stock documents (table_name mprocess), now without lines
        $own = \Illuminate\Support\Facades\DB::table('stocks')->whereIn('stock_id', $stockIds)->where('table_name', 'mprocess')
            ->whereNotExists(fn ($q) => $q->from('stock_items')->whereColumn('stock_items.stock_id', 'stocks.stock_id'))
            ->pluck('stock_id')->all();
        app(StockRepository::class)->purge($own);
        PurchaseItem::where('purchase_id', $id)->delete();
        MProcess::where('purchase_id', $id)->delete();
    }

    /**
     * True when the processing order's lines would stay exactly as they are (same raw material and
     * quantity sent, same processed material, quantity and price per row, in order).
     */
    public function linesUnchanged(int $purchaseId, array $data): bool
    {
        $current = MProcess::where('mprocess.purchase_id', $purchaseId)
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'mprocess.purchase_item_id')
            ->orderBy('mprocess.mprocess_id')
            ->get(['mprocess.before_mid', 'mprocess.before_qty', 'purchase_items.material_id', 'purchase_items.quantity', 'purchase_items.price'])
            ->map(fn ($r) => [(int) $r->before_mid, round((float) $r->before_qty, 4), (int) $r->material_id, round((float) $r->quantity, 4), round((float) $r->price, 4)])
            ->all();
        $new = [];
        foreach ((array) ($data['price'] ?? []) as $key => $price) {
            $new[] = [(int) ($data['amaterial_id'][$key] ?? 0), round((float) ($data['aquantity'][$key] ?? 0), 4),
                (int) ($data['bmaterial_id'][$key] ?? 0), round((float) ($data['bquantity'][$key] ?? 0), 4), round((float) $price, 4)];
        }

        return $current === $new;
    }
}
