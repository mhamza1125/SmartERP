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

    public function ajaxBank(Request $request)
    {
        $table = $request->input('table');
        $tableId = $request->input('tableId');
        $bank = $this->bankRepository->getBank($table, $tableId);

        return response()->json(['data' => $bank]);
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

    public function store(TransactionRequest $request)
    {
        $validatedData = $request->validated();
        if ($validatedData['transaction_type'] == 'receiveAdvance' ||
            ($request->has('brs_type') && $request->input('brs_type') == 1)) {
            $validatedData['credit'] = $validatedData['debit'];
            $validatedData['debit'] = null;
        }

        // if ($validatedData['bank_id'] != '0' && $validatedData['credit'] == '0') {
        if ($validatedData['bank_id'] != '0' && $validatedData['debit'] == '0') {
            $transaction = $this->transactionRepository->bankBalance2($validatedData['bank_id']);
            $balance = $transaction->tcredit - $transaction->tdebit;
        } else {
            $balance = $this->transactionRepository->cashBalance();
        }

        if ($balance < $validatedData['debit'] && $validatedData['credit'] == '0') {
            return redirect()->back()->with(['fails' => 'Insufficient Balance Available'])->withInput();
        }

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
        $balance = ($bBalance->tcredit ?? 0) - ($bBalance->tdebit ?? 0);
        // $balance = $bBalance->tcredit - $bBalance->tdebit;

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
        if ($request->input('transaction_type') == 'receiveAdvance' ||
            ($request->has('brs_type') && $request->input('brs_type') == 1)) {
            $request->merge(['credit' => $request->input('debit'), 'debit' => null]);
        } elseif ($request->has('brs_type') && $request->input('brs_type') == 2) {
            $request->merge(['credit' => null]);
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
}
