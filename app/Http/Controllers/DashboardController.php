<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        // Card 1: Orders in Progress (Confirmed orders - status 2)
        $ordersInProgress = DB::table('orders')
            ->where('order_status', 2)
            ->count();

        // Card 2: Running PTCs (Active PTCs - status 6, is_ptc_master = 1)
        $runningPtcs = DB::table('stocks')
            ->where('is_ptc_master', 1)
            ->where('stock_status', 6)
            ->count();

        // Card 3: Expense This Month
        // Expenses are stored as credit (incurred) or debit (reversal)
        // Sum both to get total expense activity
        $expenseThisMonth = DB::table('transactions')
            ->where('transaction_to', 'expense')
            ->where('transaction_type', 'expense')
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->selectRaw('COALESCE(SUM(credit), 0) + COALESCE(SUM(debit), 0) as total')
            ->value('total') ?? 0;

        // Card 4: Low Stock Alerts (products with stock < 50 units, excluding rejection stock head_id = 105)
        $lowStockAlerts = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->where('stock_items.material_id', 0) // Only products, not materials
            ->whereNull('stock_items.component_product_type_id') // Exclude component products
            ->select('stock_items.product_type_id')
            ->distinct()
            ->get()
            ->filter(function($item) {
                // Calculate total stock for this product type across all stages, excluding rejection stock
                $totalStock = DB::table('stock_items')
                    ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
                    ->where('stock_items.product_type_id', $item->product_type_id)
                    ->where('stock_items.material_id', 0)
                    ->whereNull('stock_items.component_product_type_id')
                    ->get()
                    ->sum(function($stock) {
                        // Exclude rejection stock (head_id = 105)
                        if(($stock->sthead_id ?? $stock->stage_id) == 105) return 0;
                        return ($stock->stockIn ?? 0) - ($stock->stockOut ?? 0);
                    });
                return $totalStock < 50;
            })
            ->count();


        // Table 1: Running PTCs (10 most recent)
        $runningPtcsTable = DB::table('stocks')
            ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
            ->where('stocks.is_ptc_master', 1)
            ->where('stocks.stock_status', 6)
            ->select(
                'stocks.stock_id',
                'stocks.stock_no',
                'stocks.description',
                'stocks.stock_date',
                'products.article_no',
                'products.name as product_name',
                'size_head.name as size_name'
            )
            ->distinct('stocks.stock_id')
            ->orderBy('stocks.stock_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function($ptc) {
                // Extract quantity from description field
                $qty = 0;
                if (preg_match('/\[QTY:(\d+)\]/i', $ptc->description, $matches)) {
                    $qty = (int)$matches[1];
                }
                $ptc->quantity = $qty;
                return $ptc;
            });

        // Table 2: Orders in Progress (10 most recent)
        $ordersInProgressTable = DB::table('orders')
            ->join('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->where('orders.order_status', 2)
            ->select(
                'orders.order_id',
                'orders.job_no',
                'orders.order_date',
                'orders.due_date',
                'customers.fname',
                'customers.lname'
            )
            ->orderBy('orders.order_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function($order) {
                // Count order items
                $itemCount = DB::table('order_items')
                    ->where('order_id', $order->order_id)
                    ->count();
                $order->item_count = $itemCount;
                return $order;
            });

        return view('dashboard', [
            'ordersInProgress' => $ordersInProgress,
            'runningPtcs' => $runningPtcs,
            'expenseThisMonth' => $expenseThisMonth ?? 0,
            'lowStockAlerts' => $lowStockAlerts,
            'runningPtcsTable' => $runningPtcsTable,
            'ordersInProgressTable' => $ordersInProgressTable,
        ]);
    }
}
