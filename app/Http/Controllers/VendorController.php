<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Requests\VendorRequest;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\VendorRepository;
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
        TransactionRepository $transactionRepository,
        MaterialRepository $materialRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->vendorRepository = $vendorRepository;
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
        $count = $this->vendorRepository->refNo();

        return view('addVendor', [
            'city' => $city,
            'count' => $count,
            'material' => $material,
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

        return view('vendorInfo', [
            'material' => $material,
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
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->vDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $detail = $this->transactionRepository->vDetail($id);
        }
        $totalCredit = $detail->where('transaction_type', '!=', 'wages')->sum('credit');
        $totalDebit = $detail->where('transaction_type', '!=', 'wages')->sum('debit');
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

    public function edit(Vendor $id)
    {
        $this->authorize('edit', Vendor::class);
        $vendorType = $this->headRepository->get('11');
        $city = $this->headRepository->get('8');
        $material = $this->materialRepository->all();
        $vmaterial = $this->materialRepository->getMaterial($id['material_id']);

        return view('editVendor', [
            'city' => $city,
            'vendor' => $id,
            'material' => $material,
            'vmaterial' => $vmaterial,
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
        $request->merge(['material_id' => $materialIds]);
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
