<?php

namespace App\Repositories;

use App\Models\StockItem;
use App\Models\ReceiveMaterial;
use Illuminate\Support\Facades\DB;

class StockItemRepository implements GlobalInterface {
    
    public function all(){
        return StockItem::all();
    }

    public function get($id){
        return StockItem::where('stock_items.stock_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->select('stock_items.*', 'products.*', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage')   
        ->get();
    }

    public function stock(){
        return DB::table(function ($subquery) {
            $subquery->select('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name as mtname', 'uhead.name as uname')
                ->selectRaw('SUM(receive_materials.quantity) as total_received')
                ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
                ->from('materials')
                ->leftJoin('purchase_items', 'purchase_items.material_id', '=', 'materials.material_id')
                ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
                ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
                ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
                ->where('receive_materials.inspection_status', '2')
                ->groupBy('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name', 'uhead.name');
        }, 'material_stock')
        ->leftJoin('stock_items', 'stock_items.material_id', '=', 'material_stock.material_id')
        ->leftJoin('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->select('material_stock.*')
        ->selectRaw('IFNULL(SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END), 0) as stockIn')
        ->selectRaw('IFNULL(SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END), 0) as stockOut')
        ->groupBy('material_stock.material_id', 'material_stock.material_no', 'material_stock.name', 'material_stock.mtname', 'material_stock.uname')
        ->get();
    }

    public function stockOld(){
        // Not Used
        return ReceiveMaterial::leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
        ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
        ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
        ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->select('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name as mtname', 'uhead.name as uname')
        ->selectRaw('SUM(receive_materials.quantity) as total_received')
        ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
        ->where('receive_materials.inspection_status', '2')
        ->groupBy('materials.material_id')
        ->orderBy('materials.name')
        ->get();
    }

    public function stockAll(){
        // Not Used
        return StockItem::select('stock_items.material_id')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->where('stock_items.material_id', '>', '0')
        ->groupBy('stock_items.material_id')
        ->get();
    }

    public function pStock(){
        // Product Stock
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->where('stock_items.material_id', '=', 0)
        ->groupBy('stock_items.product_type_id', 'stock_items.stage_id')
        ->get();
    }

    public function pStockGet($id){
        // Product Stock AjaxPM
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->where('stock_items.material_id', '=', 0)
        ->where('stock_items.product_type_id', '=', $id)
        ->groupBy('stock_items.product_type_id', 'stock_items.stage_id')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = StockItem::create($data);
        return $store->stock_item_id;
    }

    public function update($id, array $data) {
        $existingItems = StockItem::where('stock_id', $id)->get();
        // Assuming $data['product_type_id'] and $data['material_id'] are arrays of IDs.
        foreach ($existingItems as $existingItem) {
            // Check if combination does not exist in the provided data
            if (!in_array($existingItem->product_type_id, $data['product_type_id']) || !in_array($existingItem->material_id, $data['material_id'])) {
                StockItem::where('stock_id', $id)
                    ->where('product_type_id', $existingItem->product_type_id)
                    ->where('material_id', $existingItem->material_id)
                    ->delete();
            }
        }
        
        // Assuming you have an array of product_type_ids and material_ids indexed similarly to quantities
        foreach ($data['quantity'] as $key => $quantity) {
            // Assuming you have these arrays in your $data and they are indexed accordingly
            $ptid = $data['product_type_id'][$key+1] ?? null;
            $mid = $data['material_id'][$key] ?? null;
            $sid = $data['stage_id'][$key] ?? null;
            
            // Validate that both $ptid and $mid are not null
            if ($ptid !== null && $mid !== null && $sid !== null) {
                $stockItem = [
                    'stock_id' => $id,
                    'product_type_id' => $ptid,
                    'material_id' => $mid,
                    'quantity' => $quantity,
                    'stage_id' => $sid,
                ];
                
                $stock = StockItem::where('stock_id', $id)
                    ->where('product_type_id', $ptid)
                    ->where('material_id', $mid)
                    ->where('stage_id', $sid)
                    ->first();
                if ($stock) {
                    $stock->update($stockItem);
                } else {
                    $stockItem['created_by'] = auth()->id();
                    StockItem::create($stockItem);
                }
            }
        }
    }
    
    public function delete($id){}
}
