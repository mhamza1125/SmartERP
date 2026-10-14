<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\StockItemRepository;
use App\Repositories\StockRequirementRepository;
use App\Repositories\ProductMaterialRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $stockItemRepository;
    protected $productMaterialRepository;
    protected $stockRequirementRepository;

    public function __construct(
        StockItemRepository $stockItemRepository,
        ProductMaterialRepository $productMaterialRepository,
        StockRequirementRepository $stockRequirementRepository
    ) {
        $this->middleware(['auth', 'all']);
        $this->stockItemRepository = $stockItemRepository;
        $this->productMaterialRepository = $productMaterialRepository;
        $this->stockRequirementRepository = $stockRequirementRepository;
    }

    /**
     * Product Stock Requirements Report
     * Shows current stock, unclosed PTCs, confirmed orders, and total needed
     */
    public function productStockRequirements(Request $request)
    {
        $this->authorize('reports_access', User::class);
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

        // Batch-tracked figures: each confirmed order is covered by its own reserved stock and
        // PTCs first; only the rest draws on free stock (see StockRequirementRepository)
        $requirements = $this->stockRequirementRepository->requirements();

        // Build the report data
        $reportData = [];

        foreach ($productTypes as $pt) {
            $requirement = $requirements[$pt->product_type_id] ?? [];
            $currentStockQty = $requirement['current_stock'] ?? 0;
            $unclosedPtcQty = $requirement['in_production'] ?? 0;
            $confirmedOrderQty = $requirement['open_orders'] ?? 0;
            $totalNeeded = $requirement['total_needed'] ?? 0;

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
                'reserved_stock' => $requirement['reserved_stock'] ?? 0,
                'free_stock' => $requirement['free_stock'] ?? 0,
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
        $this->authorize('reports_access', User::class);
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

            // Add PTCs to ledger: pieces still in production (issued, not yet released to stock).
            // Released pieces are already in the current stock above.
            foreach ($unclosedPtcs as $ptc) {
                $ptcQty = $this->stockRequirementRepository->ptcWorkInProgress($ptc->stock_id, $productTypeId);

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

            // Get confirmed orders (order_status = 2) with what is still to be delivered
            $confirmedOrders = $this->stockRequirementRepository->openOrderQuantities($productTypeId);

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

