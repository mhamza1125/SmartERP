<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\StockRequest;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Repositories\IGroupRepository;
use App\Repositories\VendorRepository;
use App\Repositories\MachineRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\IGroupItemRepository;
use App\Repositories\ProductCostRepository;
use App\Repositories\ProductMaterialRepository;

class StockController extends Controller
{
    protected $headRepository;

    protected $imageRepository;

    protected $orderRepository;

    protected $stockRepository;

    protected $igroupRepository;

    protected $vendorRepository;

    protected $machineRepository;

    protected $materialRepository;

    protected $employeeRepository;

    protected $stockItemRepository;

    protected $orderItemRepository;

    protected $igroupItemRepository;

    protected $productCostRepository;

    protected $productMaterialRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        StockRepository $stockRepository,
        IGroupRepository $igroupRepository,
        OrderRepository $orderRepository,
        VendorRepository $vendorRepository,
        MachineRepository $machineRepository,
        MaterialRepository $materialRepository,
        EmployeeRepository $employeeRepository,
        OrderItemRepository $orderItemRepository,
        IGroupItemRepository $igroupItemRepository,
        StockItemRepository $stockItemRepository,
        ProductCostRepository $productCostRepository,
        ProductMaterialRepository $productMaterialRepository,
    ) {
        $this->middleware(['auth', 'all'])->except([
            'ajaxPM', 'ajaxPT', 'ajaxPTStock', 'ajaxPC', 'ajaxPS',
            'ajaxIG', 'ajaxMQty', 'ajaxAMQty', 'ajaxATMQty', 'getAllProducts'
        ]);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->orderRepository = $orderRepository;
        $this->stockRepository = $stockRepository;
        $this->igroupRepository = $igroupRepository;
        $this->vendorRepository = $vendorRepository;
        $this->machineRepository = $machineRepository;
        $this->materialRepository = $materialRepository;
        $this->employeeRepository = $employeeRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->igroupItemRepository = $igroupItemRepository;
        $this->productCostRepository = $productCostRepository;
        $this->productMaterialRepository = $productMaterialRepository;
    }

    public function index()
    {
        $this->authorize('stocks_access', Stock::class);

        // Available Stock
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();

        return view('stock', [
            'stock' => $stock,
            'pstock' => $pstock,
        ]);
    }

    /**
     * Print stock report
     */
    public function printStock(Request $request)
    {
        $this->authorize('stocks_access', Stock::class);

        // Available Stock
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $type = $request->query('type', 'all'); // 'all', 'product', or 'machine'

        return view('print.stock-report', [
            'stock' => $stock,
            'pstock' => $pstock,
            'type' => $type,
        ]);
    }

    public function wages()
    {
        $this->authorize('access', Transaction::class);
        
        // Employee / Vendor Wages
        $wages = $this->stockRepository->wagesAll();
        $employeeWages = $wages['employees'];
        $vendorWages = $wages['vendors'];
        foreach (['employeeWages', 'vendorWages'] as $key) {
            foreach ($$key as &$item) {
                $item = (object) $item;
            }
        }

        return view('wages', [
            'employeeWages' => $employeeWages,
            'vendorWages' => $vendorWages,
        ]);
    }

    public function wShow(Request $request, $id)
    {
        $this->authorize('show', Transaction::class);
        // Show Monthly & Filtered Wages
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        if (! empty($dfrom) && ! empty($dto)) {
            $wages = $this->stockRepository->wagesInfoFilter($issue, $dfrom, $dto);
        } else {
            $wages = $this->stockRepository->wagesInfo($issue);
        }
        $totalWages = $wages->sum('total_wages');

        return view('wagesInfo', [
            'head' => $head,
            'issue' => $issue,
            'wages' => $wages,
            'totalWages' => $totalWages,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    public function ajaxPM(Request $request)
    {
        try {
            // Ajax Product Material
            $productId = $request->input('productId');

            // Validate productId
            if (empty($productId) || !is_numeric($productId)) {
                \Log::error('ajaxPM: Invalid productId provided', ['productId' => $productId]);
                return response()->json(['error' => 'Invalid product ID'], 400);
            }

            \Log::info('ajaxPM: Processing request', ['productId' => $productId]);

            $productMaterial = $this->productMaterialRepository->get($productId);
            $stockItem = $this->stockItemRepository->pStockGet($productId);

            \Log::info('ajaxPM: Data retrieved successfully', [
                'productId' => $productId,
                'materials_count' => count($productMaterial),
                'stock_items_count' => count($stockItem)
            ]);

            return response()->json([
                'materials' => $productMaterial,
                'stockItems' => $stockItem,
            ]);
        } catch (\Exception $e) {
            \Log::error('ajaxPM: Exception occurred', [
                'productId' => $request->input('productId'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function ajaxPT(Request $request)
    {
        // Ajax Product Type
        $orderId = $request->input('orderId');
        $orderItem = $this->orderItemRepository->get($orderId);

        return response()->json(['data' => $orderItem]);
    }

    public function ajaxPTStock(Request $request)
    {
        // Ajax Product Type - All products with stock (for issuance forms)
        $pstock = $this->stockItemRepository->pStock();

        // Group products by product_type_id to avoid duplicates
        $groupedProducts = $pstock->groupBy('product_type_id')->map(function ($group) {
            $first = $group->first();
            return [
                'product_type_id' => $first->product_type_id,
                'article_no' => $first->article_no,
                'name' => $first->name,
                'sname' => $first->sname, // size name
                'product_id' => $first->product_id,
            ];
        })->values();

        return response()->json(['data' => $groupedProducts]);
    }

    public function getAllProducts(Request $request)
    {
        // Ajax All Product Types - For Default Production
        $products = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as size', 'size.head_id', '=', 'product_types.size_id')
            ->where('product_types.product_type_status', '1')
            ->where('products.product_status', '1')
            ->select(
                'product_types.product_type_id',
                'products.article_no',
                'products.name',
                'size.name as hname'
            )
            ->orderBy('products.article_no')
            ->get();

        return response()->json(['data' => $products]);
    }

    public function ajaxIG(Request $request)
    {
        // Ajax Issuance Group
        $orderId = $request->input('orderId');
        // $orderId = '16';
        $igroup = $this->igroupRepository->igroups($orderId);

        return response()->json(['data' => $igroup]);
    }

    public function ajaxPS(Request $request)
    {
        // Ajax Product Stage
        $productId = $request->input('productId');
        $pstage = $this->headRepository->getStageAjax($productId);

        return response()->json(['data' => $pstage]);
    }

    public function ajaxPC(Request $request)
    {
        // Ajax Product Cost
        $productId = $request->input('productId');
        $productCost = $this->productCostRepository->pcost($productId);

        return response()->json(['data' => $productCost]);
    }

    public function ajaxMQty(Request $request)
    {
        // Ajax Material Qty Against Order
        $orderId = $request->input('orderId');
        $materialId = $request->input('materialId');
        $estimate = $this->orderItemRepository->estimateMaterial($orderId, $materialId);

        return response()->json(['data' => $estimate]);
    }

    public function ajaxAMQty(Request $request)
    {
        // Ajax Material Qty Against Article
        $orderId = $request->input('orderId');
        $productId = $request->input('productId');
        $materialId = $request->input('materialId');
        $estimate = $this->orderItemRepository->estimateAMaterial($orderId, $productId, $materialId);

        return response()->json(['data' => $estimate]);
    }

    public function ajaxATMQty(Request $request)
    {
        // Ajax Material Qty Against Article Type
        $orderId = $request->input('orderId');
        $productId = $request->input('productId');
        $materialId = $request->input('materialId');
        $estimate = $this->orderItemRepository->estimateATMaterial($orderId, $productId, $materialId);

        return response()->json(['data' => $estimate]);
    }

    // ==================================================
    // ==================== Issuance ====================
    // ==================================================

    public function issue()
    {
        $this->authorize('show', Stock::class);

        // All Issuance
        $issue = $this->stockRepository->issue();

        return view('issue', [
            'issue' => $issue,
        ]);
    }

    public function issue2()
    {
        $this->authorize('show', Stock::class);
        // All Issuance
        $issue = $this->stockRepository->issueMaterial();

        return view('issueMaterial', [
            'issue' => $issue,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Stock::class);
        // Add Issuance
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $order = $this->orderRepository->active();
        $count = $this->stockRepository->refNo();
        $stage = $this->headRepository->get('12');

        return view('addIssue', [
            'count' => $count,
            'order' => $order,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            'vendor' => $vendor,
            'stage' => $stage,
        ]);
    }

    public function gcreate()
    {
        $this->authorize('create', Stock::class);
        // Add Group Issuance
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $order = $this->orderRepository->active();
        $count = $this->stockRepository->refNo();
        $stage = $this->headRepository->get('12');
        $gstock = $this->stockItemRepository->gstock();
        $igroup = $this->igroupRepository->active();

        return view('addGIssue', [
            'count' => $count,
            'order' => $order,
            'igroup' => $igroup,
            'stock' => $stock,
            'pstock' => $pstock,
            'gstock' => $gstock,
            'employee' => $employee,
            'vendor' => $vendor,
            'stage' => $stage,
        ]);
    }

    public function create2()
    {
        $this->authorize('create', Stock::class);
        // Add Machine Material Issuance
        $employee = $this->employeeRepository->wages();
        // $vendor = $this->vendorRepository->worker();
        $material = $this->materialRepository->machine();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $machine = $this->machineRepository->all();
        $count = $this->stockRepository->refNo();

        return view('addIMM', [
            'count' => $count,
            'machine' => $machine,
            'material' => $material,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            // 'vendor' => $vendor,
        ]);
    }

    public function store(StockRequest $request)
    {
        // Store Issuance
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $ptid = $request->input('product_type_id');
        $quantities = $request->input('quantity');
        $mid = $request->input('material_id');
        $stages = $request->input('stage_id');
        $works = $request->input('work_logs');
        if ($request->has('issue_id')) { // Add Receive Issuance
            $this->stockRepository->update($request->input('issue_id'), ['stock_status' => $request->input('stock_status')]);
        }
        $getId = $this->stockRepository->store($validatedData);
        $this->storeSI($getId, $ptid, $mid, $quantities, $stages, $works, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'issuance', 'stocks', $getId);
            }
        }
        if ($request->has('machine_id')) {
            return redirect()->route('mstock.show', $getId)->with('success', 'Record Inserted Successfully');
        } elseif (! $request->has('issue_id')) {
            return redirect()->route('stock.show', $getId)->with('success', 'Record Inserted Successfully');
        } else {
            return redirect()->route('rstock.show', $getId)->with('success', 'Record Inserted Successfully');
        }
    }

    public function gstore(StockRequest $request)
    {
        // Store IGroup Issuance
        $validatedData = $request->validated();
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $getId = $this->stockRepository->store($validatedData);
        $igroups = $request->input('igroup_id');
        $quantities = $request->input('quantity');
        $this->storeIGroup($getId, $igroups, $quantities);

        return redirect()->route('stock.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Stock::class);
        // Show Issuance
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getAvg($id);
        $issueAll = $this->stockItemRepository->getAll($id);
        $issueSum = $this->stockItemRepository->getSum($id);
        $totalTimes = $this->stockItemRepository->times($id);

        return view('issueInfo', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
            'issueAll' => $issueAll,
            'issueSum' => $issueSum,
            'totalTimes' => $totalTimes,
            'count' => $totalTimes->count(),
        ]);
    }

    public function show2($id)
    {
        $this->authorize('show', Stock::class);
        // Show Machine Material Issuance
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getMM($id);
        $image = $this->imageRepository->image('stocks', $id);

        return view('issueMMInfo', [
            'image' => $image,
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    public function dailyIssue(Request $request)
    {
        $this->authorize('show', Stock::class);
        // Daily / Filtered Issuance
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $tname = $request->input('table_name');
        $oid = $request->input('order_id');
        $tid = $request->input('employee_id');
        $order = $this->orderRepository->all();
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        if (! empty($dfrom) && ! empty($dto)) {
            $issueItem = $this->stockItemRepository->dailyIssueFilter($dfrom, $dto, $tname, $tid, $oid);
        } else {
            $issueItem = $this->stockItemRepository->dailyIssue();
        }
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                $key = $item->product_type_id.'|'.$item->ifname;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }

        return view('dailyIssue', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oid' => $oid,
            'tid' => $tid,
            'tname' => $tname,
            'order' => $order,
            'vendor' => $vendor,
            'average' => $average,
            'employee' => $employee,
            'issueItem' => $issueItem,
        ]);
    }

    /**
     * Print daily issuance report
     */
    public function printDailyIssue(Request $request)
    {
        $this->authorize('show', Stock::class);
        // Daily / Filtered Issuance
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $tname = $request->input('table_name');
        $oid = $request->input('order_id');
        $tid = $request->input('employee_id');

        if (! empty($dfrom) && ! empty($dto)) {
            $issueItem = $this->stockItemRepository->dailyIssueFilter($dfrom, $dto, $tname, $tid, $oid);
        } else {
            $issueItem = $this->stockItemRepository->dailyIssue();
        }

        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                $key = $item->product_type_id.'|'.$item->ifname;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }

        return view('print.dailyIssue', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oid' => $oid,
            'tid' => $tid,
            'tname' => $tname,
            'average' => $average,
            'issueItem' => $issueItem,
        ]);
    }

    public function dailyReceive(Request $request)
    {
        $this->authorize('show', Stock::class);
        // Daily / Filtered Issuance
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $tname = $request->input('table_name');
        $oid = $request->input('order_id');
        $tid = $request->input('employee_id');
        $order = $this->orderRepository->all();
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        if (! empty($dfrom) && ! empty($dto)) {
            $issueItem = $this->stockItemRepository->dailyReceiveFilter($dfrom, $dto, $tname, $tid, $oid);
        } else {
            $issueItem = $this->stockItemRepository->dailyReceive();
        }

        return view('dailyReceive', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oid' => $oid,
            'tid' => $tid,
            'tname' => $tname,
            'order' => $order,
            'vendor' => $vendor,
            'employee' => $employee,
            'issueItem' => $issueItem,
        ]);
    }

    /**
     * Print daily receive report
     */
    public function printDailyReceive(Request $request)
    {
        $this->authorize('show', Stock::class);
        // Daily / Filtered Receiving
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $tname = $request->input('table_name');
        $oid = $request->input('order_id');
        $tid = $request->input('employee_id');

        if (! empty($dfrom) && ! empty($dto)) {
            $issueItem = $this->stockItemRepository->dailyReceiveFilter($dfrom, $dto, $tname, $tid, $oid);
        } else {
            $issueItem = $this->stockItemRepository->dailyReceive();
        }

        return view('print.dailyReceive', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oid' => $oid,
            'tid' => $tid,
            'tname' => $tname,
            'issueItem' => $issueItem,
        ]);
    }

    public function edit(Stock $id)
    {
        $this->authorize('edit', Stock::class);
        // Edit Issuance
        $issueItem = $this->stockItemRepository->get($id->stock_id);
        $employee = $this->employeeRepository->wages();
        $vendor = $this->vendorRepository->worker();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $order = $this->orderRepository->active();
        $stage = $this->headRepository->get('12');

        return view('editIssue', [
            'issue' => $id,
            'issueItem' => $issueItem,
            'order' => $order,
            'stage' => $stage,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            'vendor' => $vendor,
        ]);
    }

    public function edit2(Stock $id)
    {
        $this->authorize('edit', Stock::class);
        // Edit Machine Material Issuance
        $issueItem = $this->stockItemRepository->getMM($id->stock_id);
        $employee = $this->employeeRepository->wages();
        // $vendor = $this->vendorRepository->worker();
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();
        $material = $this->materialRepository->machine();
        $machine = $this->machineRepository->all();

        return view('editIMM', [
            'issue' => $id,
            'issueItem' => $issueItem,
            'machine' => $machine,
            'material' => $material,
            'stock' => $stock,
            'pstock' => $pstock,
            'employee' => $employee,
            // 'vendor' => $vendor,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Update Issuance
        if (array_sum($request->input('quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->stockRepository->update($id, $request->input());
        $issueId = $request->input('issue_id');
        if ($request->has('issue_id')) {
            $this->stockRepository->update($issueId, ['stock_status' => $request->input('stock_status')]);
        }
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'issuance', 'stocks', $id);
            }
        }
        $this->stockItemRepository->update($id, $request->input());
        if ($request->has('machine_id')) {
            return redirect()->route('mstock.show', $id)->with('success', 'Record Updated Successfully');
        } elseif (! $request->has('issue_id')) {
            return redirect()->route('stock.show', $id)->with('success', 'Record Updated Successfully');
        } else {
            return redirect()->route('rstock.show', $id)->with('success', 'Record Updated Successfully');
        }
    }

    // ==================================================
    // ================ Receive Issuance ================
    // ==================================================

    public function rIssue()
    {
        $this->authorize('create', Stock::class);
        // All Receive Issuance
        $head = $this->headRepository->get('12');
        $receive = $this->stockRepository->receive();
        $issue = $this->stockRepository->receiveIssue();
        $jobs = $issue->pluck('job_no')->filter()->unique();

        return view('receiveIssue', [
            'jobs' => $jobs,
            'head' => $head,
            'issue' => $issue,
            'receive' => $receive,
        ]);
    }

    public function rCreate($id)
    {
        $this->authorize('create', Stock::class);
        // Receive Issuance
        $head = $this->headRepository->get('12');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getAvg($id);
        $count = $this->stockRepository->refNo2($id);
        $rstock = $this->stockItemRepository->rstock($id);
        $issueSum = $this->stockItemRepository->getSum($id);
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                // $key = $item->article_no . '|' . $item->sname;
                $key = $item->product_type_id;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }

        return view('addReceiveIssue', [
            'count' => $count,
            'head' => $head,
            'issue' => $issue,
            'rstock' => $rstock,
            'average' => $average,
            'issueSum' => $issueSum,
            'issueItem' => $issueItem,
            'issueItemUnique' => $issueItem,
        ]);
    }

    public function rShow($id)
    {
        $this->authorize('show', Stock::class);
        // Show Receive Issuance
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);

        return view('receiveIssueInfo', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    /**
     * Print issuance information
     */
    public function printIssuance($id)
    {
        $this->authorize('show', Stock::class);
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getAvg($id);
        $issueAll = $this->stockItemRepository->getAll($id);
        $issueSum = $this->stockItemRepository->getSum($id);
        $totalTimes = $this->stockItemRepository->times($id);

        return view('print.issuance', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
            'issueAll' => $issueAll,
            'issueSum' => $issueSum,
            'totalTimes' => $totalTimes,
            'count' => $totalTimes->count(),
        ]);
    }

    /**
     * Print receive issuance information
     */
    public function printReceiveIssuance($id)
    {
        $this->authorize('show', Stock::class);
        $head = $this->headRepository->get('14');
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->get($id);

        return view('print.receive-issuance', [
            'head' => $head,
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    /**
     * Print machine material issuance information
     */
    public function printMachineIssuance($id)
    {
        $this->authorize('show', Stock::class);
        $issue = $this->stockRepository->get($id);
        $issueItem = $this->stockItemRepository->getMM($id);

        return view('print.machine-issuance', [
            'issue' => $issue,
            'issueItem' => $issueItem,
        ]);
    }

    public function rEdit($id)
    {
        $this->authorize('edit', Stock::class);
        // Edit Receive Issuance
        $head = $this->headRepository->get('12');
        $issue = $this->stockRepository->get($id);
        $date = $this->stockRepository->get($issue['issue_id']);
        $issueItem = $this->stockItemRepository->getAvg($issue['issue_id']);
        $rstock = $this->stockItemRepository->rstock($issue['issue_id']);
        $receiveItem = $this->stockItemRepository->get($id);
        $workLog = $this->stockItemRepository->workLog($id);
        $issueSum = $this->stockItemRepository->getSum($issue['issue_id']);
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                // $key = $item->article_no . '|' . $item->sname;
                $key = $item->product_type_id;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }

        return view('editReceiveIssue', [
            'head' => $head,
            'date' => $date,
            'issue' => $issue,
            'rstock' => $rstock,
            'average' => $average,
            'issueSum' => $issueSum,
            'issueItem' => $issueItem,
            'receiveItem' => $receiveItem,
            'issueItemUnique' => $issueItem,
            'workLog' => $workLog,
        ]);
    }

    public function destroy(Stock $stock)
    {
        $this->authorize('delete', Stock::class);
    }

    private function storeSI($getId, $ptids, $mids, $quantities, $stages, $works, $all)
    {
        // Store Issuance Items
        $tid = $all['employee_id'];
        $tname = $all['table_name'];
        foreach ($quantities as $key => $quantity) {
            $ptid = $ptids[$key] ?? 0;
            $mid = $mids[$key] ?? 0;
            $stage = $stages[$key] ?? 0;
            $work = $works[$key] ?? 0;
            $wages = $work ? $this->productCostRepository->wages($ptid, $work, $tid, $tname) : '0';
            $stockItem = [
                'stock_id' => $getId,
                'product_type_id' => $ptid,
                'material_id' => $mid,
                'quantity' => $quantity,
                'stage_id' => $stage,
                'work_logs' => $work,
                'work_wages' => $wages,
            ];
            $this->stockItemRepository->store($stockItem);
        }
    }

    private function storeIGroup($getId, $igroups, $quantities)
    {
        // Store Issuance Items
        foreach ($igroups as $key => $igroup) {
            $igroupItem = $this->igroupItemRepository->get($igroup);
            foreach ($igroupItem as $item) {
                $quantity = $quantities[$key] * $item->quantity ?? 0;
                $ptid = $item->product_type_id ?? 0;
                $mid = $item->material_id ?? 0;
                $stage = $item->stage_id ?? 0;
                $stockItem = [
                    'stock_id' => $getId,
                    'product_type_id' => $ptid,
                    'material_id' => $mid,
                    'quantity' => $quantity,
                    'stage_id' => $stage,
                    'work_logs' => '0',
                    'work_wages' => '0',
                ];
                $this->stockItemRepository->store($stockItem);
            }
        }
    }

    // ==================================================
    // ================ PTC (Process Travel Card) =======
    // ==================================================

    /**
     * Display list of all PTC records
     */
    public function ptcList()
    {
        $this->authorize('show', Stock::class);

        $ptcList = Stock::where('is_ptc_master', 1)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->leftJoin('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
            ->select(
                'stocks.*',
                'orders.job_no',
                'employees.name as employee_name',
                'vendors.fname as vendor_name',
                'stage_head.name as current_stage_name',
                'products.name as product_name',
                'products.article_no',
                'size_head.name as size_name'
            )
            ->groupBy('stocks.stock_id')
            ->orderBy('stocks.created_at', 'desc')
            ->get();

        $orders = $this->orderRepository->active();
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        return view('ptc', [
            'ptcList' => $ptcList,
            'orders' => $orders,
            'employees' => $employees,
            'vendors' => $vendors,
        ]);
    }

    /**
     * Show PTC creation form with pre-selected data from modal
     */
    public function ptcCreate(Request $request)
    {
        $this->authorize('create', Stock::class);

        $orderId = $request->get('order_id');
        $productTypeId = $request->get('product_type_id');
        $startStageId = $request->get('start_stage_id');
        $endStageId = $request->get('end_stage_id');

        // Get PTC reference number in new format: YYMMNNN
        $ptcNo = $this->stockRepository->ptcRefNo();

        // Get employees and vendors
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        // Get material stock for issuance
        $stock = $this->stockItemRepository->stock();

        // Get product stock (products in stages)
        $pstock = $this->stockItemRepository->pStock();

        // Get selected product details
        $product = null;
        $stages = collect();
        $startStage = null;
        $endStage = null;
        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $allStages = $this->headRepository->getByIds($stageIds);

                // Filter stages based on start/end selection
                $startIndex = 0;
                $endIndex = count($stageIds) - 1;

                foreach ($stageIds as $index => $stageId) {
                    if ($stageId == $startStageId) $startIndex = $index;
                    if ($stageId == $endStageId) $endIndex = $index;
                }

                $stages = $allStages->slice($startIndex, $endIndex - $startIndex + 1)->values();
                $startStage = $stages->first();
                $endStage = $stages->last();
            }
        }

        // Get order details if order-based
        $order = null;
        $orderQuantity = null;
        if ($orderId && $orderId != '0') {
            $order = $this->orderRepository->get($orderId);
            // Get order quantity for this product type
            $orderItem = \DB::table('order_items')
                ->where('order_id', $orderId)
                ->where('product_type_id', $productTypeId)
                ->first();
            $orderQuantity = $orderItem ? $orderItem->quantity : null;
        }

        // Get materials linked to this product type (BOM only)
        $materials = collect();
        if ($productTypeId && $product) {
            // Get from product_materials table
            $productMaterialIds = \DB::table('product_materials')
                ->where('product_type_id', $productTypeId)
                ->pluck('material_id')
                ->toArray();

            // Also get from products.material_id (pipe-separated)
            if ($product->material_id) {
                $productMaterialIdsFromProduct = array_filter(explode('|', $product->material_id));
                $productMaterialIds = array_unique(array_merge($productMaterialIds, $productMaterialIdsFromProduct));
            }

            if (!empty($productMaterialIds)) {
                $materials = \DB::table('materials')
                    ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
                    ->whereIn('materials.material_id', $productMaterialIds)
                    ->select('materials.*', 'uhead.name as uname')
                    ->get();
            }
        }

        return view('addPTC', [
            'ptcNo' => $ptcNo,
            'order_id' => $orderId,
            'product_type_id' => $productTypeId,
            'start_stage_id' => $startStageId,
            'end_stage_id' => $endStageId,
            'productType' => $product,
            'stages' => $stages,
            'startStage' => $startStage,
            'endStage' => $endStage,
            'order' => $order,
            'orderQuantity' => $orderQuantity,
            'employees' => $employees,
            'vendors' => $vendors,
            'materials' => $materials,
            'stock' => $stock,
            'pstock' => $pstock,
        ]);
    }

    /**
     * Store a new PTC record
     */
    public function ptcStore(Request $request)
    {
        $this->authorize('create', Stock::class);

        $request->validate([
            'stock_no' => 'required|string',
            'stock_date' => 'required|date',
            'ptc_product_type_id' => 'required',
            'start_stage_id' => 'required',
            'end_stage_id' => 'required',
        ]);

        $productTypeId = $request->input('ptc_product_type_id');
        $startStageId = $request->input('start_stage_id');
        $endStageId = $request->input('end_stage_id');

        // Get all stages for the product
        $product = \DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->where('product_type_id', $productTypeId)
            ->first();

        $stageIds = $product ? explode('|', $product->stage_ids) : [];

        // Find start and end indices
        $startIndex = array_search($startStageId, $stageIds);
        $endIndex = array_search($endStageId, $stageIds);

        // Determine next stage
        $nextStageId = null;
        if ($startIndex !== false && $startIndex < $endIndex) {
            $nextStageId = $stageIds[$startIndex + 1] ?? null;
        }

        // Create PTC master record
        // Store product_quantity in description with format [QTY:X]
        $productQty = $request->input('product_quantity', 1);
        $description = '[QTY:' . $productQty . ']' . ($request->input('description') ? ' ' . $request->input('description') : '');

        $ptcData = [
            'stock_no' => $request->input('stock_no'),
            'order_id' => $request->input('order_id', 0),
            'table_name' => $request->input('table_name', 'employee'),
            'employee_id' => $request->input('employee_id', 0),
            'stock_type' => 2, // Issuance
            'stock_date' => $request->input('stock_date'),
            'stock_status' => Stock::STATUS_PTC_IN_PROGRESS,
            'is_ptc_master' => 1,
            'current_stage_id' => $startStageId,
            'next_stage_id' => $nextStageId,
            'issue_for' => $startStageId,
            'description' => $description,
            'created_by' => auth()->id(),
        ];

        $ptcId = $this->stockRepository->store($ptcData);

        // Store stock items (materials/products issued)
        $quantities = $request->input('quantity', []);
        $materialIds = $request->input('material_id', []);
        $productTypeIds = $request->input('product_type_id', []);
        $stageIdsInput = $request->input('stage_id', []);

        foreach ($quantities as $key => $quantity) {
            if ($quantity > 0) {
                // Determine if this is a material or product issuance
                $itemProductTypeId = $productTypeIds[$key] ?? 0;
                $itemMaterialId = $materialIds[$key] ?? 0;
                $itemStageId = $stageIdsInput[$key] ?? 0;

                // For material issuance, use the PTC's product_type_id
                // For product issuance, use the item's product_type_id
                $stockItem = [
                    'stock_id' => $ptcId,
                    'product_type_id' => ($itemProductTypeId > 0) ? $itemProductTypeId : $productTypeId,
                    'material_id' => $itemMaterialId,
                    'quantity' => $quantity,
                    'stage_id' => ($itemStageId > 0) ? $itemStageId : $startStageId,
                    'work_logs' => '0',
                    'work_wages' => '0',
                    'created_by' => auth()->id(),
                ];
                $this->stockItemRepository->store($stockItem);
            }
        }

        return redirect()->route('ptc.show', $ptcId)->with('success', 'PTC Created Successfully');
    }

    /**
     * Display PTC details
     */
    public function ptcShow($id)
    {
        $this->authorize('show', Stock::class);

        $ptc = Stock::where('stock_id', $id)
            ->where('is_ptc_master', 1)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'orders.job_no', 'orders.order_id as order_id_ref', 'employees.name as employee_name',
                'vendors.fname as vendor_name', 'stage_head.name as current_stage_name')
            ->first();

        if (!$ptc) {
            return redirect()->route('ptc')->with('fails', 'PTC record not found');
        }

        // Get PTC items
        $ptcItems = $this->stockItemRepository->get($id);

        // Get product details
        $productTypeId = $ptcItems->first()->product_type_id ?? null;
        $product = null;
        $stages = collect();
        $orderQuantity = null;

        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $stages = $this->headRepository->getByIds($stageIds);
            }

            // Get order quantity if PTC is linked to an order
            if ($ptc->order_id) {
                $orderItem = \DB::table('order_items')
                    ->where('order_id', $ptc->order_id)
                    ->where('product_type_id', $productTypeId)
                    ->first();
                $orderQuantity = $orderItem ? $orderItem->quantity : null;
            }
        }

        // Get all issuance records for this PTC (including initial PTC record)
        // First get subsequent issuances (where ptc_id = $id and stock_type = 2)
        $subsequentIssuances = Stock::where('ptc_id', $id)
            ->where('stock_type', 2)
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.issue_for')
            ->select('stocks.*', 'employees.name as employee_name', 'vendors.fname as vendor_name', 'stage_head.name as stage_name')
            ->get();

        // Get the initial PTC record (is_ptc_master = 1)
        $initialIssuance = Stock::where('stocks.stock_id', $id)
            ->where('is_ptc_master', 1)
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'employees.name as employee_name', 'vendors.fname as vendor_name', 'stage_head.name as stage_name')
            ->get();

        // Merge initial issuance with subsequent issuances (initial first, then sorted by date desc)
        $issuances = $initialIssuance->merge($subsequentIssuances)->sortByDesc('stock_date')->values();

        // Get all receiving records for this PTC (separate list)
        $receivings = Stock::where('ptc_id', $id)
            ->where('stock_type', 1)
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'employees.name as employee_name', 'vendors.fname as vendor_name', 'stage_head.name as stage_name')
            ->orderBy('stocks.stock_date', 'desc')
            ->orderBy('stocks.stock_id', 'desc')
            ->get();

        // Group receivings by issue_id for quick lookup (to show which issuances have been received)
        $receivingsByIssueId = $receivings->groupBy('issue_id');

        // Get initial PTC record (the master record) for movement history
        $initialPtc = Stock::where('stocks.stock_id', $id)
            ->where('stocks.is_ptc_master', 1)
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->leftJoin('heads as issue_stage', 'issue_stage.head_id', '=', 'stocks.issue_for')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->select('stocks.*', 'stage_head.name as stage_name', 'issue_stage.name as issue_stage_name',
                'employees.name as employee_name', 'vendors.fname as vendor_name')
            ->get();

        // Get stage movement history (all records linked to this PTC)
        $subsequentMovements = Stock::where('ptc_id', $id)
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->leftJoin('heads as issue_stage', 'issue_stage.head_id', '=', 'stocks.issue_for')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->select('stocks.*', 'stage_head.name as stage_name', 'issue_stage.name as issue_stage_name',
                'employees.name as employee_name', 'vendors.fname as vendor_name')
            ->orderBy('stocks.stock_date', 'asc')
            ->orderBy('stocks.stock_id', 'asc')
            ->get();

        // Merge initial PTC record with subsequent movements
        $movements = $initialPtc->merge($subsequentMovements);

        // Get items for each movement
        $movementItems = [];
        foreach ($movements as $movement) {
            $movementItems[$movement->stock_id] = \DB::table('stock_items')
                ->where('stock_items.stock_id', $movement->stock_id)
                ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
                ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
                ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
                ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
                ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
                ->select('stock_items.*', 'materials.name as material_name', 'products.name as product_name',
                    'shead.name as size_name', 'sthead.name as stage_name')
                ->get();
        }

        // Also get items for the initial PTC record (for View modal)
        $movementItems[$id] = \DB::table('stock_items')
            ->where('stock_items.stock_id', $id)
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->select('stock_items.*', 'materials.name as material_name', 'products.name as product_name',
                'shead.name as size_name', 'sthead.name as stage_name')
            ->get();

        // Get last received items (most recent receive record)
        $lastReceive = $movements->where('stock_type', 1)->last();
        $lastReceivedItems = $lastReceive ? ($movementItems[$lastReceive->stock_id] ?? collect()) : collect();

        // Find current stage index
        $currentStageIndex = 0;
        foreach ($stages as $index => $stage) {
            if ($stage->head_id == $ptc->current_stage_id) {
                $currentStageIndex = $index;
                break;
            }
        }

        // Check if PTC can be edited (only if at first stage and no movements)
        $canEdit = $movements->where('stock_type', 1)->isEmpty();

        // Check if at final stage
        $isFinalStage = $ptc->stock_status == Stock::STATUS_PTC_COMPLETED ||
            ($stages->isNotEmpty() && $currentStageIndex >= $stages->count() - 1);

        return view('ptcInfo', [
            'ptc' => $ptc,
            'ptcItems' => $ptcItems,
            'product' => $product,
            'stages' => $stages,
            'movements' => $movements,
            'movementItems' => $movementItems,
            'lastReceive' => $lastReceive,
            'lastReceivedItems' => $lastReceivedItems,
            'currentStageIndex' => $currentStageIndex,
            'canEdit' => $canEdit,
            'isFinalStage' => $isFinalStage,
            'orderQuantity' => $orderQuantity,
            'issuances' => $issuances,
            'receivings' => $receivings,
            'receivingsByIssueId' => $receivingsByIssueId,
        ]);
    }

    /**
     * Show PTC edit form
     */
    public function ptcEdit($id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc) {
            return redirect()->route('ptc')->with('fails', 'PTC record not found');
        }

        // Check if PTC can be edited
        $movements = Stock::where('ptc_id', $id)->where('stock_type', 1)->count();
        if ($movements > 0) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot edit PTC after stage movement has occurred');
        }

        // Get PTC items
        $ptcItems = $this->stockItemRepository->get($id);

        // Get employees and vendors
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        // Get material stock
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();

        // Get product details
        $productTypeId = $ptcItems->first()->product_type_id ?? null;
        $product = null;
        $stages = collect();
        $startStage = null;
        $endStage = null;

        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $stages = $this->headRepository->getByIds($stageIds);
                $startStage = $stages->first();
                $endStage = $stages->last();
            }
        }

        // Get materials
        $materials = $this->materialRepository->all();

        // Get PTC with joined data
        $ptcWithData = Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->select('stocks.*', 'orders.job_no', 'employees.name as employee_name', 'vendors.fname as vendor_name')
            ->first();

        return view('editPTC', [
            'ptc' => $ptcWithData,
            'stockItems' => $ptcItems,
            'product' => $product,
            'stages' => $stages,
            'startStage' => $startStage,
            'endStage' => $endStage,
            'employees' => $employees,
            'vendors' => $vendors,
            'materials' => $materials,
            'stock' => $stock,
            'pstock' => $pstock,
        ]);
    }

    /**
     * Update PTC record
     */
    public function ptcUpdate(Request $request, $id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc) {
            return redirect()->route('ptc')->with('fails', 'PTC record not found');
        }

        // Check if PTC can be edited
        $movements = Stock::where('ptc_id', $id)->where('stock_type', 1)->count();
        if ($movements > 0) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot edit PTC after stage movement');
        }

        // Update PTC record
        $ptc->update([
            'stock_date' => $request->input('stock_date', $ptc->stock_date),
            'table_name' => $request->input('table_name', $ptc->table_name),
            'employee_id' => $request->input('employee_id', $ptc->employee_id),
            'description' => $request->input('description'),
        ]);

        // Update stock items
        $this->stockItemRepository->update($id, $request->input());

        return redirect()->route('ptc.show', $id)->with('success', 'PTC Updated Successfully');
    }

    /**
     * Show PTC Issuance Form (separate page for issuing materials/products)
     */
    public function ptcIssueForm($id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot issue - PTC not found or already completed');
        }

        // Get PTC items
        $ptcItems = $this->stockItemRepository->get($id);

        // Get employees and vendors
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        // Get material stock for issuance
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();

        // Get product details
        $productTypeId = $ptcItems->first()->product_type_id ?? null;
        $product = null;
        $stages = collect();

        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $stages = $this->headRepository->getByIds($stageIds);
            }
        }

        // Get materials linked to this product type (BOM only)
        $materials = collect();
        if ($productTypeId) {
            // Get from product_materials table
            $productMaterialIds = \DB::table('product_materials')
                ->where('product_type_id', $productTypeId)
                ->pluck('material_id')
                ->toArray();

            // Also get from products.material_id (pipe-separated)
            if ($product && $product->material_id) {
                $productMaterialIdsFromProduct = array_filter(explode('|', $product->material_id));
                $productMaterialIds = array_unique(array_merge($productMaterialIds, $productMaterialIdsFromProduct));
            }

            if (!empty($productMaterialIds)) {
                $materials = \DB::table('materials')
                    ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
                    ->whereIn('materials.material_id', $productMaterialIds)
                    ->select('materials.*', 'uhead.name as unit')
                    ->get();
            }
        }

        // Get PTC with joined data
        $ptcWithData = Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('heads as current_stage', 'current_stage.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'orders.job_no', 'current_stage.name as current_stage_name')
            ->first();

        // Get all issuance records for this PTC
        $issuances = Stock::where('ptc_id', $id)
            ->where('stock_type', 2)
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.issue_for')
            ->select('stocks.*', 'employees.name as employee_name', 'vendors.fname as vendor_name', 'stage_head.name as stage_name')
            ->orderBy('stocks.stock_date', 'desc')
            ->get();

        return view('ptcIssuance', [
            'ptc' => $ptcWithData,
            'ptcItems' => $ptcItems,
            'product' => $product,
            'stages' => $stages,
            'employees' => $employees,
            'vendors' => $vendors,
            'materials' => $materials,
            'stock' => $stock,
            'pstock' => $pstock,
            'issuances' => $issuances,
        ]);
    }

    /**
     * Store PTC Issuance
     */
    public function ptcIssueStore(Request $request, $id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot issue - PTC completed');
        }

        // Get product type ID from PTC items
        $ptcItems = $this->stockItemRepository->get($id);
        $productTypeId = $ptcItems->first()->product_type_id ?? null;

        // Get quantities
        $quantities = $request->input('quantity', []);
        $hasItems = !empty(array_filter($quantities, fn($q) => $q > 0));

        if (!$hasItems) {
            return redirect()->back()->with('fails', 'No items to issue');
        }

        // Create issue record with new format: sequential number per PTC
        $issueNo = $this->stockRepository->issueRefNo($id);
        $issueData = [
            'ptc_id' => $id,
            'stock_no' => $issueNo, // Store only sequence number, display adds prefix
            'order_id' => $ptc->order_id,
            'table_name' => $request->input('table_name', 'employee'),
            'employee_id' => $request->input('employee_id', 0),
            'stock_type' => 2, // Issue
            'stock_date' => $request->input('stock_date', date('Y-m-d')),
            'stock_status' => Stock::STATUS_PTC_IN_PROGRESS,
            'current_stage_id' => $ptc->current_stage_id,
            'issue_for' => $request->input('issue_for', $ptc->current_stage_id),
            'description' => $request->input('description'),
            'created_by' => auth()->id(),
        ];

        $issueId = $this->stockRepository->store($issueData);

        // Store issue items
        $materialIds = $request->input('material_id', []);
        $productTypeIds = $request->input('product_type_id', []);
        $stageIds = $request->input('stage_id', []);

        foreach ($quantities as $key => $quantity) {
            if ($quantity > 0) {
                $itemProductTypeId = $productTypeIds[$key] ?? 0;
                $itemMaterialId = $materialIds[$key] ?? 0;
                $itemStageId = $stageIds[$key] ?? 0;

                $stockItem = [
                    'stock_id' => $issueId,
                    'product_type_id' => ($itemProductTypeId > 0) ? $itemProductTypeId : $productTypeId,
                    'material_id' => $itemMaterialId,
                    'quantity' => $quantity,
                    'stage_id' => ($itemStageId > 0) ? $itemStageId : $ptc->current_stage_id,
                    'work_logs' => '0',
                    'work_wages' => '0',
                    'created_by' => auth()->id(),
                ];
                $this->stockItemRepository->store($stockItem);
            }
        }

        return redirect()->route('ptc.show', $id)->with('success', 'Issuance created successfully');
    }

    /**
     * Show PTC Issuance Edit Form
     */
    public function ptcIssueEdit($id, $issuanceId)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();
        $issuance = Stock::where('stock_id', $issuanceId)->where('ptc_id', $id)->first();

        if (!$ptc || !$issuance) {
            return redirect()->route('ptc.show', $id)->with('fails', 'PTC or Issuance not found');
        }

        // Check if issuance has any receivings (cannot edit if received)
        $hasReceivings = Stock::where('issue_id', $issuanceId)->where('stock_type', 1)->exists();
        if ($hasReceivings) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot edit issuance after receiving has occurred');
        }

        // Get issuance items
        $issuanceItems = $this->stockItemRepository->get($issuanceId);

        // Get employees and vendors
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        // Get product details
        $ptcItems = $this->stockItemRepository->get($id);
        $productTypeId = $ptcItems->first()->product_type_id ?? null;
        $product = null;
        $stages = collect();

        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $stages = $this->headRepository->getByIds($stageIds);
            }
        }

        // Get PTC with joined data
        $ptcWithData = Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('heads as current_stage', 'current_stage.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'orders.job_no', 'current_stage.name as current_stage_name')
            ->first();

        return view('editPtcIssuance', [
            'ptc' => $ptcWithData,
            'issuance' => $issuance,
            'issuanceItems' => $issuanceItems,
            'product' => $product,
            'stages' => $stages,
            'employees' => $employees,
            'vendors' => $vendors,
        ]);
    }

    /**
     * Update PTC Issuance
     */
    public function ptcIssueUpdate(Request $request, $id, $issuanceId)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();
        $issuance = Stock::where('stock_id', $issuanceId)->where('ptc_id', $id)->first();

        if (!$ptc || !$issuance) {
            return redirect()->route('ptc.show', $id)->with('fails', 'PTC or Issuance not found');
        }

        // Check if issuance has any receivings (cannot edit if received)
        $hasReceivings = Stock::where('issue_id', $issuanceId)->where('stock_type', 1)->exists();
        if ($hasReceivings) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot edit issuance after receiving has occurred');
        }

        // Update issuance record
        $issuance->update([
            'stock_date' => $request->input('stock_date', $issuance->stock_date),
            'table_name' => $request->input('table_name', $issuance->table_name),
            'employee_id' => $request->input('employee_id', $issuance->employee_id),
            'issue_for' => $request->input('issue_for', $issuance->issue_for),
            'description' => $request->input('description', $issuance->description),
        ]);

        // Update item quantities
        $quantities = $request->input('quantity', []);
        $stockItemIds = $request->input('stock_item_id', []);

        foreach ($stockItemIds as $key => $stockItemId) {
            $quantity = $quantities[$key] ?? 0;
            if ($quantity > 0) {
                \DB::table('stock_items')
                    ->where('stock_item_id', $stockItemId)
                    ->update(['quantity' => $quantity]);
            }
        }

        return redirect()->route('ptc.show', $id)->with('success', 'Issuance updated successfully');
    }

    /**
     * Show PTC Receiving Form (separate page for receiving materials/products)
     */
    public function ptcReceiveForm($id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot receive - PTC not found or already completed');
        }

        // Get PTC items
        $ptcItems = $this->stockItemRepository->get($id);

        // Get employees and vendors
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        // Get product details
        $productTypeId = $ptcItems->first()->product_type_id ?? null;
        $product = null;
        $stages = collect();

        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $stages = $this->headRepository->getByIds($stageIds);
            }
        }

        // Get PTC with joined data
        $ptcWithData = Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->select('stocks.*', 'orders.job_no')
            ->first();

        // Get current stage
        $currentStageId = $ptc->current_stage_id;

        // Get all issued items for this PTC (initial + all issuances)
        $issueRecordIds = Stock::where('ptc_id', $id)
            ->where('stock_type', 2)
            ->pluck('stock_id')
            ->toArray();
        $issueRecordIds[] = $id; // Include initial PTC

        $issueItem = \DB::table('stock_items')
            ->whereIn('stock_items.stock_id', $issueRecordIds)
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('product_materials', function ($join) {
                $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                    ->on('product_materials.material_id', '=', 'stock_items.material_id');
            })
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->select('stock_items.*', 'products.product_id', 'products.name as pname', 'products.article_no',
                'materials.material_id', 'materials.name', 'uhead.name as uname', 'shead.name as sname',
                'sthead.name as stage', 'puhead.name as puname', 'product_materials.quantity as pqty')
            ->orderBy('products.product_id')
            ->get();

        // Get already received items
        $rstock = \DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stocks.ptc_id', $id)
            ->where('stocks.stock_type', 1)
            ->select(
                'stock_items.product_type_id',
                'stock_items.material_id',
                \DB::raw('SUM(stock_items.quantity) AS rqty')
            )
            ->groupBy('stock_items.product_type_id', 'stock_items.material_id')
            ->get();

        // Get receive totals summary
        $issueSum = \DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stocks.ptc_id', $id)
            ->where('stocks.stock_type', 1)
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->groupBy('stock_items.product_type_id', 'stock_items.material_id', 'stock_items.stage_id')
            ->selectRaw('stock_items.product_type_id, stock_items.material_id, stock_items.stage_id,
                SUM(stock_items.quantity) as total_quantity, products.article_no, products.name as pname,
                materials.name, shead.name as sname, sthead.name as stage, uhead.name as uname, puhead.name as puname')
            ->get();

        // Calculate average for materials
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                $key = $item->product_type_id;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }

        // Get all receiving records for this PTC
        $receivings = Stock::where('ptc_id', $id)
            ->where('stock_type', 1)
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'employees.name as employee_name', 'vendors.fname as vendor_name', 'stage_head.name as stage_name')
            ->orderBy('stocks.stock_date', 'desc')
            ->get();

        return view('ptcReceiving', [
            'ptc' => $ptcWithData,
            'ptcItems' => $ptcItems,
            'product' => $product,
            'stages' => $stages,
            'employees' => $employees,
            'vendors' => $vendors,
            'issueItem' => $issueItem,
            'rstock' => $rstock,
            'issueSum' => $issueSum,
            'average' => $average,
            'receivings' => $receivings,
            'issuance' => null,
            'issuanceSeqNo' => null,
        ]);
    }

    /**
     * Show PTC Receiving Form for a specific issuance
     */
    public function ptcReceiveFromIssuance($id, $issuanceId)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot receive - PTC not found or already completed');
        }

        // Get the specific issuance record
        // For initial PTC issuance (is_ptc_master=1), the stock_id equals issuanceId
        // For subsequent issuances, ptc_id equals the PTC's stock_id
        $issuance = Stock::where('stock_id', $issuanceId)
            ->where(function($q) use ($id) {
                $q->where('ptc_id', $id)
                  ->orWhere(function($q2) use ($id) {
                      // Initial PTC record: stock_id = id AND is_ptc_master = 1
                      $q2->where('stock_id', $id)->where('is_ptc_master', 1);
                  });
            })
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->select('stocks.*', 'employees.name as employee_name', 'employees.employee_no',
                'vendors.fname as vendor_name', 'vendors.vendor_no')
            ->first();

        if (!$issuance) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Issuance record not found');
        }

        // Get items from this specific issuance
        $issueItem = \DB::table('stock_items')
            ->where('stock_items.stock_id', $issuanceId)
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('product_materials', function ($join) {
                $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                    ->on('product_materials.material_id', '=', 'stock_items.material_id');
            })
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->select('stock_items.*', 'products.product_id', 'products.name as pname', 'products.article_no',
                'materials.material_id', 'materials.name', 'uhead.name as uname', 'shead.name as sname',
                'sthead.name as stage', 'puhead.name as puname', 'product_materials.quantity as pqty')
            ->orderBy('products.product_id')
            ->get();

        // Get PTC items
        $ptcItems = $this->stockItemRepository->get($id);
        $productTypeId = $ptcItems->first()->product_type_id ?? null;

        // Get product details
        $product = null;
        $stages = collect();

        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $stages = $this->headRepository->getByIds($stageIds);
            }
        }

        // Get employees and vendors
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        // Get PTC with joined data (including current stage name)
        $ptcWithData = Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('heads as current_stage', 'current_stage.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'orders.job_no', 'current_stage.name as current_stage_name')
            ->first();

        // Get already received items for this issuance
        $rstock = \DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stocks.issue_id', $issuanceId)
            ->where('stocks.stock_type', 1)
            ->select(
                'stock_items.product_type_id',
                'stock_items.material_id',
                \DB::raw('SUM(stock_items.quantity) AS rqty')
            )
            ->groupBy('stock_items.product_type_id', 'stock_items.material_id')
            ->get();

        // Get receive totals summary
        $issueSum = \DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stocks.issue_id', $issuanceId)
            ->where('stocks.stock_type', 1)
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->groupBy('stock_items.product_type_id', 'stock_items.material_id', 'stock_items.stage_id')
            ->selectRaw('stock_items.product_type_id, stock_items.material_id, stock_items.stage_id,
                SUM(stock_items.quantity) as total_quantity, products.article_no, products.name as pname,
                materials.name, shead.name as sname, sthead.name as stage, uhead.name as uname, puhead.name as puname')
            ->get();

        // Calculate average for materials
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                $key = $item->product_type_id;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }

        // Get all receiving records for this issuance
        $receivings = Stock::where('issue_id', $issuanceId)
            ->where('stock_type', 1)
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as stage_head', 'stage_head.head_id', '=', 'stocks.current_stage_id')
            ->select('stocks.*', 'employees.name as employee_name', 'vendors.fname as vendor_name', 'stage_head.name as stage_name')
            ->orderBy('stocks.stock_date', 'desc')
            ->get();

        // Get issuance sequence number for display
        $issuanceSeqNo = $this->stockRepository->getIssuanceSeqNo($id, $issuanceId);

        return view('ptcReceiving', [
            'ptc' => $ptcWithData,
            'ptcItems' => $ptcItems,
            'product' => $product,
            'stages' => $stages,
            'employees' => $employees,
            'vendors' => $vendors,
            'issueItem' => $issueItem,
            'rstock' => $rstock,
            'issueSum' => $issueSum,
            'average' => $average,
            'receivings' => $receivings,
            'issuance' => $issuance,
            'issuanceSeqNo' => $issuanceSeqNo,
        ]);
    }

    /**
     * Store PTC Receiving
     */
    public function ptcReceiveStore(Request $request, $id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot receive - PTC completed');
        }

        // Get product type ID from PTC items
        $ptcItems = $this->stockItemRepository->get($id);
        $productTypeId = $ptcItems->first()->product_type_id ?? null;

        // Get receive quantities
        $rQuantities = $request->input('r_quantity', []);
        $hasItems = !empty(array_filter($rQuantities, fn($q) => $q > 0));

        if (!$hasItems) {
            return redirect()->back()->with('fails', 'No items to receive');
        }

        // Create receive record with new format: sequential number per issuance
        // issue_id links to specific issuance if receiving against it, otherwise to PTC master
        $issuanceId = $request->input('issue_id', $id);
        $receiveNo = $this->stockRepository->receiveRefNo($issuanceId);
        $receiveData = [
            'issue_id' => $issuanceId,
            'ptc_id' => $id,
            'stock_no' => $receiveNo, // Store only sequence number, display adds prefix
            'order_id' => $ptc->order_id,
            'table_name' => $request->input('table_name', 'employee'),
            'employee_id' => $request->input('employee_id', 0),
            'stock_type' => 1, // Receive
            'stock_date' => $request->input('stock_date', date('Y-m-d')),
            'stock_status' => 1,
            'current_stage_id' => $request->input('receive_stage_id', $ptc->current_stage_id),
            'description' => $request->input('description'),
            'created_by' => auth()->id(),
        ];

        $receiveId = $this->stockRepository->store($receiveData);

        // Store receive items
        $rMaterialIds = $request->input('r_material_id', []);
        $rProductTypeIds = $request->input('r_product_type_id', []);
        $rStageIds = $request->input('r_stage_id', []);
        $rWorkLogs = $request->input('r_work_logs', []);

        // Get employee/vendor info for wages calculation
        $employeeId = $request->input('employee_id', 0);
        $tableName = $request->input('table_name', 'employee');

        foreach ($rQuantities as $key => $quantity) {
            if ($quantity > 0) {
                $itemProductTypeId = $rProductTypeIds[$key] ?? 0;
                $itemMaterialId = $rMaterialIds[$key] ?? 0;
                $itemStageId = $rStageIds[$key] ?? 0;
                $workLog = $rWorkLogs[$key] ?? '0';

                // Calculate wages from work_logs using the same logic as regular receiving
                $wages = '0';
                if ($workLog && $workLog != '0') {
                    $ptId = ($itemProductTypeId > 0) ? $itemProductTypeId : $productTypeId;
                    $wages = $this->productCostRepository->wages($ptId, $workLog, $employeeId, $tableName);
                    if (empty($wages)) {
                        $wages = '0';
                    }
                }

                $stockItem = [
                    'stock_id' => $receiveId,
                    'product_type_id' => ($itemProductTypeId > 0) ? $itemProductTypeId : $productTypeId,
                    'material_id' => $itemMaterialId,
                    'quantity' => $quantity,
                    'stage_id' => ($itemStageId > 0) ? $itemStageId : $ptc->current_stage_id,
                    'work_logs' => $workLog,
                    'work_wages' => $wages,
                    'created_by' => auth()->id(),
                ];
                $this->stockItemRepository->store($stockItem);
            }
        }

        return redirect()->route('ptc.show', $id)->with('success', 'Receiving recorded successfully');
    }

    /**
     * Move PTC to next stage (manual stage advancement)
     */
    public function ptcNextStage(Request $request, $id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot advance - PTC not found or already completed');
        }

        // Get product stages
        $ptcItems = $this->stockItemRepository->get($id);
        $productTypeId = $ptcItems->first()->product_type_id ?? null;

        if (!$productTypeId) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot find product for PTC');
        }

        $product = \DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->where('product_type_id', $productTypeId)
            ->first();

        if (!$product || !$product->stage_ids) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot find stages for product');
        }

        $stageIds = explode('|', $product->stage_ids);
        $currentStageIndex = array_search($ptc->current_stage_id, $stageIds);

        if ($currentStageIndex === false || $currentStageIndex >= count($stageIds) - 1) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Already at final stage - use Close PTC instead');
        }

        // Move to next stage
        $nextStageId = $stageIds[$currentStageIndex + 1];
        $nextNextStageId = isset($stageIds[$currentStageIndex + 2]) ? $stageIds[$currentStageIndex + 2] : null;

        $ptc->update([
            'current_stage_id' => $nextStageId,
            'next_stage_id' => $nextNextStageId,
        ]);

        // Get stage names for success message
        $nextStageName = \DB::table('heads')->where('head_id', $nextStageId)->value('name');

        return redirect()->route('ptc.show', $id)->with('success', "PTC moved to stage: {$nextStageName}");
    }

    /**
     * Close/Complete PTC manually
     */
    public function ptcClose(Request $request, $id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc) {
            return redirect()->route('ptc.show', $id)->with('fails', 'PTC not found');
        }

        if ($ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'PTC is already completed');
        }

        // Mark PTC as completed
        $ptc->update([
            'stock_status' => Stock::STATUS_PTC_COMPLETED,
            'next_stage_id' => null,
        ]);

        return redirect()->route('ptc.show', $id)->with('success', 'PTC has been closed/completed successfully');
    }

    /**
     * Show move to next stage form
     */
    public function ptcMoveStageForm($id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot move stage - PTC not found or already completed');
        }

        // Get PTC items
        $ptcItems = $this->stockItemRepository->get($id);

        // Get employees and vendors
        $employees = $this->employeeRepository->wages();
        $vendors = $this->vendorRepository->worker();

        // Get material stock for issue section
        $stock = $this->stockItemRepository->stock();
        $pstock = $this->stockItemRepository->pStock();

        // Get product details and stages
        $productTypeId = $ptcItems->first()->product_type_id ?? null;
        $product = null;
        $stages = collect();
        $currentStageIndex = 0;

        if ($productTypeId) {
            $product = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_type_id', $productTypeId)
                ->select('product_types.*', 'products.*', 'size_head.name as size_name')
                ->first();

            if ($product && $product->stage_ids) {
                $stageIds = explode('|', $product->stage_ids);
                $stages = $this->headRepository->getByIds($stageIds);

                foreach ($stages as $index => $stage) {
                    if ($stage->head_id == $ptc->current_stage_id) {
                        $currentStageIndex = $index;
                        break;
                    }
                }
            }
        }

        $currentStage = $stages[$currentStageIndex] ?? null;
        $nextStage = $stages[$currentStageIndex + 1] ?? null;
        $isFinalStage = $currentStageIndex >= $stages->count() - 1;

        // Get materials
        $materials = $this->materialRepository->all();

        // Get PTC with joined data
        $ptcWithData = Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->select('stocks.*', 'orders.job_no')
            ->first();

        // === RECEIVE SECTION DATA ===
        // Get ALL issued items for the current stage (from initial PTC and subsequent stage movements)
        // For PTC, issued items for current stage come from:
        // 1. Initial PTC master (if current stage is first stage)
        // 2. Issue records from previous stage movements where issue_for = current_stage_id
        $currentStageId = $ptc->current_stage_id;

        // Get all issue records for this PTC that target the current stage
        $issueRecordIds = Stock::where('ptc_id', $id)
            ->where('stock_type', 2) // Issue type
            ->where('issue_for', $currentStageId)
            ->pluck('stock_id')
            ->toArray();

        // For first stage, include the initial PTC master record
        if ($currentStageIndex == 0) {
            $issueRecordIds[] = $id;
        }

        // Get issued items from all relevant issue records
        $issueItem = \DB::table('stock_items')
            ->whereIn('stock_items.stock_id', $issueRecordIds)
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('product_materials', function ($join) {
                $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                    ->on('product_materials.material_id', '=', 'stock_items.material_id');
            })
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->select('stock_items.*', 'products.product_id', 'products.name as pname', 'products.article_no', 'materials.material_id', 'materials.name',
                'uhead.name as uname', 'shead.name as sname', 'sthead.name as stage', 'puhead.name as puname', 'product_materials.quantity as pqty')
            ->orderBy('products.product_id')
            ->get();

        // Get already received items for the current stage
        // These are receive records (stock_type=1) with ptc_id = this PTC and current_stage_id = current stage
        $rstock = \DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stocks.ptc_id', $id)
            ->where('stocks.stock_type', 1) // Receive type
            ->where('stocks.current_stage_id', $currentStageId)
            ->leftJoin('product_materials', function ($join) {
                $join->on('product_materials.product_type_id', '=', 'stock_items.product_type_id')
                    ->whereColumn('product_materials.material_id', 'stock_items.material_id');
            })
            ->select(
                'stock_items.product_type_id',
                'stock_items.material_id',
                \DB::raw('SUM(stock_items.quantity) AS rqty')
            )
            ->groupBy('stock_items.product_type_id', 'stock_items.material_id')
            ->get();

        // Get receive totals for display (with all required fields)
        $issueSum = \DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stocks.ptc_id', $id)
            ->where('stocks.stock_type', 1) // Receive type
            ->where('stocks.current_stage_id', $currentStageId)
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->groupBy('stock_items.product_type_id', 'stock_items.material_id', 'stock_items.stage_id')
            ->selectRaw('stock_items.product_type_id, stock_items.material_id, stock_items.stage_id,
                SUM(stock_items.quantity) as total_quantity, products.article_no, products.name as pname,
                materials.name, shead.name as sname, sthead.name as stage, uhead.name as uname, puhead.name as puname')
            ->get();

        // Calculate average for materials
        $average = [];
        foreach ($issueItem as $item) {
            if ($item->material_id) {
                $key = $item->product_type_id;
                $currentAvg = $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0';
                $average[$key]['min_avg'] = isset($average[$key]) ? min($average[$key]['min_avg'], $currentAvg) : $currentAvg;
            }
        }

        return view('ptcMoveStage', [
            'ptc' => $ptcWithData,
            'ptcItems' => $ptcItems,
            'product' => $product,
            'stages' => $stages,
            'currentStage' => $currentStage,
            'nextStage' => $nextStage,
            'currentStageIndex' => $currentStageIndex,
            'isFinalStage' => $isFinalStage,
            'employees' => $employees,
            'vendors' => $vendors,
            'materials' => $materials,
            'stock' => $stock,
            'pstock' => $pstock,
            // Receive section data
            'issueItem' => $issueItem,
            'issueItemUnique' => $issueItem,
            'rstock' => $rstock,
            'issueSum' => $issueSum,
            'average' => $average,
        ]);
    }

    /**
     * Process stage movement
     */
    public function ptcMoveStage(Request $request, $id)
    {
        $this->authorize('edit', Stock::class);

        $ptc = Stock::where('stock_id', $id)->where('is_ptc_master', 1)->first();

        if (!$ptc || $ptc->stock_status == Stock::STATUS_PTC_COMPLETED) {
            return redirect()->route('ptc.show', $id)->with('fails', 'Cannot move stage');
        }

        // Get product stages
        $ptcItems = $this->stockItemRepository->get($id);
        $productTypeId = $ptcItems->first()->product_type_id ?? null;

        $product = \DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->where('product_type_id', $productTypeId)
            ->first();

        $stageIds = $product ? explode('|', $product->stage_ids) : [];
        $currentIndex = array_search($ptc->current_stage_id, $stageIds);
        $isFinalStage = $currentIndex !== false && $currentIndex >= count($stageIds) - 1;
        $nextIndex = $currentIndex + 1;
        $nextStageId = $stageIds[$nextIndex] ?? null;

        // === SECTION 1: Create receive record for current stage ===
        $rQuantities = $request->input('r_quantity', []);
        $hasReceiveItems = !empty(array_filter($rQuantities, fn($q) => $q > 0));

        if ($hasReceiveItems) {
            $receiveNo = $this->stockRepository->receiveRefNo($id);
            $receiveData = [
                'issue_id' => $id,
                'ptc_id' => $id,
                'stock_no' => $receiveNo, // Store only sequence number
                'order_id' => $ptc->order_id,
                'table_name' => $request->input('table_name', 'employee'),
                'employee_id' => $request->input('employee_id', 0),
                'stock_type' => 1, // Receive
                'stock_date' => $request->input('stock_date', date('Y-m-d')),
                'stock_status' => 1,
                'current_stage_id' => $ptc->current_stage_id,
                'description' => $request->input('description'),
                'created_by' => auth()->id(),
            ];

            $receiveId = $this->stockRepository->store($receiveData);

            // Store receive items
            $rMaterialIds = $request->input('r_material_id', []);
            $rProductTypeIds = $request->input('r_product_type_id', []);
            $rStageIds = $request->input('r_stage_id', []);
            $rWorkLogs = $request->input('r_work_logs', []);

            foreach ($rQuantities as $key => $quantity) {
                if ($quantity > 0) {
                    $itemProductTypeId = $rProductTypeIds[$key] ?? 0;
                    $itemMaterialId = $rMaterialIds[$key] ?? 0;
                    $itemStageId = $rStageIds[$key] ?? 0;

                    $stockItem = [
                        'stock_id' => $receiveId,
                        'product_type_id' => ($itemProductTypeId > 0) ? $itemProductTypeId : $productTypeId,
                        'material_id' => $itemMaterialId,
                        'quantity' => $quantity,
                        'stage_id' => ($itemStageId > 0) ? $itemStageId : $ptc->current_stage_id,
                        'work_logs' => $rWorkLogs[$key] ?? '0',
                        'work_wages' => '0',
                        'created_by' => auth()->id(),
                    ];
                    $this->stockItemRepository->store($stockItem);
                }
            }
        }

        // === SECTION 2: Create issue record for next stage ===
        $iQuantities = $request->input('quantity', []);
        $hasIssueItems = !empty(array_filter($iQuantities, fn($q) => $q > 0));

        if ($hasIssueItems && $nextStageId) {
            $issueNo = $this->stockRepository->issueRefNo($id);
            $issueData = [
                'ptc_id' => $id,
                'stock_no' => $issueNo, // Store only sequence number
                'order_id' => $ptc->order_id,
                'table_name' => $request->input('table_name', 'employee'),
                'employee_id' => $request->input('employee_id', 0),
                'stock_type' => 2, // Issue
                'stock_date' => $request->input('stock_date', date('Y-m-d')),
                'stock_status' => Stock::STATUS_PTC_IN_PROGRESS,
                'current_stage_id' => $nextStageId,
                'issue_for' => $nextStageId,
                'description' => $request->input('description'),
                'created_by' => auth()->id(),
            ];

            $issueId = $this->stockRepository->store($issueData);

            // Store issue items
            $iMaterialIds = $request->input('material_id', []);
            $iProductTypeIds = $request->input('product_type_id', []);
            $iStageIds = $request->input('stage_id', []);

            foreach ($iQuantities as $key => $quantity) {
                if ($quantity > 0) {
                    $itemProductTypeId = $iProductTypeIds[$key] ?? 0;
                    $itemMaterialId = $iMaterialIds[$key] ?? 0;
                    $itemStageId = $iStageIds[$key] ?? 0;

                    $stockItem = [
                        'stock_id' => $issueId,
                        'product_type_id' => ($itemProductTypeId > 0) ? $itemProductTypeId : $productTypeId,
                        'material_id' => $itemMaterialId,
                        'quantity' => $quantity,
                        'stage_id' => ($itemStageId > 0) ? $itemStageId : $nextStageId,
                        'work_logs' => '0',
                        'work_wages' => '0',
                        'created_by' => auth()->id(),
                    ];
                    $this->stockItemRepository->store($stockItem);
                }
            }
        }

        // Update PTC master record
        if ($isFinalStage) {
            $ptc->update([
                'stock_status' => Stock::STATUS_PTC_COMPLETED,
                'current_stage_id' => null,
                'next_stage_id' => null,
            ]);
            return redirect()->route('ptc.show', $id)->with('success', 'PTC Completed Successfully');
        } else {
            $followingStageId = $stageIds[$nextIndex + 1] ?? null;

            $ptc->update([
                'current_stage_id' => $nextStageId,
                'next_stage_id' => $followingStageId,
                'issue_for' => $nextStageId,
            ]);

            return redirect()->route('ptc.show', $id)->with('success', 'Moved to next stage successfully');
        }
    }

    /**
     * AJAX: Get products for PTC (with size)
     */
    public function ajaxPtcProducts(Request $request)
    {
        $orderId = $request->get('order_id');

        if ($orderId && $orderId != '0') {
            // Get products from order
            $products = \DB::table('order_items')
                ->join('product_types', 'product_types.product_type_id', '=', 'order_items.product_type_id')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('order_items.order_id', $orderId)
                ->select(
                    'product_types.product_type_id',
                    'products.name as product_name',
                    'products.article_no',
                    'size_head.name as size_name',
                    'products.stage_ids'
                )
                ->distinct()
                ->get();
        } else {
            // Get all active products
            $products = \DB::table('product_types')
                ->join('products', 'products.product_id', '=', 'product_types.product_id')
                ->join('heads as size_head', 'size_head.head_id', '=', 'product_types.size_id')
                ->where('product_types.product_type_status', 1)
                ->select(
                    'product_types.product_type_id',
                    'products.name as product_name',
                    'products.article_no',
                    'size_head.name as size_name',
                    'products.stage_ids'
                )
                ->get();
        }

        return response()->json(['data' => $products]);
    }

    /**
     * AJAX: Get stages for a product
     */
    public function ajaxPtcStages(Request $request)
    {
        $productTypeId = $request->get('product_type_id');

        if (!$productTypeId) {
            return response()->json(['data' => []]);
        }

        $stages = $this->headRepository->getStageAjax($productTypeId);

        return response()->json(['data' => $stages]);
    }
}
