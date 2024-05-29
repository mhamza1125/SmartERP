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

    public function delivery($id){
        // Get Delivery Items
        return StockItem::where('deliveries.delivery_id', $id)
        ->join('deliveries', 'deliveries.stock_id', 'stock_items.stock_id')
        ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
        ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
        ->leftjoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
        ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
        ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->select('*', 'product_types.*', 'products.name', 'products.article_no', 'shead.name as hname', 'uhead.name as uname', 'sthead.name as sname', 'puhead.name as puname', 'materials.name as mname')
        ->orderBy('products.product_id')
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

    public function getMM($id){
        // Machine Material Issuance
        return StockItem::where('stock_items.stock_id', $id)
        ->join('materials', 'materials.material_id', '=', 'stock_items.material_id')
        ->leftJoin('heads', 'heads.head_id', '=', 'materials.unit_id')
        ->select('stock_items.*', 'materials.*', 'heads.name as hname')   
        ->get();
    }

    public function dailyIssue(){
        $date = date('Y-m-d');
        return StockItem::whereDate('stocks.stock_date', $date)
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
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
        ->leftJoin('heads as ifhead', 'ifhead.head_id', '=', 'stocks.issue_for')
        ->select('stock_items.*', 'products.*', 'products.product_id', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'product_materials.quantity as pqty', 'ifhead.name as ifname')
        ->where('stocks.stock_type', '2')
        ->orderBy('product_id')
        ->orderBy('product_types.size_id')
        ->get();
    }

    public function dailyIssueFilter($dfrom, $dto, $tname, $tid, $oid){
        // Just Filter By Date When both $oid, $tid are Null
        $query = StockItem::whereBetween('stocks.stock_date', [$dfrom, $dto])
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
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
        ->leftJoin('heads as ifhead', 'ifhead.head_id', '=', 'stocks.issue_for')
        ->select('stock_items.*', 'products.*', 'products.product_id', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'product_materials.quantity as pqty', 'ifhead.name as ifname')
        ->where('stocks.stock_type', '2')
        ->orderBy('product_id')
        ->orderBy('product_types.size_id');
        // ->get();

        if($tid > 0 && $oid > 0) {
            $query->where('stocks.order_id', $oid);
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        }elseif($tid > 0) {
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        }elseif($oid > 0) {
            $query->where('stocks.order_id', $oid);
        }
        return $query->get();
    }

    public function dailyReceive(){
        // Daily Receive Issuance
        $date = date('Y-m-d');
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id', 'uhead.name as uname', 'products.product_id')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->where('stock_items.material_id', '=', 0)
        ->whereDate('stocks.stock_date', $date)
        ->orderBy('product_types.product_id')
        ->orderBy('product_types.size_id')
        ->orderBy('stock_items.stage_id')
        ->where('stocks.stock_type', '1')
        ->groupBy('stock_items.product_type_id', 'stock_items.stage_id')
        ->get();
    }

    public function dailyReceiveFilter($dfrom, $dto, $tname, $tid, $oid){
        // Filtered Receive Issuance
        $query = StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id', 'uhead.name as uname', 'products.product_id')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->where('stock_items.material_id', '=', 0)
        ->whereBetween('stocks.stock_date', [$dfrom, $dto])
        ->orderBy('product_types.product_id')
        ->orderBy('product_types.size_id')
        ->orderBy('stock_items.stage_id')
        ->where('stocks.stock_type', '1')
        ->groupBy('stock_items.product_type_id', 'stock_items.stage_id');
        // ->get();

        if($tid > 0 && $oid > 0) {
            $query->where('stocks.order_id', $oid);
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        }elseif($tid > 0) {
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        }elseif($oid > 0) {
            $query->where('stocks.order_id', $oid);
        }
        return $query->get();
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
        ->select('stocks.stock_id', 'stock_no')->get();
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

    public function stockVehicle($id){
        // Available Material Stock
        return DB::table(function ($subquery) use ($id) {
            $subquery->select('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name as mtname', 'uhead.name as uname')
                // ->selectRaw('SUM(receive_materials.quantity) as total_received')
                ->selectRaw('SUM(receive_materials.approved_qty) as total_received')
                ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
                ->where('materials.material_type_id', '=', '96')
                ->from('materials')
                ->leftJoin('purchase_items', 'purchase_items.material_id', '=', 'materials.material_id')
                ->leftJoin('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
                ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
                ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
                ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
                ->where('purchases.order_id', $id)
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
    
    public function stock(){
        // Available Material Stock
        return DB::table(function ($subquery) {
            $subquery->select('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name as mtname', 'uhead.name as uname', 'material_type_id')
                // ->selectRaw('SUM(receive_materials.quantity) as total_received')
                ->selectRaw('SUM(receive_materials.approved_qty) as total_received')
                ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
                ->from('materials')
                ->leftJoin('purchase_items', 'purchase_items.material_id', '=', 'materials.material_id')
                ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
                ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
                ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
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
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'sthead.head_id as sthead_id', 'stock_items.stage_id', 'uhead.name as uname', 'products.product_id')
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

    public function gStock() {
        // Fetch igroup_items for the given order
        $igroupItems = DB::table('igroup_items')
            ->join('igroups', 'igroups.igroup_id', '=', 'igroup_items.igroup_id')
            ->select('igroup_items.*', 'igroup_items.stage_id as igstage_id')
            ->get();
    
        // Fetch available material stock
        $materialStock = DB::table(function ($subquery) {
            $subquery->select('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name as mtname', 'uhead.name as uname', 'material_type_id')
                ->selectRaw('SUM(receive_materials.approved_qty) as total_received')
                ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
                ->from('materials')
                ->leftJoin('purchase_items', 'purchase_items.material_id', '=', 'materials.material_id')
                ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
                ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
                ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
                ->groupBy('materials.material_id', 'materials.material_no', 'materials.name', 'mthead.name', 'uhead.name');
        }, 'material_stock')
        ->leftJoin('stock_items', 'stock_items.material_id', '=', 'material_stock.material_id')
        ->leftJoin('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->select('material_stock.*')
        ->selectRaw('IFNULL(SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END), 0) as stockIn')
        ->selectRaw('IFNULL(SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END), 0) as stockOut')
        ->groupBy('material_stock.material_id', 'material_stock.material_no', 'material_stock.name', 'material_stock.mtname', 'material_stock.uname')
        ->get()
        ->keyBy('material_id');
    
        // Fetch available product stock with stage_id check
        $productStock = StockItem::select('stock_items.product_type_id', 'stock_items.stage_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.head_id as sthead_id', 'sthead.name as stname', 'uhead.name as uname', 'products.product_id')
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
            ->get()
            ->keyBy(function($item) {
                return $item->product_type_id . '_' . $item->stage_id;
            });
    
        $results = [];
    
        // Calculate max issuable groups based on available stock
        foreach ($igroupItems as $item) {
            $materialId = $item->material_id;
            $productTypeId = $item->product_type_id;
            $quantity = $item->quantity;
            $stageId = $item->igstage_id; // Assuming stage_id is a column in igroup_items
    
            if ($materialId > 0) {
                // Check material stock
                $stockIn = max(0, $materialStock[$materialId]->stockIn);
                $stockOut = max(0, $materialStock[$materialId]->stockOut);
                $stockReceived = max(0, $materialStock[$materialId]->total_received);
                $stockReturned = max(0, $materialStock[$materialId]->total_returned);
                $availableStock = $stockIn - $stockOut + $stockReceived - $stockReturned;
                $maxIssuable = $availableStock > 0 ? intval($availableStock / $quantity) : 0;
            } else {
                // Check product stock with stage_id
                $stockIn = 0; 
                $stockOut = 0;
                foreach ($productStock as $stockItem) {
                    if ($stockItem->product_type_id == $productTypeId && $stockItem->sthead_id == $stageId) {
                        $stockIn += max(0, $stockItem->stockIn);
                        $stockOut += max(0, $stockItem->stockOut);
                    }
                }
                $availableStock = $stockIn - $stockOut;
                $maxIssuable = $availableStock > 0 ? intval($availableStock / $quantity) : 0;
            }
    
            if (isset($results[$item->igroup_id])) {
                // Choose min max_issuable or any other logic
                $results[$item->igroup_id]['max_issuable'] = min($results[$item->igroup_id]['max_issuable'], $maxIssuable);
            } else {
                $results[$item->igroup_id] = [
                    'igroup_id' => $item->igroup_id,
                    'material_id' => $materialId,
                    'product_type_id' => $productTypeId,
                    'stage_id' => $stageId,
                    'max_issuable' => $maxIssuable,
                ];
            }
        }
    
        return $results;
    }
    

    public function rstock($id){
        // Receiveable Stock Based on Early Receiving & Null
        return StockItem::join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('stocks as rstocks', 'rstocks.issue_id', '=', 'stocks.stock_id')
            ->leftJoin('product_materials', function($join) {
                $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                    ->where('stock_items.material_id', '=', 0);
            })
            ->where('stocks.issue_id', $id)
            ->select(
                'stock_items.product_type_id',
                DB::raw('CASE
                            WHEN stock_items.material_id != 0 THEN stock_items.material_id
                            ELSE product_materials.material_id
                        END AS material_id'),
                DB::raw('SUM(
                    CASE
                        WHEN product_materials.material_id IS NOT NULL THEN stock_items.quantity * product_materials.quantity
                        ELSE stock_items.quantity
                    END
                ) AS rqty')
            )   
            ->groupBy('material_id')
            ->get();
    }    

    public function orderStatus($id){
        // Order Current Status
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id', 'uhead.name as uname', 'products.product_id')
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
        ->orderBy('product_types.product_id')
        ->orderBy('product_types.size_id')
        ->groupBy('stock_items.product_type_id', 'stock_items.stage_id')
        ->get();
    }

    public function orderDelivery($id){
        // Order Delivery with Stage
        return StockItem::select('stock_items.product_type_id', 'products.name', 'article_no', 'shead.name as sname', 'sthead.name as stname', 'stock_items.stage_id', 'uhead.name as uname', 'order_items.quantity', 'product_materials.quantity as bqty')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 && stocks.stock_status = 3 && stocks.order_id = ? THEN stock_items.quantity ELSE 0 END) as stockOut', [$id])
        // ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 && stocks.stock_status = 3 THEN stock_items.quantity ELSE 0 END) as stockOut')
        // ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('order_items', 'order_items.product_type_id', '=', 'product_types.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->join('product_materials', 'product_materials.product_type_id', 'product_types.product_type_id')
        ->join('materials', 'materials.material_id', 'product_materials.material_id')
        ->where('materials.material_type_id', '61') // Box
        ->where('order_items.order_id', $id)
        ->whereColumn('order_items.product_stage_id', 'stock_items.stage_id')
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
            $ptid = $data['product_type_id'][$key] ?? 0;
            $mid = $data['material_id'][$key] ?? 0;
            $sid = $data['stage_id'][$key] ?? 0;
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
                    if($quantity > 0){
                        $stockItem['created_by'] = auth()->id();
                        StockItem::create($stockItem);
                    }
                }
            }
        }
    }

    public function delete($id){}
}
