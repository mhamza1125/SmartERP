<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\VendorRequest;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\VendorRepository;
use App\Repositories\ProductRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\TransactionRepository;

class VendorController extends Controller
{
    protected $headRepository;

    protected $imageRepository;

    protected $vendorRepository;

    protected $transactionRepository;

    protected $materialRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        VendorRepository $vendorRepository,
        ProductRepository $productRepository,
        TransactionRepository $transactionRepository,
        MaterialRepository $materialRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->vendorRepository = $vendorRepository;
        $this->productRepository = $productRepository;
        $this->transactionRepository = $transactionRepository;
        $this->materialRepository = $materialRepository;
    }

    public function index()
    {
        $this->authorize('access', Vendor::class);
        // Not Used Gone to Two Separate Pages
        $vendor = $this->vendorRepository->all();

        return view('vendor', [
            'vendor' => $vendor,
        ]);
    }

    public function vendor()
    {
        $this->authorize('access', Vendor::class);
        $vendor = $this->vendorRepository->vendorWithBalance();

        return view('vendor', [
            'vendor' => $vendor,
        ]);
    }

    public function contractor()
    {
        $this->authorize('contractors_access', Vendor::class);
        $vendor = $this->vendorRepository->worker();

        return view('contractor', [
            'vendor' => $vendor,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Vendor::class);
        $vendorType = $this->headRepository->get('11');
        $city = $this->headRepository->get('8');
        $material = $this->materialRepository->all();
        $product = $this->productRepository->all();
        $count = $this->vendorRepository->refNo();

        return view('addVendor', [
            'city' => $city,
            'count' => $count,
            'material' => $material,
            'product' => $product,
            'vendorType' => $vendorType,
        ]);
    }

    public function create2() // For Contractor
    {
        $this->authorize('contractors_create', Vendor::class);
        $city = $this->headRepository->get('8');
        $count = $this->vendorRepository->refNo2();

        return view('addContractor', [
            'city' => $city,
            'count' => $count,
        ]);
    }

    public function store(VendorRequest $request)
    {
        $validatedData = $request->validated();
        $materialIds = $request->input('material_id');
        $validatedData['material_id'] = $materialIds ? implode('|', $materialIds) : '0';
        $productIds = $request->input('product_id');
        $validatedData['product_id'] = $productIds ? implode('|', $productIds) : '0';
        $vendorTypeIds = $request->input('vendor_type_id');
        $validatedData['vendor_type_id'] = $vendorTypeIds ? implode('|', $vendorTypeIds) : '';
        $getId = $this->vendorRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'vendor', 'vendors', $getId);
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'vendor',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->store($transaction);

        return redirect()->route('vendor.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Vendor::class);
        $vendor = $this->vendorRepository->get($id);
        $image = $this->imageRepository->image('vendors', $id);
        $material = $this->materialRepository->getMaterial($vendor['material_id']);
        $product = $this->productRepository->getProduct($vendor['product_id'] ?? '0');

        return view('vendorInfo', [
            'material' => $material,
            'product' => $product,
            'vendor' => $vendor,
            'image' => $image,
        ]);
    }

    public function show2($id) // For Contractor
    {
        $this->authorize('contractors_show', Vendor::class);
        $vendor = $this->vendorRepository->get($id);
        $image = $this->imageRepository->image('vendors', $id);

        return view('contractorInfo', [
            'vendor' => $vendor,
            'image' => $image,
        ]);
    }

    public function detail(Request $request, $id)
    {
        $vendor = $this->vendorRepository->get($id);

        if ($vendor['vendor_type']) {
            $this->authorize('contractors_show', Vendor::class);
        } else {
            $this->authorize('show', Vendor::class);
        }
        $this->authorize('show', Transaction::class);

        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance

        // Get transaction data
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->vDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $detail = $this->transactionRepository->vDetail($id);
        }

        // Get wages data for contractors (vendor_type = 1)
        if ($vendor['vendor_type'] == 1) {
            $wages = $this->getContractorWages($id, $dfrom, $dto);
            // Merge wages with transactions and sort by date
            $detail = $this->mergeWagesWithTransactions($detail, $wages);
        }

        $totalCredit = $detail->where('transaction_type', '!=', 'wages')->sum('credit');
        $totalDebit = $detail->whereIn('transaction_type', ['wages'])->sum('debit') +
                     $detail->where('transaction_type', '!=', 'wages')->sum('debit');
        $balance = $totalCredit - $totalDebit + $oBalance + $cBalance;

        return view('vendorDetail', [
            'vendor' => $vendor,
            'detail' => $detail,
            'balance' => $balance,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    /**
     * Get wages data for a contractor
     */
    private function getContractorWages($vendorId, $dfrom = null, $dto = null)
    {
        $query = \DB::table('stocks')
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->where('stocks.employee_id', $vendorId)
            ->where('stocks.table_name', 'vendor')
            ->where('stocks.stock_type', '1') // StockIN
            ->where('stock_items.work_wages', '!=', '0')
            ->select(
                'stocks.stock_id',
                'stocks.stock_no',
                'stocks.stock_date as transaction_date',
                'stocks.created_at',
                'stock_items.quantity',
                'stock_items.work_wages',
                'products.article_no',
                'shead.name as size_name',
                'sthead.name as stage_name'
            );

        if (!empty($dfrom) && !empty($dto)) {
            $query->whereBetween('stocks.stock_date', [$dfrom, $dto]);
        }

        $wagesData = $query->orderBy('stocks.stock_date')->get();

        // Group wages by stock_no (receive/issuance number) and calculate totals
        $groupedWages = $wagesData->groupBy('stock_no')->map(function ($wageGroup, $stockNo) {
            $totalWages = 0;
            $wageDetails = [];
            $firstWage = $wageGroup->first();

            foreach ($wageGroup as $wage) {
                $workWages = explode('|', $wage->work_wages);
                $itemWages = 0;
                foreach ($workWages as $wageAmount) {
                    $itemWages += (int) $wageAmount * $wage->quantity;
                }
                $totalWages += $itemWages;

                // Store individual wage details for modal display
                $wageDetails[] = [
                    'article_no' => $wage->article_no,
                    'size_name' => $wage->size_name,
                    'stage_name' => $wage->stage_name,
                    'quantity' => $wage->quantity,
                    'wages' => $itemWages,
                ];
            }

            return (object) [
                'transaction_id' => null,
                'transaction_type' => 'wages',
                'transaction_date' => $firstWage->transaction_date,
                'created_at' => $firstWage->created_at,
                'timestamp' => $firstWage->created_at,
                'debit' => $totalWages, // Total wages for this receive/issuance
                'credit' => null,
                'description' => "Wages for {$stockNo} (" . count($wageGroup) . " items)",
                'payee_id' => null,
                'bank_id' => null,
                'order_id' => null,
                // Additional fields for enhanced display
                'stock_id' => $firstWage->stock_id,
                'stock_no' => $stockNo,
                'wage_details' => $wageDetails, // For modal popup
                'item_count' => count($wageGroup),
            ];
        });

        return $groupedWages->values(); // Reset array keys
    }

    /**
     * Merge wages data with transaction data and sort chronologically
     */
    private function mergeWagesWithTransactions($transactions, $wages)
    {
        // Convert transactions to collection if it isn't already
        if (!$transactions instanceof \Illuminate\Support\Collection) {
            $transactions = collect($transactions);
        }

        // Merge and sort by timestamp/created_at
        $merged = $transactions->concat($wages)->sortBy(function ($item) {
            return $item->timestamp ?? $item->created_at;
        });

        return $merged;
    }

    public function edit(Vendor $id)
    {
        $this->authorize('edit', Vendor::class);
        $vendorType = $this->headRepository->get('11');
        $city = $this->headRepository->get('8');
        $material = $this->materialRepository->all();
        $product = $this->productRepository->all();
        $vmaterial = $this->materialRepository->getMaterial($id['material_id']);
        $vproduct = $this->productRepository->getProduct($id['product_id'] ?? '0');

        return view('editVendor', [
            'city' => $city,
            'vendor' => $id,
            'material' => $material,
            'product' => $product,
            'vmaterial' => $vmaterial,
            'vproduct' => $vproduct,
            'vendorType' => $vendorType,
        ]);
    }

    public function edit2(Vendor $id) // For Contractor
    {
        $this->authorize('contractors_edit', Vendor::class);
        $city = $this->headRepository->get('8');

        return view('editContractor', [
            'city' => $city,
            'vendor' => $id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $materialIds = $request->input('material_id');
        $materialIds = $materialIds ? implode('|', $materialIds) : '0';
        $productIds = $request->input('product_id');
        $productIds = $productIds ? implode('|', $productIds) : '0';
        $vendorTypeIds = $request->input('vendor_type_id');
        $vendorTypeIds = $vendorTypeIds ? implode('|', $vendorTypeIds) : '';
        $request->merge(['material_id' => $materialIds, 'product_id' => $productIds, 'vendor_type_id' => $vendorTypeIds]);
        $getId = $this->vendorRepository->update($id, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'vendor', 'vendors', $getId);
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'vendor',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->updateOB($getId, 'vendor', $transaction);

        return redirect()->route('vendor.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(Vendor $vendor)
    {
        $this->authorize('delete', Vendor::class);
    }
}
