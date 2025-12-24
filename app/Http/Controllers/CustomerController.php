<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\CustomerRequest;
use App\Repositories\CustomerRepository;
use App\Repositories\TransactionRepository;

class CustomerController extends Controller
{
    protected $headRepository;

    protected $imageRepository;

    protected $customerRepository;

    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        CustomerRepository $customerRepository,
        TransactionRepository $transactionRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->customerRepository = $customerRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index()
    {
        $this->authorize('access', Customer::class);
        $customer = $this->customerRepository->all();

        return view('customer', [
            'customer' => $customer,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Customer::class);
        $count = $this->customerRepository->refNo();
        $country = $this->headRepository->get('15');
        $currency = $this->headRepository->get('16');

        return view('addCustomer', [
            'count' => $count,
            'country' => $country,
            'currency' => $currency,
        ]);
    }

    public function store(CustomerRequest $request)
    {
        $validatedData = $request->validated();
        $getId = $this->customerRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'customer', 'customers', $getId);
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'customer',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->store($transaction);

        return redirect()->route('customer.show', $getId)->with('success', 'Record Inserted Successfully');
    }
    
    public function show($id)
    {
        $this->authorize('show', Customer::class);
        $customer = $this->customerRepository->get($id);
        $image = $this->imageRepository->image('customers', $id);

        return view('customerInfo', [
            'customer' => $customer,
            'image' => $image,
        ]);
    }

    /**
     * Print customer information
     */
    public function printCustomer($id)
    {
        $this->authorize('show', Customer::class);
        $customer = $this->customerRepository->get($id);
        $image = $this->imageRepository->image('customers', $id);

        return view('print.customer', [
            'customer' => $customer,
            'image' => $image,
        ]);
    }

    public function detail(Request $request, $id)
    {
        $this->authorize('show', Customer::class);
        $this->authorize('show', Transaction::class);
        $customer = $this->customerRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->cDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $detail = $this->transactionRepository->cDetail($id);
        }

        // For customer ledger (receivable account):
        // - Deliveries are stored in debit (customer owes us)
        // - Payments are stored in debit (cash inflow to us)
        // Balance = Total Deliveries - Total Payments
        $totalDeliveries = $detail->whereNotIn('transaction_type', ['orderPayment'])->sum('debit');
        $totalPayments = $detail->where('transaction_type', 'orderPayment')->sum('debit');
        $balance = $oBalance + $totalDeliveries - $totalPayments + $cBalance;

        return view('customerDetail', [
            'customer' => $customer,
            'detail' => $detail,
            'balance' => $balance,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    /**
     * Show customer detail in customer currency (USD)
     */
    public function detail2(Request $request, $id)
    {
        $this->authorize('show', Customer::class);
        $this->authorize('show', Transaction::class);
        $customer = $this->customerRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $ccOBalance = 0; // Opening Balance in customer currency
        $ccCBalance = 0; // Closing Balance in customer currency

        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->cDetailCCFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $ccOBalance = $all['opening_balance'];
            $ccCBalance = $all['closing_balance'];
        } else {
            $detail = $this->transactionRepository->cDetailCC($id);
        }

        // For customer currency ledger:
        // - Deliveries use price2 (customer currency) from order items
        // - Payments use cc_amount from transactions
        // Balance = Total Deliveries (price2) - Total Payments (cc_amount)
        $totalDeliveries = $detail->whereNotIn('transaction_type', ['orderPayment'])->sum('price2');
        $totalPayments = $detail->where('transaction_type', 'orderPayment')->sum('cc_amount');
        $ccBalance = $ccOBalance + $totalDeliveries - $totalPayments + $ccCBalance;

        return view('customerDetail2', [
            'customer' => $customer,
            'detail' => $detail,
            'ccBalance' => $ccBalance,
            'ccOBalance' => $ccOBalance,
            'ccCBalance' => $ccCBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    /**
     * Print customer ledger
     */
    public function printCustomerLedger(Request $request, $id)
    {
        $this->authorize('show', Customer::class);
        $this->authorize('show', Transaction::class);
        $customer = $this->customerRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->cDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $detail = $this->transactionRepository->cDetail($id);
        }

        // For customer ledger (receivable account):
        // - Deliveries are stored in debit (customer owes us)
        // - Payments are stored in debit (cash inflow to us)
        // Balance = Total Deliveries - Total Payments
        $totalDeliveries = $detail->whereNotIn('transaction_type', ['orderPayment'])->sum('debit');
        $totalPayments = $detail->where('transaction_type', 'orderPayment')->sum('debit');
        $balance = $oBalance + $totalDeliveries - $totalPayments + $cBalance;

        return view('print.customer-ledger', [
            'customer' => $customer,
            'detail' => $detail,
            'balance' => $balance,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    public function edit(Customer $id)
    {
        $this->authorize('edit', Customer::class);
        $country = $this->headRepository->get('15');
        $currency = $this->headRepository->get('16');

        // Fetch opening balance transaction
        $openingBalance = $this->transactionRepository->getOB($id->customer_id, 'customer');

        return view('editCustomer', [
            'customer' => $id,
            'country' => $country,
            'currency' => $currency,
            'openingBalance' => $openingBalance,
        ]);
    }

    public function update(Request $request, $id)
    {
        $getId = $this->customerRepository->update($id, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'customer', 'customers', $getId);
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'customer',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->updateOB($getId, 'customer', $transaction);

        return redirect()->route('customer.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', Customer::class);
    }
}
