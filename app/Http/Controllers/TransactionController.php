<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Repositories\BankRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\VendorRepository;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $bankRepository;

    protected $customerRepository;

    protected $orderRepository;

    protected $headRepository;

    protected $imageRepository;

    protected $vendorRepository;

    protected $purchaseRepository;

    protected $employeeRepository;

    protected $transactionRepository;

    public function __construct(
        BankRepository $bankRepository,
        CustomerRepository $customerRepository,
        OrderRepository $orderRepository,
        HeadRepository $headRepository,
        ImageRepository $imageRepository,
        VendorRepository $vendorRepository,
        PurchaseRepository $purchaseRepository,
        EmployeeRepository $employeeRepository,
        TransactionRepository $transactionRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->bankRepository = $bankRepository;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->headRepository = $headRepository;
        $this->imageRepository = $imageRepository;
        $this->vendorRepository = $vendorRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->employeeRepository = $employeeRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('access', Transaction::class);

        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');

        if (!empty($dfrom) && !empty($dto)) {
            $transaction = $this->transactionRepository->filterByDate($dfrom, $dto);
        } else {
            $transaction = $this->transactionRepository->all();
        }

        return view('transaction', [
            'transaction' => $transaction,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    public function oPayment(Request $request)
    {
        $this->authorize('access', Transaction::class);

        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $customer_id = $request->input('customer_id');

        if (!empty($dfrom) && !empty($dto) || !empty($customer_id)) {
            $transaction = $this->transactionRepository->oPaymentFilter($dfrom, $dto, $customer_id);
        } else {
            $transaction = $this->transactionRepository->oPayment();
        }

        $customers = $this->customerRepository->all();

        return view('oPayment', [
            'transaction' => $transaction,
            'customers' => $customers,
            'dfrom' => $dfrom,
            'dto' => $dto,
            'customer_id' => $customer_id,
        ]);
    }

    public function ePayment(Request $request)
    {
        $this->authorize('access', Transaction::class);

        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $employee_id = $request->input('employee_id');

        if (!empty($dfrom) && !empty($dto) || !empty($employee_id)) {
            $transaction = $this->transactionRepository->ePaymentFilter($dfrom, $dto, $employee_id);
        } else {
            $transaction = $this->transactionRepository->ePayment();
        }

        $employees = $this->employeeRepository->all();

        return view('ePayment', [
            'transaction' => $transaction,
            'employees' => $employees,
            'dfrom' => $dfrom,
            'dto' => $dto,
            'employee_id' => $employee_id,
        ]);
    }

    public function vPayment(Request $request)
    {
        $this->authorize('access', Transaction::class);

        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $vendor_id = $request->input('vendor_id');

        if (!empty($dfrom) && !empty($dto) || !empty($vendor_id)) {
            $transaction = $this->transactionRepository->vPaymentFilter($dfrom, $dto, $vendor_id);
        } else {
            $transaction = $this->transactionRepository->vPayment();
        }

        $vendors = $this->vendorRepository->all()->where('vendor_type', '0');

        return view('vPayment', [
            'transaction' => $transaction,
            'vendors' => $vendors,
            'dfrom' => $dfrom,
            'dto' => $dto,
            'vendor_id' => $vendor_id,
        ]);
    }

    public function cPayment(Request $request)
    {
        $this->authorize('access', Transaction::class);

        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $contractor_id = $request->input('contractor_id');

        if (!empty($dfrom) && !empty($dto) || !empty($contractor_id)) {
            $transaction = $this->transactionRepository->cPaymentFilter($dfrom, $dto, $contractor_id);
        } else {
            $transaction = $this->transactionRepository->cPayment();
        }

        $contractors = $this->vendorRepository->all()->where('vendor_type', '1');

        return view('cPayment', [
            'transaction' => $transaction,
            'contractors' => $contractors,
            'dfrom' => $dfrom,
            'dto' => $dto,
            'contractor_id' => $contractor_id,
        ]);
    }

    public function expense(Request $request)
    {
        $this->authorize('access', Transaction::class);

        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $head_id = $request->input('head_id');

        if (!empty($dfrom) && !empty($dto) || !empty($head_id)) {
            $transaction = $this->transactionRepository->expenseFilter($dfrom, $dto, $head_id);
        } else {
            $transaction = $this->transactionRepository->expense();
        }

        $heads = $this->headRepository->get('7');

        return view('expense', [
            'transaction' => $transaction,
            'heads' => $heads,
            'dfrom' => $dfrom,
            'dto' => $dto,
            'head_id' => $head_id,
        ]);
    }

    public function bankBalance()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->bankBalance();

        return view('bankBalance', [
            'transaction' => $transaction,
        ]);
    }

    public function cashBalance(Request $request)
    {
        $this->authorize('access', Transaction::class);
        $balance = $this->transactionRepository->cashBalance();
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->cashTransactionFilter($dfrom, $dto);
            $transaction = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $transaction = $this->transactionRepository->cashTransaction();
        }

        return view('cashBalance', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'cashBalance' => $balance,
            'transaction' => $transaction,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Transaction::class);
    }

    public function createBRS()
    {
        $this->authorize('create', Transaction::class);
        $bank = $this->bankRepository->self();

        return view('addBRS', [
            'bank' => $bank,
        ]);
    }

    public function createEPayment()
    {
        $this->authorize('create', Transaction::class);
        $bank = $this->bankRepository->self();
        $employee = $this->employeeRepository->all();

        return view('addPayEmployee', [
            'bank' => $bank,
            'employee' => $employee,
        ]);
    }

    public function createVPayment()
    {
        $this->authorize('create', Transaction::class);
        $bank = $this->bankRepository->self();
        $vendor = $this->vendorRepository->vendor();

        return view('addPayVendor', [
            'bank' => $bank,
            'vendor' => $vendor,
        ]);
    }

    public function createCPayment()
    {
        $this->authorize('create', Transaction::class);
        $bank = $this->bankRepository->self();
        $worker = $this->vendorRepository->worker();

        return view('addPayContractor', [
            'bank' => $bank,
            'worker' => $worker,
        ]);
    }

    public function createExpense()
    {
        $this->authorize('create', Transaction::class);
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');

        return view('addExpense', [
            'bank' => $bank,
            'expense' => $expense,
        ]);
    }

    public function createOPayment()
    {
        $this->authorize('create', Transaction::class);
        $customer = $this->customerRepository->all();
        $bank = $this->bankRepository->self();
        $order = $this->orderRepository->all();

        return view('addPayOrder', [
            'bank' => $bank,
            'order' => $order,
            'customer' => $customer,
        ]);
    }

    public function createCashBalance()
    {
        $this->authorize('create', Transaction::class);
        // Cash Balance
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');

        return view('addExpense', [
            'bank' => $bank,
            'expense' => $expense,
        ]);
    }

    public function generalVoucher()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->generalVoucher();

        return view('generalVoucher', [
            'transaction' => $transaction,
        ]);
    }

    public function createGeneralVoucher()
    {
        $this->authorize('create', Transaction::class);
        $vendor = $this->vendorRepository->vendor();
        $contractor = $this->vendorRepository->worker();
        $employee = $this->employeeRepository->all();
        $customer = $this->customerRepository->all();

        return view('addGeneralVoucher', [
            'vendor' => $vendor,
            'contractor' => $contractor,
            'employee' => $employee,
            'customer' => $customer,
        ]);
    }

    public function ajaxBank(Request $request)
    {
        $table = $request->input('table');
        $tableId = $request->input('tableId');
        $bank = $this->bankRepository->getBank($table, $tableId);

        return response()->json(['data' => $bank]);
    }

    public function ajaxBalance(Request $request)
    {
        $table = $request->input('table');
        $tableId = $request->input('tableId');

        if (!$table || !$tableId) {
            return response()->json(['balance' => 0, 'error' => 'Invalid parameters']);
        }

        try {
            $balance = 0;

            switch ($table) {
                case 'customer':
                    $detail = $this->transactionRepository->cDetail($tableId);
                    // For customer ledger:
                    // - Deliveries are stored in debit column (customer owes us)
                    // - Payments are stored in debit column (cash inflow to us)
                    // Balance = Total Deliveries - Total Payments
                    $totalDeliveries = $detail->whereNotIn('transaction_type', ['orderPayment'])->sum('debit');
                    $totalPayments = $detail->where('transaction_type', 'orderPayment')->sum('debit');
                    $balance = $totalDeliveries - $totalPayments; // Positive = customer owes us
                    break;

                case 'employee':
                    $detail = $this->transactionRepository->eDetail($tableId);
                    $totalCredit = $detail->whereIn('transaction_type', ['advance', 'receiveAdvance', 'openingBalance'])->sum('credit');
                    $totalDebit = $detail->whereIn('transaction_type', ['advance', 'receiveAdvance', 'openingBalance', 'wages'])->sum('debit');
                    $balance = $totalCredit - $totalDebit; // Positive = we owe employee
                    break;

                case 'vendor':
                    $detail = $this->transactionRepository->vDetail($tableId);
                    // For vendor/contractor ledger (liability account):
                    // DB debit (purchases/work) = increases liability = ADD to balance
                    // DB credit (payments/returns) = decreases liability = SUBTRACT from balance
                    // Include ALL transaction types - no filtering
                    $totalDebit = $detail->sum('debit') ?? 0;
                    $totalCredit = $detail->sum('credit') ?? 0;
                    $balance = $totalDebit - $totalCredit; // Positive = we owe vendor/contractor
                    break;

                default:
                    return response()->json(['balance' => 0, 'error' => 'Invalid table']);
            }

            return response()->json(['balance' => $balance]);

        } catch (\Exception $e) {
            return response()->json(['balance' => 0, 'error' => 'Error calculating balance']);
        }
    }

    public function ajaxOrder(Request $request)
    {
        $customerId = $request->input('customerId');
        $order = $this->orderRepository->getOrder($customerId);

        return response()->json(['data' => $order]);
    }

    public function ajaxPurchase(Request $request)
    {
        $tableId = $request->input('vendorId');
        // error_log("Vendor ID: " . $tableId);
        $purchase = $this->purchaseRepository->getPurchase($tableId);

        return response()->json(['data' => $purchase]);
    }

    public function ajaxPayee(Request $request)
    {
        $type = $request->input('type');
        $payees = [];

        if ($type === 'vendor') {
            $payees = $this->vendorRepository->vendor()->map(function ($vendor) {
                return [
                    'id' => $vendor->vendor_id,
                    'no' => $vendor->vendor_no,
                    'name' => $vendor->fname,
                ];
            })->toArray();
        } elseif ($type === 'contractor') {
            $payees = $this->vendorRepository->worker()->map(function ($contractor) {
                return [
                    'id' => $contractor->vendor_id,
                    'no' => $contractor->vendor_no,
                    'name' => $contractor->fname,
                ];
            })->toArray();
        } elseif ($type === 'employee') {
            $payees = $this->employeeRepository->all()->map(function ($employee) {
                return [
                    'id' => $employee->employee_id,
                    'no' => $employee->employee_no,
                    'name' => $employee->name,
                ];
            })->toArray();
        } elseif ($type === 'customer') {
            $payees = $this->customerRepository->all()->map(function ($customer) {
                return [
                    'id' => $customer->customer_id,
                    'no' => $customer->customer_no,
                    'name' => $customer->fname,
                ];
            })->toArray();
        }

        return response()->json($payees);
    }

    public function store(TransactionRequest $request)
    {
        $validatedData = $request->validated();
        // For receiveAdvance: swap credit to debit (cash inflow from advance payment)
        if ($validatedData['transaction_type'] == 'receiveAdvance') {
            $validatedData['debit'] = $validatedData['credit'];
            $validatedData['credit'] = null;
        }
        // For BRS type 2: swap debit to credit (cash outflow for BRS correction)
        elseif ($request->has('brs_type') && $request->input('brs_type') == 2) {
            $validatedData['credit'] = $validatedData['debit'];
            $validatedData['debit'] = null;
        }
        // For BRS type 1: keep debit as debit (cash inflow increases balance)
        elseif ($request->has('brs_type') && $request->input('brs_type') == 1) {
            $validatedData['credit'] = null;
        }

        // For generalVoucher: handle debit/credit based on voucher type
        if ($validatedData['transaction_to'] == 'generalVoucher') {
            // Determine if payee is vendor, contractor, employee, or customer based on payee_type
            $payeeType = $request->input('payee_type');
            $validatedData['transaction_to'] = $payeeType; // Set to 'vendor', 'contractor', 'employee', or 'customer'

            // Get the voucher type and amount
            $voucherType = $request->input('voucher_type', 'debit');
            $amount = $request->input('amount', 0);

            if ($voucherType === 'debit') {
                // Debit Voucher (Charge to Payee): Store in credit column
                // This represents a charge/bill that hasn't been paid yet
                $validatedData['credit'] = $amount;
                $validatedData['debit'] = null;
            } else {
                // Credit Voucher (Credit to Payee): Store in debit column
                // This represents a credit/allowance that hasn't been settled yet
                $validatedData['debit'] = $amount;
                $validatedData['credit'] = null;
            }

            // Set ledger_flag=0 so general vouchers don't appear in Cash/Bank ledgers
            // They will only appear in payee ledgers (Vendor/Contractor/Employee/Customer)
            $validatedData['ledger_flag'] = 0;
        }

        // For expense payments: handle debit/credit based on expense_type
        if ($validatedData['transaction_to'] == 'expense' &&
            $validatedData['transaction_type'] == 'expense') {
            $expenseType = $request->input('expense_type', 'credit');
            $amount = $request->input('amount', 0);

            if ($expenseType === 'credit') {
                // Expense incurred: store as credit (cash outflow)
                $validatedData['credit'] = $amount;
                $validatedData['debit'] = null;
            } else {
                // Expense reversal: store as debit (cash inflow)
                $validatedData['debit'] = $amount;
                $validatedData['credit'] = null;
            }
        }

        // For vendor and contractor payments: swap debit to credit for Cashbook posting (cash outflow)
        // Vendor/Contractor ledger stores in debit (reduces liability), Cashbook stores in credit (cash outflow)
        // BUT NOT for general vouchers (transaction_type = 'generalVoucher')
        if (in_array($validatedData['transaction_to'], ['vendor', 'contractor']) &&
            in_array($validatedData['transaction_type'], ['payment', 'wages', 'advance'])) {
            // Swap debit to credit for Cashbook posting (cash outflow)
            if (isset($validatedData['debit']) && $validatedData['debit'] > 0) {
                $validatedData['credit'] = $validatedData['debit'];
                $validatedData['debit'] = null;
            }
        }

        // For ALL customer payments: ensure amount is in debit column (cash inflow)
        // BUT NOT for general vouchers (transaction_type = 'generalVoucher')
        if ($validatedData['transaction_to'] == 'customer' &&
            $validatedData['transaction_type'] != 'generalVoucher') {
            // Customer payments are cash inflows - should be in debit column
            // Ensure credit is null for customer payments
            $validatedData['credit'] = null;
        }

        // Determine if this is a cash outflow transaction (requires balance validation)
        $isOutflowTransaction = in_array($validatedData['transaction_to'], ['employee', 'vendor', 'contractor', 'expense']) ||
                               ($validatedData['transaction_to'] == 'brs' && $request->has('brs_type') && $request->input('brs_type') == 2);

        // Determine if this is a cash inflow transaction (no balance validation needed)
        $isInflowTransaction = ($validatedData['transaction_to'] == 'customer' && $validatedData['transaction_type'] == 'orderPayment') ||
                              ($validatedData['transaction_type'] == 'receiveAdvance') ||
                              ($validatedData['transaction_to'] == 'brs' && $request->has('brs_type') && $request->input('brs_type') == 1);

        // Get current balance
        if ($validatedData['bank_id'] != '0') {
            // Bank account balance
            $transaction = $this->transactionRepository->bankBalance2($validatedData['bank_id']);
            $balance = ($transaction->tcredit ?? 0) - ($transaction->tdebit ?? 0);
        } else {
            // Cash balance
            $balance = $this->transactionRepository->cashBalance();
        }

        // Validate balance only for outflow transactions
        if ($isOutflowTransaction && !$isInflowTransaction) {
            // For outflow transactions, check if we have sufficient balance
            // Outflow amount is stored in 'credit' column
            $outflowAmount = $validatedData['credit'] ?? 0;

            if ($outflowAmount > 0 && $balance < $outflowAmount) {
                return redirect()->back()->with(['fails' => 'Insufficient Balance Available'])->withInput();
            }
        }
        // For inflow transactions, skip balance validation entirely

        $getId = $this->transactionRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'transaction', 'transactions', $getId);
            }
        }

        // Check if this is a general voucher (transaction_type = 'generalVoucher')
        if ($request->input('transaction_type') == 'generalVoucher') {
            return redirect()->route('transaction.showGeneralVoucher', $getId)->with('success', 'Record Inserted Successfully');
        } elseif ($request->input('transaction_to') == 'employee') {
            return redirect()->route('transaction.showEPayment', $getId)->with('success', 'Record Inserted Successfully');
        } elseif ($request->input('transaction_to') == 'vendor') {
            return redirect()->route('transaction.showVPayment', $getId)->with('success', 'Record Inserted Successfully');
        } elseif ($request->input('transaction_to') == 'contractor') {
            return redirect()->route('transaction.showCPayment', $getId)->with('success', 'Record Inserted Successfully');
        } elseif ($request->input('transaction_to') == 'customer') {
            return redirect()->route('transaction.showOPayment', $getId)->with('success', 'Record Inserted Successfully');
        } elseif ($request->input('transaction_to') == 'expense') {
            return redirect()->route('transaction.showExpense', $getId)->with('success', 'Record Inserted Successfully');
        } elseif ($request->input('transaction_to') == 'brs') {
            return redirect()->route('transaction.showBRS', $getId)->with('success', 'Record Inserted Successfully');
        } else {
            return redirect()->route('transaction')->with('success', 'Record Inserted Successfully');
        }
    }

    public function show($id)
    {
        $this->authorize('show', Transaction::class);
    }

    public function showExpense($id)
    {
        $this->authorize('show', Transaction::class);
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getExpense($id);

        return view('expenseInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showEPayment($id)
    {
        $this->authorize('show', Transaction::class);
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getEPayment($id);

        return view('ePaymentInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showVPayment($id)
    {
        $this->authorize('show', Transaction::class);
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getVPayment($id);

        return view('vPaymentInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showCPayment($id)
    {
        $this->authorize('show', Transaction::class);
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getVPayment($id);

        return view('cPaymentInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showOPayment($id)
    {
        $this->authorize('show', Transaction::class);
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getOPayment($id);

        return view('oPaymentInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showGeneralVoucher($id)
    {
        $this->authorize('show', Transaction::class);
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getGeneralVoucher($id);

        return view('generalVoucherInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showBRS($id)
    {
        $this->authorize('show', Transaction::class);
        $transaction = $this->transactionRepository->getBRS($id);

        return view('brsInfo', [
            'transaction' => $transaction,
        ]);
    }

    public function showBBalance(Request $request, $id)
    {
        $this->authorize('show', Transaction::class);
        $bank = $this->bankRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->bankTransactionFilter($id, $dfrom, $dto);
            $transaction = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $transaction = $this->transactionRepository->bankTransaction($id);
        }
        $bBalance = $this->transactionRepository->bankBalance2($id);
        $balance = ($bBalance->tdebit ?? 0) - ($bBalance->tcredit ?? 0);
        // $balance = $bBalance->tdebit - $bBalance->tcredit;

        return view('bankBalanceDetail', [
            'dto' => $dto,
            'dfrom' => $dfrom,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'bank' => $bank,
            'balance' => $balance,
            'transaction' => $transaction,
        ]);
    }

    public function edit(Box $id)
    {
        $this->authorize('edit', Transaction::class);
    }

    public function editEPayment(Transaction $id)
    {
        $this->authorize('edit', Transaction::class);
        $bank = $this->bankRepository->self();
        $employee = $this->employeeRepository->all();

        return view('editPayEmployee', [
            'transaction' => $id,
            'bank' => $bank,
            'employee' => $employee,
        ]);
    }

    public function editVPayment(Transaction $id)
    {
        $this->authorize('edit', Transaction::class);
        $bank = $this->bankRepository->self();
        $vendor = $this->vendorRepository->all();

        return view('editPayVendor', [
            'transaction' => $id,
            'bank' => $bank,
            'vendor' => $vendor,
        ]);
    }

    public function editCPayment(Transaction $id)
    {
        $this->authorize('edit', Transaction::class);
        $bank = $this->bankRepository->self();
        $vendor = $this->vendorRepository->all();

        return view('editPayContractor', [
            'transaction' => $id,
            'bank' => $bank,
            'vendor' => $vendor,
        ]);
    }

    public function editOPayment(Transaction $id)
    {
        $this->authorize('edit', Transaction::class);
        $customer = $this->customerRepository->all();
        $bank = $this->bankRepository->self();
        $order = $this->orderRepository->all();

        return view('editPayOrder', [
            'transaction' => $id,
            'bank' => $bank,
            'order' => $order,
            'customer' => $customer,
        ]);
    }

    public function editGeneralVoucher(Transaction $id)
    {
        $this->authorize('edit', Transaction::class);
        $vendor = $this->vendorRepository->vendor();
        $contractor = $this->vendorRepository->worker();
        $employee = $this->employeeRepository->all();
        $customer = $this->customerRepository->all();

        return view('editGeneralVoucher', [
            'transaction' => $id,
            'vendor' => $vendor,
            'contractor' => $contractor,
            'employee' => $employee,
            'customer' => $customer,
        ]);
    }

    public function editExpense(Transaction $id)
    {
        $this->authorize('edit', Transaction::class);
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');

        return view('editExpense', [
            'transaction' => $id,
            'bank' => $bank,
            'expense' => $expense,
        ]);
    }

    public function editBRS(Transaction $id)
    {
        $this->authorize('edit', Transaction::class);
        $bank = $this->bankRepository->self();

        return view('editBRS', [
            'transaction' => $id,
            'bank' => $bank,
        ]);
    }

    public function update(Request $request, $id)
    {
        // For receiveAdvance: swap credit to debit (cash inflow from advance payment)
        if ($request->input('transaction_type') == 'receiveAdvance') {
            $request->merge(['debit' => $request->input('credit'), 'credit' => null]);
        }
        // For BRS type 2: swap debit to credit (cash outflow for BRS correction)
        elseif ($request->has('brs_type') && $request->input('brs_type') == 2) {
            $request->merge(['credit' => $request->input('debit'), 'debit' => null]);
        }
        // For BRS type 1: keep debit as debit (cash inflow increases balance)
        elseif ($request->has('brs_type') && $request->input('brs_type') == 1) {
            $request->merge(['credit' => null]);
        }

        // For generalVoucher: handle debit/credit based on payee type
        if ($request->input('transaction_to') == 'generalVoucher') {
            // Determine if payee is vendor, contractor, employee, or customer based on payee_type
            $payeeType = $request->input('payee_type');
            $request->merge(['transaction_to' => $payeeType]); // Set to 'vendor', 'contractor', 'employee', or 'customer'

            // For vendor/contractor: keep debit as debit (charge to them reduces payable)
            // For employee: keep debit as debit (charge to them reduces payable)
            // For customer: keep debit as debit (charge to them increases receivable)
            $request->merge(['credit' => null]); // Keep debit only for all types
        }

        // For expense payments: handle debit/credit based on expense_type
        if ($request->input('transaction_to') == 'expense' &&
            $request->input('transaction_type') == 'expense') {
            $expenseType = $request->input('expense_type', 'credit');
            $amount = $request->input('amount', 0);

            if ($expenseType === 'credit') {
                // Expense incurred: store as credit (cash outflow)
                $request->merge(['credit' => $amount, 'debit' => null]);
            } else {
                // Expense reversal: store as debit (cash inflow)
                $request->merge(['debit' => $amount, 'credit' => null]);
            }
        }

        // For vendor and contractor payments: swap debit to credit for Cashbook posting (cash outflow)
        // Vendor/Contractor ledger stores in debit (reduces liability), Cashbook stores in credit (cash outflow)
        // BUT NOT for general vouchers (transaction_type = 'generalVoucher')
        if (in_array($request->input('transaction_to'), ['vendor', 'contractor']) &&
            in_array($request->input('transaction_type'), ['payment', 'wages', 'advance'])) {
            // Swap debit to credit for Cashbook posting (cash outflow)
            if ($request->input('debit') > 0) {
                $request->merge(['credit' => $request->input('debit'), 'debit' => null]);
            }
        }

        // For order payments: ensure debit/credit are correct for Cashbook posting (cash inflow)
        // Customer payments are cash inflows - should be in debit column
        if ($request->input('transaction_to') == 'customer' &&
            $request->input('transaction_type') == 'orderPayment') {
            $request->merge(['credit' => null]);
            // debit already has the amount, no swap needed for orderPayment
        }

        $getId = $this->transactionRepository->update($id, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'transaction', 'transactions', $id);
            }
        }
        // Check if this is a general voucher (transaction_type = 'generalVoucher')
        if ($request->input('transaction_type') == 'generalVoucher') {
            return redirect()->route('transaction.showGeneralVoucher', $id)->with('success', 'Record Updated Successfully');
        } elseif ($request->input('transaction_to') == 'employee') {
            return redirect()->route('transaction.showEPayment', $id)->with('success', 'Record Updated Successfully');
        } elseif ($request->input('transaction_to') == 'vendor') {
            return redirect()->route('transaction.showVPayment', $id)->with('success', 'Record Updated Successfully');
        } elseif ($request->input('transaction_to') == 'contractor') {
            return redirect()->route('transaction.showCPayment', $id)->with('success', 'Record Updated Successfully');
        } elseif ($request->input('transaction_to') == 'customer') {
            return redirect()->route('transaction.showOPayment', $id)->with('success', 'Record Updated Successfully');
        } elseif ($request->input('transaction_to') == 'expense') {
            return redirect()->route('transaction.showExpense', $id)->with('success', 'Record Updated Successfully');
        } elseif ($request->input('transaction_to') == 'brs') {
            return redirect()->route('transaction.showBRS', $id)->with('success', 'Record Updated Successfully');
        } else {
            return redirect()->route('transaction')->with('success', 'Record Updated Successfully');
        }
    }

    public function destroy(product $product)
    {
        $this->authorize('delete', Transaction::class);
    }

    /**
     * Get transaction data by type
     */
    private function getTransactionByType($id, $transactionTo)
    {
        switch ($transactionTo) {
            case 'employee':
                return $this->transactionRepository->getEPayment($id);
            case 'vendor':
                return $this->transactionRepository->getVPayment($id);
            case 'contractor':
                return $this->transactionRepository->getVPayment($id); // Uses same method as vendor
            case 'customer':
                return $this->transactionRepository->getOPayment($id);
            case 'expense':
                return $this->transactionRepository->getExpense($id);
            case 'brs':
                return $this->transactionRepository->getBRS($id);
            case 'generalVoucher':
                return $this->transactionRepository->getGeneralVoucher($id);
            default:
                return null;
        }
    }

    /**
     * Determine debit account based on transaction type
     */
    private function getDebitAccount($transaction)
    {
        if ($transaction['bank_id']) {
            return $transaction['bname'] ?? 'Bank Account';
        }

        return 'CASH';
    }

    /**
     * Determine credit account based on transaction type
     */
    private function getCreditAccount($transaction)
    {
        switch ($transaction['transaction_to']) {
            case 'employee':
                return ($transaction['employee_no'] ?? '') . ' - ' . ($transaction['name'] ?? 'Employee');
            case 'vendor':
                return ($transaction['vendor_no'] ?? '') . ' - ' . ($transaction['fname'] ?? 'Vendor');
            case 'contractor':
                return ($transaction['vendor_no'] ?? '') . ' - ' . ($transaction['fname'] ?? 'Contractor');
            case 'customer':
                return ($transaction['customer_no'] ?? '') . ' - ' . ($transaction['fname'] ?? 'Customer');
            case 'expense':
                return 'Expense Account';
            default:
                if ($transaction['payee_bank_id']) {
                    return $transaction['rname'] ?? 'Payee Bank';
                }
                return 'CASH';
        }
    }

    /**
     * Print payment voucher using standard print template
     */
    public function printPayment($id)
    {
        $this->authorize('show', Transaction::class);

        // First get basic transaction to determine type
        $basicTransaction = Transaction::find($id);

        if (!$basicTransaction) {
            return redirect()->back()->with('error', 'Transaction not found');
        }

        // Get detailed transaction data based on type
        $transaction = $this->getTransactionByType($id, $basicTransaction->transaction_to);

        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaction details not found');
        }

        // Generate voucher number
        $voucherNumber = 'TXN-' . date('Y') . '-' . str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT);

        return view('print.payment', [
            'transaction' => $transaction,
            'voucherNumber' => $voucherNumber,
        ]);
    }

    /**
     * Print expense voucher
     */
    public function printExpense($id)
    {
        $this->authorize('show', Transaction::class);
        $expense = $this->transactionRepository->getExpense($id);

        if (!$expense) {
            return redirect()->back()->with('error', 'Expense not found');
        }

        return view('print.expense', [
            'expense' => $expense,
        ]);
    }

    /**
     * Print Balance Adjustment details
     */
    public function printBRS($id)
    {
        $this->authorize('show', Transaction::class);
        $transaction = $this->transactionRepository->getBRS($id);

        if (!$transaction) {
            return redirect()->back()->with('error', 'Balance Adjustment not found');
        }

        // Generate voucher number
        $voucherNumber = 'TXN-' . date('Y') . '-' . str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT);

        return view('print.payment', [
            'transaction' => $transaction,
            'voucherNumber' => $voucherNumber,
        ]);
    }

}
