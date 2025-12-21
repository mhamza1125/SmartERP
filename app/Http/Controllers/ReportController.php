<?php

namespace App\Http\Controllers;

use App\Repositories\StockItemRepository;
use App\Repositories\ProductMaterialRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $stockItemRepository;
    protected $productMaterialRepository;

    public function __construct(
        StockItemRepository $stockItemRepository,
        ProductMaterialRepository $productMaterialRepository
    ) {
        $this->middleware(['auth', 'all']);
        $this->stockItemRepository = $stockItemRepository;
        $this->productMaterialRepository = $productMaterialRepository;
    }

    /**
     * Product Stock Requirements Report
     * Shows current stock, unclosed PTCs, confirmed orders, and total needed
     */
    public function productStockRequirements(Request $request)
    {
        $productTypeId = $request->input('product_type_id');

        // Get all product types for the dropdown filter
        $productTypes = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as color_head', 'color_head.head_id', '=', 'product_types.color_id')
            ->select(
                'product_types.product_type_id',
                'products.article_no',
                'products.name as product_name',
                'size_head.name as size_name',
                'color_head.name as color_name'
            )
            ->orderBy('products.article_no')
            ->get();

        // Get current stock using the same method as /stock page
        // The /stock page sums all stages for each product_type_id, excluding rejection stock (head_id = 105)
        $currentStock = $this->stockItemRepository->pStock();

        // Get unclosed PTCs (In Progress only - status 6)
        // PTCs are stored in stocks table, but product_type_id is in stock_items table
        // Use DISTINCT to avoid counting the same PTC multiple times (one PTC can have multiple stock_items)
        // Status 7 (Completed) should not be included as those are closed PTCs
        $unclosedPtcs = DB::table('stocks')
            ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->where('stocks.is_ptc_master', 1)
            ->where('stocks.stock_status', 6) // In Progress only
            ->select('stock_items.product_type_id', 'stocks.stock_id', 'stocks.description', 'stocks.stock_status')
            ->distinct('stocks.stock_id')
            ->get();

        // Get confirmed orders (order_status = 2)
        $confirmedOrders = DB::table('order_items')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->where('orders.order_status', 2) // Confirmed
            ->select('order_items.product_type_id', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.product_type_id')
            ->get();

        // Build the report data
        $reportData = [];

        foreach ($productTypes as $pt) {
            // Calculate current stock using the same method as /stock page
            // Sum all stages for this product_type_id, excluding rejection stock (head_id = 105)
            $stageItems = $currentStock->where('product_type_id', $pt->product_type_id);
            $currentStockQty = 0;
            foreach ($stageItems as $item) {
                // Exclude rejection stock from total count
                if(($item->sthead_id ?? $item->stage_id) != 105) {
                    $currentStockQty += $item->stockIn - $item->stockOut;
                }
            }

            // Calculate unclosed PTC quantity
            $unclosedPtcQty = 0;
            foreach ($unclosedPtcs as $ptc) {
                if ($ptc->product_type_id == $pt->product_type_id) {
                    // Extract quantity from description field
                    if (preg_match('/\[QTY:(\d+)\]/i', $ptc->description, $matches)) {
                        $unclosedPtcQty += (int)$matches[1];
                    }
                }
            }

            // Get confirmed order quantity
            $confirmedOrder = $confirmedOrders->where('product_type_id', $pt->product_type_id)->first();
            $confirmedOrderQty = $confirmedOrder ? $confirmedOrder->total_qty : 0;

            // Calculate total needed
            $totalNeeded = $confirmedOrderQty - ($currentStockQty + $unclosedPtcQty);

            // Get materials for this product type
            $materials = DB::table('product_materials')
                ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
                ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
                ->where('product_materials.product_type_id', $pt->product_type_id)
                ->where('product_materials.component_type', '!=', 'product')
                ->select('materials.name', 'product_materials.quantity', 'heads.name as unit_name')
                ->get();

            $reportData[] = [
                'product_type_id' => $pt->product_type_id,
                'product_name' => $pt->product_name,
                'article_no' => $pt->article_no,
                'size_name' => $pt->size_name,
                'color_name' => $pt->color_name,
                'current_stock' => $currentStockQty,
                'unclosed_ptc' => $unclosedPtcQty,
                'confirmed_orders' => $confirmedOrderQty,
                'total_needed' => $totalNeeded,
                'materials' => $materials,
            ];
        }

        // Filter by product type if specified
        if ($productTypeId) {
            $reportData = array_filter($reportData, function($item) use ($productTypeId) {
                return $item['product_type_id'] == $productTypeId;
            });
        }

        return view('reports.product-stock-requirements', [
            'reportData' => $reportData,
            'productTypes' => $productTypes,
            'selectedProductTypeId' => $productTypeId,
        ]);
    }

    /**
     * Product Stock Ledger Report
     * Shows ledger-style view of stock movements for a single product type
     */
    public function productStockLedger(Request $request)
    {
        $productTypeId = $request->input('product_type_id');

        // Get all product types for the dropdown filter
        $productTypes = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as color_head', 'color_head.head_id', '=', 'product_types.color_id')
            ->select(
                'product_types.product_type_id',
                'products.article_no',
                'products.name as product_name',
                'size_head.name as size_name',
                'color_head.name as color_name'
            )
            ->orderBy('products.article_no')
            ->get();

        $ledgerData = [];
        $selectedProduct = null;
        $currentStockQty = 0;

        if ($productTypeId) {
            // Get selected product details
            $selectedProduct = $productTypes->where('product_type_id', $productTypeId)->first();

            // Get current stock using the same method as /stock page
            // The /stock page sums all stages for each product_type_id, excluding rejection stock (head_id = 105)
            $currentStock = $this->stockItemRepository->pStock();
            $stageItems = $currentStock->where('product_type_id', $productTypeId);

            // Calculate total stock across all stages, excluding rejection stock (head_id = 105)
            $currentStockQty = 0;
            foreach ($stageItems as $item) {
                // Exclude rejection stock from total count
                if(($item->sthead_id ?? $item->stage_id) != 105) {
                    $currentStockQty += $item->stockIn - $item->stockOut;
                }
            }

            // Add opening stock as first row
            $ledgerData[] = [
                'transaction_type' => 'Current Stock',
                'stock_in' => $currentStockQty,
                'stock_out' => 0,
                'balance' => $currentStockQty,
                'date' => now(),
                'reference' => 'Opening',
            ];

            // Get unclosed PTCs (In Progress only - status 6)
            $unclosedPtcs = DB::table('stocks')
                ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
                ->where('stocks.is_ptc_master', 1)
                ->where('stocks.stock_status', 6) // In Progress only
                ->where('stock_items.product_type_id', $productTypeId)
                ->select('stocks.stock_id', 'stocks.stock_no', 'stocks.description', 'stocks.stock_date')
                ->distinct('stocks.stock_id')
                ->orderBy('stocks.stock_date')
                ->get();

            // Add PTCs to ledger
            // PTCs represent production/manufacturing that will add to stock (Stock In)
            foreach ($unclosedPtcs as $ptc) {
                $ptcQty = 0;
                if (preg_match('/\[QTY:(\d+)\]/i', $ptc->description, $matches)) {
                    $ptcQty = (int)$matches[1];
                }

                if ($ptcQty > 0) {
                    $ledgerData[] = [
                        'transaction_type' => 'PTC-' . $ptc->stock_no,
                        'stock_in' => $ptcQty,
                        'stock_out' => 0,
                        'balance' => 0, // Will be calculated
                        'date' => $ptc->stock_date,
                        'reference' => 'PTC',
                    ];
                }
            }

            // Get confirmed orders (order_status = 2)
            $confirmedOrders = DB::table('order_items')
                ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
                ->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
                ->where('orders.order_status', 2) // Confirmed
                ->where('order_items.product_type_id', $productTypeId)
                ->select(
                    'orders.order_id',
                    'orders.order_date',
                    'order_items.quantity',
                    'customers.fname',
                    'customers.lname'
                )
                ->orderBy('orders.order_date')
                ->get();

            // Add orders to ledger
            foreach ($confirmedOrders as $order) {
                $customerName = ($order->fname ?? '') . ' ' . ($order->lname ?? '');
                $ledgerData[] = [
                    'transaction_type' => 'Order #' . $order->order_id . ' - ' . trim($customerName),
                    'stock_in' => 0,
                    'stock_out' => $order->quantity,
                    'balance' => 0, // Will be calculated
                    'date' => $order->order_date,
                    'reference' => 'Order',
                ];
            }

            // Sort by date
            usort($ledgerData, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            // Calculate running balance
            $runningBalance = 0;
            foreach ($ledgerData as &$row) {
                $runningBalance += $row['stock_in'] - $row['stock_out'];
                $row['balance'] = $runningBalance;
            }
        }

        return view('reports.product-stock-ledger', [
            'ledgerData' => $ledgerData,
            'productTypes' => $productTypes,
            'selectedProduct' => $selectedProduct,
            'selectedProductTypeId' => $productTypeId,
        ]);
    }
}

