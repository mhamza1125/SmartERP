<?php

namespace App\Repositories;

use App\Models\StockItem;
use App\Models\ReceiveMaterial;
use Illuminate\Support\Facades\DB;
use App\Repositories\ProductCostRepository;

class StockItemRepository implements GlobalInterface {
    protected $productCostRepository;

    public function __construct(ProductCostRepository $productCostRepository)
    {
        $this->productCostRepository = $productCostRepository;
    }
    
    public function all(){
        return StockItem::all();
    }

    public function get($id){
        return StockItem::where('stock_items.stock_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
        ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->select('stock_items.*', 'products.*', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname')
        ->orderBy('product_id')
        ->get();
    }

    public function getAvg($id){
        // Showing Product Average Along Matrials in Receive Issuance
        return StockItem::where('stock_items.stock_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->leftJoin('product_materials', function($join) {
            $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                 ->on('product_materials.material_id', '=', 'stock_items.material_id');
        })
        ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
        ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->select('stock_items.*', 'products.*', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'product_materials.quantity as pqty')   
        ->orderBy('product_id')
        ->get();
    }

    public function getAll($id){
        // Used by StockInfo
        return StockItem::where('stocks.issue_id', $id)
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
        ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->select('stock_items.*', 'products.*', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'stocks.stock_no')   
        ->get();
    }

    public function getSum($id){
        // Used By StockInfo
        return  StockItem::where('stocks.issue_id', $id)
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
        ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->groupBy('stock_items.product_type_id', 'stock_items.material_id', 'stock_items.stage_id')
        ->selectRaw('stock_items.*, products.*, materials.*, stock_items.product_type_id,
            stock_items.material_id, stock_items.stage_id, SUM(stock_items.quantity) as total_quantity, products.name as pname, uhead.name as uname, shead.name as sname, sthead.name as stage, puhead.name as puname, stocks.stock_no')
        ->get();
    }

    public function times($id){
        // Used By StockInfo
        return StockItem::where('stocks.issue_id', $id)
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->groupBy('stocks.stock_id')
        ->select('stock_no')->get();
    }

    public function workLog($id){
        // Used By Edit Receive Issuance
        return StockItem::where('stock_items.stock_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('product_costs', 'product_costs.product_id', '=', 'product_types.product_id')
        ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
        ->select('product_costs.*','heads.name as hname')   
        ->groupBy('heads.head_id')
        ->get();

        return StockItem::where('stock_items.stock_id', $id)
        ->join('product_costs', 'product_costs.product_type_id', '=', 'stock_items.product_type_id')
        ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
        ->select('product_costs.*','heads.name as hname')   
        ->groupBy('heads.head_id')
        ->get();
    }

    public function stock(){
        // Available Material Stock
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

    public function pStock(){
        // Available Product Stock
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id', 'uhead.name as uname')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->where('stock_items.material_id', '=', 0)
        ->groupBy('stock_items.product_type_id', 'stock_items.stage_id')
        ->get();
    }

    public function orderStatus($id){
        // Order Current Status
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id', 'uhead.name as uname')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('order_items', 'order_items.product_type_id', '=', 'product_types.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->where('order_items.order_id', $id)
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
            if (!in_array($existingItem->product_type_id, $data['product_type_id']) || !in_array($existingItem->material_id, $data['material_id']) || !in_array($existingItem->stage_id, $data['stage_id'])) {
                StockItem::where('stock_item_id', $existingItem->stock_item_id)->delete();
            }
        }
        $tid = $data['employee_id'];
        $tname = $data['table_name'];
        // Assuming you have an array of product_type_ids and material_ids indexed similarly to quantities
        foreach ($data['quantity'] as $key => $quantity) {
            // Assuming you have these arrays in your $data and they are indexed accordingly
            $ptid = $data['product_type_id'][$key] ?? null;
            $mid = $data['material_id'][$key] ?? null;
            $sid = $data['stage_id'][$key] ?? null;
            $work = $data['work_logs'][$key] ?? 0;
            $wages = $work ? $this->productCostRepository->wages($ptid, $work, $tid, $tname) : '0';

            // Validate that both $ptid and $mid are not null
            if ($ptid !== null && $mid !== null && $sid !== null) {
                $stockItem = [
                    'stock_id' => $id,
                    'product_type_id' => $ptid,
                    'material_id' => $mid,
                    'quantity' => $quantity,
                    'stage_id' => $sid,
                    'work_logs' => $work,
                    'work_wages' => $wages,
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
