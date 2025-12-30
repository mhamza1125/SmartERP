<?php

namespace App\Repositories;

use App\Models\StockItem;
use Illuminate\Support\Facades\DB;

class StockItemRepository implements GlobalInterface
{
    protected $productCostRepository;

    public function __construct(ProductCostRepository $productCostRepository)
    {
        $this->productCostRepository = $productCostRepository;
    }

    public function all()
    {
        return StockItem::all();
    }

    public function get($id)
    {
        return StockItem::where('stock_items.stock_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->leftJoin('product_types as cpt', 'cpt.product_type_id', '=', 'stock_items.component_product_type_id')
            ->leftJoin('products as cp', 'cp.product_id', '=', 'cpt.product_id')
            ->leftJoin('heads as csize', 'csize.head_id', '=', 'cpt.size_id')
            ->select('stock_items.*', 'products.*', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'cp.article_no as component_article_no', 'cp.name as component_name', 'csize.name as component_size')
            ->orderBy('product_id')
            ->get();
    }

    public function delivery($id)
    {
        // Get Delivery Items with pricing from order_items
        return StockItem::where('deliveries.delivery_id', $id)
            ->join('deliveries', 'deliveries.stock_id', 'stock_items.stock_id')
            ->join('stocks', 'stocks.stock_id', 'stock_items.stock_id')
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftjoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->leftJoin('product_materials', function($join) {
                $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                     ->whereIn('product_materials.material_id', function($query) {
                         // Get packing box material IDs (material_type_id = 61)
                         $query->select('material_id')
                               ->from('materials')
                               ->where('material_type_id', 61);
                     });
            })
            ->leftJoin('order_items', function($join) {
                $join->on('order_items.product_type_id', '=', 'stock_items.product_type_id')
                     ->on('order_items.product_stage_id', '=', 'stock_items.stage_id')
                     ->on('order_items.order_id', '=', 'stocks.order_id');
            })
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->leftJoin('heads as chead', 'chead.head_id', '=', 'customers.currency_id')
            ->select(
                'stock_items.*',
                'product_types.product_type_id',
                'product_types.product_id',
                'product_types.size_id',
                'products.name',
                'products.article_no',
                'products.hs_code',
                'products.product_id',
                'shead.name as hname',
                'uhead.name as uname',
                'sthead.name as sname',
                'puhead.name as puname',
                'materials.name as mname',
                'materials.material_id',
                'product_materials.quantity as bqty',
                'order_items.price',
                'chead.name as cname'
            )
            ->orderBy('products.product_id')
            ->get();

    }

    public function getAvg($id)
    {
        // Showing Product Average Along Matrials in Receive Issuance
        return StockItem::where('stock_items.stock_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('product_materials', function ($join) {
                $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                    ->on('product_materials.material_id', '=', 'stock_items.material_id');
            })
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftjoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->leftJoin('product_types as cpt', 'cpt.product_type_id', '=', 'stock_items.component_product_type_id')
            ->leftJoin('products as cp', 'cp.product_id', '=', 'cpt.product_id')
            ->leftJoin('heads as csize', 'csize.head_id', '=', 'cpt.size_id')
            ->select('stock_items.*', 'products.product_id', 'products.article_no', 'products.name as pname', 'materials.material_id', 'materials.material_no', 'materials.name', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'product_materials.quantity as pqty', 'cp.article_no as component_article_no', 'cp.name as component_name', 'csize.name as component_size')
            ->orderBy('product_id')
            ->get();
    }

    public function getMM($id)
    {
        // Machine Material Issuance
        return StockItem::where('stock_items.stock_id', $id)
            ->join('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('stock_items.*', 'materials.*', 'heads.name as hname')
            ->get();
    }

    public function dailyIssue()
    {
        $date = date('Y-m-d');

        return StockItem::whereDate('stocks.stock_date', $date)
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('product_materials', function ($join) {
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

    public function dailyIssueFilter($dfrom, $dto, $tname, $tid, $oid)
    {
        // Just Filter By Date When both $oid, $tid are Null
        $query = StockItem::whereBetween('stocks.stock_date', [$dfrom, $dto])
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('product_materials', function ($join) {
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

        if ($tid > 0 && $oid > 0) {
            $query->where('stocks.order_id', $oid);
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        } elseif ($tid > 0) {
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        } elseif ($oid > 0) {
            $query->where('stocks.order_id', $oid);
        }

        return $query->get();
    }

    public function dailyReceive()
    {
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

    public function dailyReceiveFilter($dfrom, $dto, $tname, $tid, $oid)
    {
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

        if ($tid > 0 && $oid > 0) {
            $query->where('stocks.order_id', $oid);
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        } elseif ($tid > 0) {
            $query->where('stocks.employee_id', $tid);
            $query->where('stocks.table_name', $tname);
        } elseif ($oid > 0) {
            $query->where('stocks.order_id', $oid);
        }

        return $query->get();
    }

    public function getAll($id)
    {
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
            ->leftJoin('product_types as cpt', 'cpt.product_type_id', '=', 'stock_items.component_product_type_id')
            ->leftJoin('products as cp', 'cp.product_id', '=', 'cpt.product_id')
            ->select('stock_items.*', 'products.*', 'products.name as pname', 'materials.*', 'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'stocks.stock_no', 'cp.article_no as component_article_no', 'cp.name as component_name')
            ->get();
    }

    public function getSum($id)
    {
        // Used By StockInfo
        return StockItem::where('stocks.issue_id', $id)
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->leftJoin('product_types as cpt', 'cpt.product_type_id', '=', 'stock_items.component_product_type_id')
            ->leftJoin('products as cp', 'cp.product_id', '=', 'cpt.product_id')
            ->leftJoin('heads as csize', 'csize.head_id', '=', 'cpt.size_id')
            ->groupBy('stock_items.product_type_id', 'stock_items.material_id', 'stock_items.stage_id', 'stock_items.component_product_type_id')
            ->selectRaw('stock_items.*, products.product_id, products.article_no, products.name as pname, materials.material_id, materials.material_no, materials.name, stock_items.product_type_id,
            stock_items.material_id, stock_items.stage_id, stock_items.component_product_type_id, SUM(stock_items.quantity) as total_quantity, uhead.name as uname, shead.name as sname, sthead.name as stage, puhead.name as puname, stocks.stock_no, cp.article_no as component_article_no, cp.name as component_name, csize.name as component_size')
            ->get();
    }

    public function times($id)
    {
        // Used By StockInfo - Get receive records with dates and status
        return StockItem::where('stocks.issue_id', $id)
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->groupBy('stocks.stock_id')
            ->select('stocks.stock_id', 'stocks.stock_no', 'stocks.stock_date as receive_date', 'stocks.stock_status')
            ->get();
    }

    public function workLog($id)
    {
        // Used By Edit Receive Issuance
        return StockItem::where('stock_items.stock_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('product_costs', 'product_costs.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
            ->select('product_costs.*', 'heads.name as hname')
            ->groupBy('heads.head_id')
            ->get();

        return StockItem::where('stock_items.stock_id', $id)
            ->join('product_costs', 'product_costs.product_type_id', '=', 'stock_items.product_type_id')
            ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
            ->select('product_costs.*', 'heads.name as hname')
            ->groupBy('heads.head_id')
            ->get();
    }

    public function stockVehicle($id)
    {
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

    public function stock()
    {
        // Available Material Stock
        return DB::table(function ($subquery) {
            $subquery->select('materials.material_id', 'materials.material_no', 'materials.name', 'materials.location', 'mthead.name as mtname', 'uhead.name as uname', 'material_type_id')
                // ->selectRaw('SUM(receive_materials.quantity) as total_received')
                ->selectRaw('SUM(receive_materials.approved_qty) as total_received')
                ->selectRaw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
                ->from('materials')
                ->leftJoin('purchase_items', 'purchase_items.material_id', '=', 'materials.material_id')
                ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
                ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
                ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
                ->groupBy('materials.material_id', 'materials.material_no', 'materials.name', 'materials.location', 'mthead.name', 'uhead.name');
        }, 'material_stock')
            ->leftJoin('stock_items', 'stock_items.material_id', '=', 'material_stock.material_id')
            ->leftJoin('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->select('material_stock.*')
            ->selectRaw('IFNULL(SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END), 0) as stockIn')
            ->selectRaw('IFNULL(SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END), 0) as stockOut')
            ->groupBy('material_stock.material_id', 'material_stock.material_no', 'material_stock.name', 'material_stock.location', 'material_stock.mtname', 'material_stock.uname')
            ->get();
    }

    public function freeStock()
    {
        // Free Material Stock Based on Default Purchases
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

    public function orderDeliveryTemp($id)
{
    // Subquery to calculate purchase-based received and returned stock quantities
    $purchaseSub = DB::table('purchase_items')
        ->select(
            'purchase_items.product_type_id',
            'purchase_items.product_stage_id',
            DB::raw('SUM(receive_materials.approved_qty) as total_received'),
            DB::raw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
        )
        ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
        ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
        ->where('purchase_items.material_id', 0)
        ->groupBy('purchase_items.product_type_id', 'purchase_items.product_stage_id');

    // Main Query: Combine order delivery with purchase stock calculations
    return StockItem::select(
            'stock_items.product_type_id', 
            'products.name', 
            'products.article_no', 
            'shead.name as sname', 
            'sthead.name as stname', 
            'stock_items.stage_id', 
            'uhead.name as uname', 
            'order_items.quantity', 
            'product_materials.quantity as bqty'
        )
        ->selectRaw('
            SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) 
            + IFNULL(purchase_sub.total_received, 0) 
            - IFNULL(purchase_sub.total_returned, 0) 
            AS stockIn')
        ->selectRaw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
        ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('order_items', 'order_items.product_type_id', '=', 'product_types.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
        ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
        ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
        ->join('product_materials', 'product_materials.product_type_id', 'product_types.product_type_id')
        ->join('materials', 'materials.material_id', 'product_materials.material_id')
        ->leftJoinSub($purchaseSub, 'purchase_sub', function ($join) {
            $join->on('purchase_sub.product_type_id', '=', 'product_types.product_type_id')
                 ->on('purchase_sub.product_stage_id', '=', 'stock_items.stage_id');
        })
        ->where('materials.material_type_id', '61') // Box
        ->where('order_items.order_id', $id)
        ->whereColumn('order_items.product_stage_id', 'stock_items.stage_id')
        ->where('stock_items.material_id', '=', 0)
        ->groupBy(
            'stock_items.product_type_id', 
            'stock_items.stage_id', 
            'products.name', 
            'products.article_no', 
            'shead.name', 
            'sthead.name', 
            'sthead.head_id', 
            'uhead.name', 
            'products.product_id'
        )
        ->get();
}


    public function pStock()
    {
        // Subquery 1: Stock-based quantities grouped by product_type_id and stage_id (regular products)
        $stockSub = DB::table('stock_items')
            ->select(
                'stock_items.product_type_id',
                'stock_items.stage_id',
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
            )
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stock_items.material_id', '=', 0)
            ->whereNull('stock_items.component_product_type_id') // Exclude component product entries
            ->groupBy('stock_items.product_type_id', 'stock_items.stage_id');

        // Subquery 1b: Component product stock (when products are used as components)
        // These are tracked by component_product_type_id, not product_type_id
        // Use the actual stage_id from stock_items (components don't have stages when used as components)
        $componentStockSub = DB::table('stock_items')
            ->select(
                'stock_items.component_product_type_id as product_type_id',
                'stock_items.stage_id', // Use actual stage from stock_items
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
            )
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->whereNotNull('stock_items.component_product_type_id')
            ->groupBy('stock_items.component_product_type_id', 'stock_items.stage_id');

        // Subquery 2: Purchase-based received/returned quantities (for products only)
        $purchaseSub = DB::table('purchase_items')
            ->select(
                'purchase_items.product_type_id',
                'purchase_items.product_stage_id as stage_id',
                DB::raw('SUM(receive_materials.approved_qty) as total_received'),
                DB::raw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
            )
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->where('purchase_items.material_id', 0)
            ->groupBy('purchase_items.product_type_id', 'purchase_items.product_stage_id');

        // Combine stock, component stock, and purchase data using UNION approach
        $combinedSub = DB::table(DB::raw("(
            SELECT product_type_id, stage_id, stockIn, stockOut, 0 as purchase_received, 0 as purchase_returned
            FROM ({$stockSub->toSql()}) as stock_data
            UNION ALL
            SELECT product_type_id, stage_id, stockIn, stockOut, 0 as purchase_received, 0 as purchase_returned
            FROM ({$componentStockSub->toSql()}) as component_stock_data
            UNION ALL
            SELECT product_type_id, stage_id, 0 as stockIn, 0 as stockOut, total_received as purchase_received, total_returned as purchase_returned
            FROM ({$purchaseSub->toSql()}) as purchase_data
        ) as combined_data"))
            ->mergeBindings($stockSub)
            ->mergeBindings($componentStockSub)
            ->mergeBindings($purchaseSub)
            ->select(
                'product_type_id',
                'stage_id',
                DB::raw('SUM(stockIn) + SUM(purchase_received) - SUM(purchase_returned) as stockIn'),
                DB::raw('SUM(stockOut) as stockOut')
            )
            ->groupBy('product_type_id', 'stage_id');

        // Main Query - Join aggregated data with product info
        return DB::table(DB::raw("({$combinedSub->toSql()}) as agg_stock"))
            ->mergeBindings($combinedSub)
            ->select(
                'agg_stock.product_type_id',
                'products.name',
                'products.article_no',
                'shead.name as sname',
                'agg_stock.stage_id',
                'sthead.name as stname',
                'sthead.head_id as sthead_id',
                'uhead.name as uname',
                'products.product_id',
                'agg_stock.stockIn',
                'agg_stock.stockOut'
            )
            ->join('product_types', 'product_types.product_type_id', '=', 'agg_stock.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'agg_stock.stage_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->orderBy('products.product_id')
            ->orderBy('product_types.size_id')
            ->orderBy('agg_stock.stage_id')
            ->get();
    }

    /**
     * Get stock data for a specific product type (used by Production Order Print)
     * Uses the same calculation logic as pStock() but filtered by product_type_id
     * This method filters the full pStock() results in PHP to ensure consistency
     */
    public function pStockByProductType($productTypeId)
    {
        // Get all stock data using the same method as the /stock page
        $allStock = $this->pStock();

        // Filter to only the requested product type
        $filteredStock = $allStock->filter(function($item) use ($productTypeId) {
            return $item->product_type_id == $productTypeId;
        });

        // Return as a collection with only the needed fields
        return $filteredStock->map(function($item) {
            return (object)[
                'product_type_id' => $item->product_type_id,
                'stage_id' => $item->stage_id,
                'stockIn' => $item->stockIn,
                'stockOut' => $item->stockOut,
            ];
        })->values();
    }

    public function gStock()
    {
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

        // Subquery: purchase-based received/returned quantities (for products only)
        $purchaseSub = DB::table('purchase_items')
            ->select(
                'purchase_items.product_type_id',
                'purchase_items.product_stage_id',
                DB::raw('SUM(receive_materials.approved_qty) as total_received'),
                DB::raw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
            )
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->where('purchase_items.material_id', 0)
            ->groupBy('purchase_items.product_type_id', 'purchase_items.product_stage_id');

        // Fetch available product stock with stage_id check
        $productStock = DB::table('product_types')
            ->select(
                'product_types.product_type_id', 'products.name', 'products.article_no', 'shead.name as sname', 'sthead.name as stname', 'sthead.head_id as sthead_id', 'uhead.name as uname', 'products.product_id',
                DB::raw('COALESCE(stock_items.stage_id, purchase_sub.product_stage_id) as stage_id'),
                DB::raw('
                    SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END)
                    + IFNULL(purchase_sub.total_received, 0)
                    - IFNULL(purchase_sub.total_returned, 0) AS stockIn'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) AS stockOut')
            )
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('stock_items', function ($join) {
                $join->on('stock_items.product_type_id', '=', 'product_types.product_type_id')
                    ->where('stock_items.material_id', '=', 0);
            })
            ->leftJoin('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoinSub($purchaseSub, 'purchase_sub', function ($join) {
                $join->on('purchase_sub.product_type_id', '=', 'product_types.product_type_id')
                    ->whereRaw('purchase_sub.product_stage_id = COALESCE(stock_items.stage_id, purchase_sub.product_stage_id)');
            })
            ->leftJoin('heads as sthead', function ($join) {
                $join->on('sthead.head_id', '=', DB::raw('COALESCE(stock_items.stage_id, purchase_sub.product_stage_id)'));
            })
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->groupBy(
                'product_types.product_type_id', 'products.name', 'products.article_no', 'shead.name', 'sthead.name', 'sthead.head_id', 'uhead.name', 'products.product_id',
                DB::raw('COALESCE(stock_items.stage_id, purchase_sub.product_stage_id)')
            )
            ->get()
            ->keyBy(function ($item) {
                return $item->product_type_id.'_'.$item->stage_id;
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

    public function rstock($id)
    {
        // Receiveable Stock Based on Early Receiving & Null
        return StockItem::join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('stocks as rstocks', 'rstocks.issue_id', '=', 'stocks.stock_id')
            ->leftJoin('product_materials', function ($join) {
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

    public function orderStatus($id)
    {
        // Order Current Status - includes ordered quantity from order_items
        // Start from order_items to show ALL products in the order, including those with zero stock
        // Uses the same calculation logic as pStock() to include both stock_items and purchase_items

        // Subquery 1: Stock-based quantities grouped by product_type_id and stage_id (regular products)
        $stockSub = DB::table('stock_items')
            ->select(
                'stock_items.product_type_id',
                'stock_items.stage_id',
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
            )
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stock_items.material_id', '=', 0)
            ->whereNull('stock_items.component_product_type_id') // Exclude component product entries
            ->groupBy('stock_items.product_type_id', 'stock_items.stage_id');

        // Subquery 1b: Component product stock (when products are used as components)
        // Use the actual stage_id from stock_items (components don't have stages when used as components)
        $componentStockSub = DB::table('stock_items')
            ->select(
                'stock_items.component_product_type_id as product_type_id',
                'stock_items.stage_id', // Use actual stage from stock_items
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
            )
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->whereNotNull('stock_items.component_product_type_id')
            ->groupBy('stock_items.component_product_type_id', 'stock_items.stage_id');

        // Subquery 2: Purchase-based received/returned quantities (for products only)
        $purchaseSub = DB::table('purchase_items')
            ->select(
                'purchase_items.product_type_id',
                'purchase_items.product_stage_id as stage_id',
                DB::raw('SUM(receive_materials.approved_qty) as total_received'),
                DB::raw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
            )
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->where('purchase_items.material_id', 0)
            ->groupBy('purchase_items.product_type_id', 'purchase_items.product_stage_id');

        // Combine stock, component stock, and purchase data using UNION approach
        $combinedSub = DB::table(DB::raw("(
            SELECT product_type_id, stage_id, stockIn, stockOut, 0 as purchase_received, 0 as purchase_returned
            FROM ({$stockSub->toSql()}) as stock_data
            UNION ALL
            SELECT product_type_id, stage_id, stockIn, stockOut, 0 as purchase_received, 0 as purchase_returned
            FROM ({$componentStockSub->toSql()}) as component_stock_data
            UNION ALL
            SELECT product_type_id, stage_id, 0 as stockIn, 0 as stockOut, total_received as purchase_received, total_returned as purchase_returned
            FROM ({$purchaseSub->toSql()}) as purchase_data
        ) as combined_data"))
            ->mergeBindings($stockSub)
            ->mergeBindings($componentStockSub)
            ->mergeBindings($purchaseSub)
            ->select(
                'product_type_id',
                'stage_id',
                DB::raw('SUM(stockIn) + SUM(purchase_received) - SUM(purchase_returned) as stockIn'),
                DB::raw('SUM(stockOut) as stockOut')
            )
            ->groupBy('product_type_id', 'stage_id');

        // Main Query - Join aggregated data with order items and product info
        return DB::table('order_items')
            ->select(
                'order_items.product_type_id',
                'products.name',
                'products.article_no',
                'shead.name as sname',
                'sthead.name as stname',
                'agg_stock.stage_id',
                'uhead.name as uname',
                'products.product_id',
                'agg_stock.stockIn',
                'agg_stock.stockOut'
            )
            ->selectRaw('MAX(order_items.quantity) as ordered_qty')
            ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->leftJoinSub(
                DB::table(DB::raw("({$combinedSub->toSql()}) as agg_stock"))
                    ->mergeBindings($combinedSub),
                'agg_stock',
                function ($join) {
                    $join->on('agg_stock.product_type_id', '=', 'product_types.product_type_id');
                }
            )
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'agg_stock.stage_id')
            ->where('order_items.order_id', $id)
            ->orderBy('product_types.product_id')
            ->orderBy('product_types.size_id')
            ->orderBy('agg_stock.stage_id')
            ->groupBy('order_items.product_type_id', 'agg_stock.stage_id', 'products.name', 'products.article_no', 'shead.name', 'sthead.name', 'uhead.name', 'products.product_id', 'agg_stock.stockIn', 'agg_stock.stockOut')
            ->get();
    }

    public function orderDelivery($id)
    {
        // Subquery for Purchase Received/Returned (for products)
        $purchaseSub = DB::table('purchase_items')
            ->select(
                'purchase_items.product_type_id',
                'purchase_items.product_stage_id',
                DB::raw('SUM(receive_materials.approved_qty) as total_received'),
                DB::raw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
            )
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->where('purchase_items.material_id', 0)
            ->groupBy('purchase_items.product_type_id', 'purchase_items.product_stage_id');

        // Subquery for Box Materials (only box materials, not all component products)
        $boxMaterialsSub = DB::table('product_materials')
            ->select(
                'product_materials.product_type_id',
                DB::raw('MAX(product_materials.quantity) as bqty')
            )
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->where('materials.material_type_id', 61) // Box only
            ->groupBy('product_materials.product_type_id');

        // Main Query
        return StockItem::select(
                'stock_items.product_type_id',
                'products.name',
                'products.article_no',
                'shead.name as sname',
                'sthead.name as stname',
                'stock_items.stage_id', // Just stage_id from stock_items
                'uhead.name as uname',
                'order_items.quantity',
                DB::raw('COALESCE(box_materials.bqty, 0) as bqty')
            )
            ->selectRaw('
                (
                    SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END)
                    + IFNULL(purchase_sub.total_received, 0)
                    - IFNULL(purchase_sub.total_returned, 0)
                ) as stockIn
            ')
            ->selectRaw('
                SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut
            ')
            ->selectRaw('
                SUM(CASE WHEN stocks.stock_type = 2 AND stocks.stock_status = 3 AND stocks.order_id = ? THEN stock_items.quantity ELSE 0 END) as stockOutDelivered
            ', [$id])
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('order_items', 'order_items.product_type_id', '=', 'product_types.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id') // Only on stock_items.stage_id
            ->leftJoinSub($boxMaterialsSub, 'box_materials', function ($join) {
                $join->on('box_materials.product_type_id', '=', 'product_types.product_type_id');
            })
            ->leftJoinSub($purchaseSub, 'purchase_sub', function ($join) {
                $join->on('purchase_sub.product_type_id', '=', 'product_types.product_type_id')
                    ->whereColumn('purchase_sub.product_stage_id', '=', 'stock_items.stage_id');
            })
            ->where('order_items.order_id', $id)
            ->whereColumn('order_items.product_stage_id', 'stock_items.stage_id')
            ->where('stock_items.material_id', 0)
            ->groupBy(
                'stock_items.product_type_id',
                'stock_items.stage_id',
                'products.name',
                'products.article_no',
                'shead.name',
                'sthead.name',
                'uhead.name',
                'order_items.quantity'
            )
            ->get();
    }

    public function orderDeliveryOld($id)
    {
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

    public function pStockGet($id)
    {
        // Product Stock AjaxPM - includes both purchase-based and in-house stock
        // Uses the same UNION-based approach as pStock() to prevent double-counting

        // Subquery 1: Stock-based quantities for regular products (not components)
        $stockSub = DB::table('stock_items')
            ->select(
                'stock_items.product_type_id',
                'stock_items.stage_id',
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
            )
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stock_items.material_id', '=', 0)
            ->where('stock_items.product_type_id', '=', $id)
            ->whereNull('stock_items.component_product_type_id') // Exclude component product entries
            ->groupBy('stock_items.product_type_id', 'stock_items.stage_id');

        // Subquery 1b: Component product stock (when this product is used as a component)
        // These are tracked by component_product_type_id, not product_type_id
        // Use the actual stage_id from stock_items (components don't have stages when used as components)
        $componentStockSub = DB::table('stock_items')
            ->select(
                'stock_items.component_product_type_id as product_type_id',
                'stock_items.stage_id', // Use actual stage from stock_items
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as stockIn'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END) as stockOut')
            )
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stock_items.component_product_type_id', '=', $id)
            ->whereNotNull('stock_items.component_product_type_id')
            ->groupBy('stock_items.component_product_type_id', 'stock_items.stage_id');

        // Subquery 2: Purchase-based received/returned quantities (for products only)
        $purchaseSub = DB::table('purchase_items')
            ->select(
                'purchase_items.product_type_id',
                'purchase_items.product_stage_id as stage_id',
                DB::raw('SUM(receive_materials.approved_qty) as total_received'),
                DB::raw('IFNULL(SUM(return_materials.quantity), 0) as total_returned')
            )
            ->leftJoin('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->where('purchase_items.material_id', 0)
            ->where('purchase_items.product_type_id', '=', $id)
            ->groupBy('purchase_items.product_type_id', 'purchase_items.product_stage_id');

        // Combine stock, component stock, and purchase data using UNION approach
        // This prevents double-counting by keeping each data source separate until final aggregation
        $combinedSub = DB::table(DB::raw("(
            SELECT product_type_id, stage_id, stockIn, stockOut, 0 as purchase_received, 0 as purchase_returned
            FROM ({$stockSub->toSql()}) as stock_data
            UNION ALL
            SELECT product_type_id, stage_id, stockIn, stockOut, 0 as purchase_received, 0 as purchase_returned
            FROM ({$componentStockSub->toSql()}) as component_stock_data
            UNION ALL
            SELECT product_type_id, stage_id, 0 as stockIn, 0 as stockOut, total_received as purchase_received, total_returned as purchase_returned
            FROM ({$purchaseSub->toSql()}) as purchase_data
        ) as combined_data"))
            ->mergeBindings($stockSub)
            ->mergeBindings($componentStockSub)
            ->mergeBindings($purchaseSub)
            ->select(
                'product_type_id',
                'stage_id',
                DB::raw('SUM(stockIn) + SUM(purchase_received) - SUM(purchase_returned) as stockIn'),
                DB::raw('SUM(stockOut) as stockOut')
            )
            ->groupBy('product_type_id', 'stage_id');

        // Main Query - Join aggregated data with product info
        return DB::table(DB::raw("({$combinedSub->toSql()}) as agg_stock"))
            ->mergeBindings($combinedSub)
            ->select(
                'agg_stock.product_type_id',
                'products.name',
                'products.article_no',
                'shead.name as sname',
                'agg_stock.stage_id',
                'sthead.name as stname',
                'sthead.head_id as sthead_id',
                'uhead.name as uname',
                'products.product_id',
                'agg_stock.stockIn',
                'agg_stock.stockOut'
            )
            ->join('product_types', 'product_types.product_type_id', '=', 'agg_stock.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'agg_stock.stage_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->orderBy('products.product_id')
            ->orderBy('product_types.size_id')
            ->orderBy('agg_stock.stage_id')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = StockItem::create($data);

        return $store->stock_item_id;
    }

    public function update($id, array $data)
    {
        $existingItems = StockItem::where('stock_id', $id)->get();
        $componentIds = $data['component_id'] ?? [];

        // Assuming $data['product_type_id'] and $data['material_id'] are arrays of IDs.
        foreach ($existingItems as $existingItem) {
            // Check if combination does not exist in the provided data
            $found = false;
            foreach ($data['quantity'] as $key => $qty) {
                $ptid = $data['product_type_id'][$key] ?? 0;
                $mid = $data['material_id'][$key] ?? 0;
                $sid = $data['stage_id'][$key] ?? 0;
                $cid = $componentIds[$key] ?? null;

                if ($existingItem->product_type_id == $ptid &&
                    $existingItem->material_id == $mid &&
                    $existingItem->stage_id == $sid &&
                    $existingItem->component_product_type_id == $cid) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
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
            $cid = $componentIds[$key] ?? null;
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
                    'component_product_type_id' => $cid ?: null,
                    'work_logs' => $work,
                    'work_wages' => $wages,
                ];

                $stockQuery = StockItem::where('stock_id', $id)
                    ->where('product_type_id', $ptid)
                    ->where('material_id', $mid)
                    ->where('stage_id', $sid);

                if ($cid) {
                    $stockQuery->where('component_product_type_id', $cid);
                } else {
                    $stockQuery->whereNull('component_product_type_id');
                }

                $stock = $stockQuery->first();
                if ($stock) {
                    $stock->update($stockItem);
                } else {
                    if ($quantity > 0) {
                        $stockItem['created_by'] = auth()->id();
                        StockItem::create($stockItem);
                    }
                }
            }
        }
    }

    public function updateStock($id, array $data)
    {
        $stock = StockItem::where('stock_id', '1')
            ->where('material_id', $id)
            ->first();
        if ($stock) {
            $stock->update($data);
        } else {
            $data['created_by'] = auth()->id();
            $store = StockItem::create($data);

            return $store->stock_item_id;
        }
    }

    public function updateProductStock($productId, $openingStock)
    {
        // Update opening stock for the first product type of this product
        $firstProductType = DB::table('product_types')
            ->where('product_id', $productId)
            ->where('product_type_status', 1)
            ->orderBy('product_type_id')
            ->first();

        if ($firstProductType) {
            $stock = StockItem::where('stock_id', '1')
                ->where('product_type_id', $firstProductType->product_type_id)
                ->where('material_id', '0')
                ->first();

            $stockData = [
                'stock_id' => '1',
                'product_type_id' => $firstProductType->product_type_id,
                'material_id' => '0',
                'quantity' => $openingStock,
                'stage_id' => '0',
                'work_logs' => '0',
                'work_wages' => '0',
            ];

            if ($stock) {
                $stock->update($stockData);
            } else {
                if ($openingStock > 0) {
                    $stockData['created_by'] = auth()->id();
                    $store = StockItem::create($stockData);
                    return $store->stock_item_id;
                }
            }
        }
    }

    public function delete($id)
    {
        // Delete stock items by stock_id
        return StockItem::where('stock_id', $id)->delete();
    }

    public function orderRemainingItems($id)
    {
        // Subquery for regular product delivered quantities
        $deliveredSub = DB::raw('(
            SELECT
                si.product_type_id,
                si.stage_id,
                SUM(si.quantity) as delivered_quantity
            FROM stock_items si
            JOIN stocks s ON s.stock_id = si.stock_id
            WHERE s.order_id = ' . $id . '
            AND s.stock_status = 3
            AND s.stock_type = 2
            AND si.component_product_type_id IS NULL
            GROUP BY si.product_type_id, si.stage_id
        ) as delivered');

        // Subquery for component product delivered quantities
        $componentDeliveredSub = DB::raw('(
            SELECT
                si.component_product_type_id as product_type_id,
                0 as stage_id,
                SUM(si.quantity) as delivered_quantity
            FROM stock_items si
            JOIN stocks s ON s.stock_id = si.stock_id
            WHERE s.order_id = ' . $id . '
            AND s.stock_status = 3
            AND s.stock_type = 2
            AND si.component_product_type_id IS NOT NULL
            GROUP BY si.component_product_type_id
        ) as component_delivered');

        // Subquery for regular product returned quantities
        $returnedSub = DB::raw('(
            SELECT
                si.product_type_id,
                si.stage_id,
                SUM(dri.quantity) as returned_quantity
            FROM delivery_return_items dri
            JOIN stock_items si ON si.stock_item_id = dri.stock_item_id
            JOIN stocks s ON s.stock_id = si.stock_id
            WHERE s.order_id = ' . $id . '
            AND si.component_product_type_id IS NULL
            GROUP BY si.product_type_id, si.stage_id
        ) as returned');

        // Subquery for component product returned quantities
        $componentReturnedSub = DB::raw('(
            SELECT
                si.component_product_type_id as product_type_id,
                0 as stage_id,
                SUM(dri.quantity) as returned_quantity
            FROM delivery_return_items dri
            JOIN stock_items si ON si.stock_item_id = dri.stock_item_id
            JOIN stocks s ON s.stock_id = si.stock_id
            WHERE s.order_id = ' . $id . '
            AND si.component_product_type_id IS NOT NULL
            GROUP BY si.component_product_type_id
        ) as component_returned');

        // Get order items with delivered quantities and returned quantities to show remaining items
        return DB::table('order_items')
            ->select(
                'order_items.order_item_id',
                'order_items.product_type_id',
                'order_items.product_stage_id',
                'order_items.quantity as ordered_quantity',
                'products.name as product_name',
                'products.article_no',
                'shead.name as size_name',
                'sthead.name as stage_name',
                'uhead.name as unit_name',
                DB::raw('COALESCE(delivered.delivered_quantity, 0) as delivered_quantity'),
                DB::raw('COALESCE(returned.returned_quantity, 0) as returned_quantity'),
                DB::raw('(COALESCE(delivered.delivered_quantity, 0) - COALESCE(returned.returned_quantity, 0)) as net_delivered_quantity'),
                DB::raw('(order_items.quantity - (COALESCE(delivered.delivered_quantity, 0) - COALESCE(returned.returned_quantity, 0))) as remaining_quantity')
            )
            ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'order_items.product_stage_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->leftJoin($deliveredSub, function($join) {
                $join->on('delivered.product_type_id', '=', 'order_items.product_type_id')
                     ->on('delivered.stage_id', '=', 'order_items.product_stage_id');
            })
            ->leftJoin($returnedSub, function($join) {
                $join->on('returned.product_type_id', '=', 'order_items.product_type_id')
                     ->on('returned.stage_id', '=', 'order_items.product_stage_id');
            })
            ->where('order_items.order_id', $id)
            ->orderBy('products.product_id')
            ->orderBy('product_types.product_type_id')
            ->get();
    }

    public function deleteOpeningStock($productTypeId)
    {
        // Delete opening stock entries for a specific product type
        return StockItem::where('product_type_id', $productTypeId)
            ->where('stock_id', '1') // Opening stock
            ->where('material_id', '0')
            ->delete();
    }
}
