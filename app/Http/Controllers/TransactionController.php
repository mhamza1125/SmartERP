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

    public function index()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->all();

        return view('transaction', [
            'transaction' => $transaction,
        ]);
    }

    public function oPayment()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->oPayment();

        return view('oPayment', [
            'transaction' => $transaction,
        ]);
    }

    public function ePayment()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->ePayment();

        return view('ePayment', [
            'transaction' => $transaction,
        ]);
    }

    public function vPayment()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->vPayment();

        return view('vPayment', [
            'transaction' => $transaction,
        ]);
    }

    public function cPayment()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->cPayment();

        return view('cPayment', [
            'transaction' => $transaction,
        ]);
    }

    public function expense()
    {
        $this->authorize('access', Transaction::class);
        $transaction = $this->transactionRepository->expense();

        return view('expense', [
            'transaction' => $transaction,
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

    public function createGeneralVoucher()
    {
        $this->authorize('create', Transaction::class);

        return view('addGeneralVoucher');
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
                    $totalCredit = $detail->sum('credit');
                    $totalDebit = $detail->sum('debit');
                    $balance = $totalCredit - $totalDebit; // Positive = customer owes us
                    break;

                case 'employee':
                    $detail = $this->transactionRepository->eDetail($tableId);
                    $totalCredit = $detail->whereIn('transaction_type', ['advance', 'receiveAdvance', 'openingBalance'])->sum('credit');
                    $totalDebit = $detail->whereIn('transaction_type', ['advance', 'receiveAdvance', 'openingBalance', 'wages'])->sum('debit');
                    $balance = $totalCredit - $totalDebit; // Positive = we owe employee
                    break;

                case 'vendor':
                    $detail = $this->transactionRepository->vDetail($tableId);
                    $totalCredit = $detail->where('transaction_type', '!=', 'wages')->sum('credit');
                    $totalDebit = $detail->whereIn('transaction_type', ['wages'])->sum('debit') +
                                 $detail->where('transaction_type', '!=', 'wages')->sum('debit');
                    $balance = $totalCredit - $totalDebit; // Positive = we owe vendor/contractor
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

        // For generalVoucher: keep debit as debit (charge to vendor/contractor reduces payable)
        if ($validatedData['transaction_to'] == 'generalVoucher') {
            // Determine if payee is vendor or contractor based on payee_type
            $payeeType = $request->input('payee_type');
            $validatedData['transaction_to'] = $payeeType; // Set to 'vendor' or 'contractor'
            $validatedData['credit'] = null; // Keep debit only
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

        // For ALL customer payments: ensure amount is in debit column (cash inflow)
        if ($validatedData['transaction_to'] == 'customer') {
            // Customer payments are cash inflows - should be in debit column
            if (isset($validatedData['credit']) && $validatedData['credit'] > 0) {
                // If amount is in credit, move it to debit
                $validatedData['debit'] = $validatedData['credit'];
                $validatedData['credit'] = null;
            }
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

        if ($request->input('transaction_to') == 'employee') {
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

        // For order payments: ensure debit/credit are correct for Cashbook posting (cash inflow)
        if ($request->input('transaction_to') == 'customer' &&
            $request->input('transaction_type') == 'orderPayment' &&
            $request->input('debit') > 0) {
            $request->merge(['credit' => null]);
            // debit already has the amount, no swap needed for orderPayment
        }

        $getId = $this->transactionRepository->update($id, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'transaction', 'transactions', $id);
            }
        }
        if ($request->input('transaction_to') == 'employee') {
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
     * Print BRS details
     */
    public function printBRS($id)
    {
        $this->authorize('show', Transaction::class);
        $transaction = $this->transactionRepository->getBRS($id);

        if (!$transaction) {
            return redirect()->back()->with('error', 'BRS not found');
        }

        // Generate voucher number
        $voucherNumber = 'TXN-' . date('Y') . '-' . str_pad($transaction['transaction_id'], 4, '0', STR_PAD_LEFT);

        return view('print.payment', [
            'transaction' => $transaction,
            'voucherNumber' => $voucherNumber,
        ]);
    }

}
