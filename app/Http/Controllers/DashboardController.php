<?php

namespace App\Http\Controllers;

use App\Repositories\StockItemRepository;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Transaction;
use App\Repositories\OrderRepository;
use App\Repositories\PurchaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Low Stock Alerts card: a product size below this many units (all stages except Rejection). */
    private const LOW_STOCK_THRESHOLD = 50;

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        // Each card / table is computed only for users who may open its module (same checks as the view)
        $user = auth()->user();

        // Card 1: Orders in Progress (Confirmed orders - status 2)
        $ordersInProgress = 0;
        if ($user->can('access', Order::class)) {
            $ordersInProgress = DB::table('orders')
                ->where('order_status', 2)
                ->count();
        }

        // Card 2: Running PTCs (Active PTCs - status 6, is_ptc_master = 1)
        $runningPtcs = 0;
        if ($user->can('access', Stock::class)) {
            $runningPtcs = DB::table('stocks')
                ->where('is_ptc_master', 1)
                ->where('stock_status', 6)
                ->count();
        }

        // Card 3: Expense This Month
        // Expenses are stored as credit (incurred) or debit (reversal): net expense = credit - debit (F-12)
        $expenseThisMonth = 0;
        if ($user->can('access', Transaction::class)) {
            $expenseThisMonth = DB::table('transactions')
                ->where(fn ($q) => $q->where(fn ($q) => $q->where('transaction_to', 'expense')->where('transaction_type', 'expense'))
                    // Vendor payments of a journal voucher with an expense head are that expense, paid
                    ->orWhere(fn ($q) => $q->whereIn('transaction_to', ['vendor', 'contractor'])->where('transaction_type', 'payment')
                        ->whereNotNull('voucher_id')->whereNotNull('expense_head_id')))
                ->whereMonth('transaction_date', Carbon::now()->month)
                ->whereYear('transaction_date', Carbon::now()->year)
                ->selectRaw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as total')
                ->value('total') ?? 0;
        }

        // Card 4: Low Stock Alerts: product sizes whose stock (the /stock figure, all stages except
        // Rejection 105) is below 50 units. One stock query (F-11; it used to read raw lines, 1 query per size)
        $lowStockAlerts = 0;
        if ($user->can('stocks_access', Stock::class)) {
            $lowStockAlerts = app(StockItemRepository::class)->pStock()
                ->groupBy('product_type_id')
                ->filter(fn ($rows) => $rows->sum(fn ($row) => (int) $row->stage_id === Stock::STAGE_REJECTED ? 0 : (float) $row->stockIn - (float) $row->stockOut) < self::LOW_STOCK_THRESHOLD)
                ->count();
        }

        // Table 1: Running PTCs (10 most recent)
        $runningPtcsTable = collect();
        if ($user->can('access', Stock::class)) {
            // One row per PTC: its first product line (a PTC with several lines used to repeat, F-39)
            $firstLine = DB::table('stock_items')->where('product_type_id', '>', 0)
                ->groupBy('stock_id')->selectRaw('stock_id, MIN(stock_item_id) as stock_item_id');
            $runningPtcsTable = DB::table('stocks')
                ->joinSub($firstLine, 'first_line', 'first_line.stock_id', '=', 'stocks.stock_id')
                ->join('stock_items', 'stock_items.stock_item_id', '=', 'first_line.stock_item_id')
                ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id') // PTC without an order = Default PTC
                ->where('stocks.is_ptc_master', 1)
                ->where('stocks.stock_status', 6)
                ->select(
                    'stocks.stock_id',
                    'stocks.stock_no',
                    'stocks.description',
                    'stocks.stock_date',
                    'products.article_no',
                    'products.name as product_name',
                    'size_head.name as size_name',
                    'orders.job_no'
                )
                ->orderBy('stocks.stock_date', 'desc')
                ->orderBy('stocks.stock_id', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($ptc) {
                    // Extract quantity from description field
                    $qty = 0;
                    if (preg_match('/\[QTY:(\d+)\]/i', $ptc->description, $matches)) {
                        $qty = (int) $matches[1];
                    }
                    $ptc->quantity = $qty;

                    return $ptc;
                });
        }

        // Table: Orders in Progress - every confirmed (running) order, nearest due date first
        $ordersInProgressTable = $user->can('access', Order::class)
            ? app(OrderRepository::class)->inProgress()
            : collect();

        // Table: Open Purchase Orders - pending / partially received, nearest required date first
        $openPurchasesTable = $user->can('access', Purchase::class)
            ? app(PurchaseRepository::class)->openPurchases()
            : null;

        // Recent price changes: buying prices for purchase users, selling prices for customer users
        $partyPrices = app(\App\Repositories\PartyPriceRepository::class);
        $purchasePriceChanges = $user->can('access', Purchase::class) ? $partyPrices->changes('vendor', null, 10) : null;
        $sellingPriceChanges = $user->can('access', \App\Models\Customer::class) ? $partyPrices->changes('customer', null, 10) : null;

        return view('dashboard', [
            'purchasePriceChanges' => $purchasePriceChanges,
            'sellingPriceChanges' => $sellingPriceChanges,
            'ordersInProgress' => $ordersInProgress,
            'runningPtcs' => $runningPtcs,
            'expenseThisMonth' => $expenseThisMonth ?? 0,
            'lowStockAlerts' => $lowStockAlerts,
            'runningPtcsTable' => $runningPtcsTable,
            'ordersInProgressTable' => $ordersInProgressTable,
            'openPurchasesTable' => $openPurchasesTable,
        ]);
    }
}
