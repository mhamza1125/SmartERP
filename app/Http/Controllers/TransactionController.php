<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\BankRepository;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Repositories\OrderRepository;
use App\Repositories\VendorRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EmployeeRepository;
use App\Http\Requests\TransactionRequest;
use App\Repositories\TransactionRepository;

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
    ){
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

    public function index(){
        $transaction = $this->transactionRepository->all();
        return view('transaction', [
            'transaction' => $transaction,
        ]);
    }

    public function oPayment(){
        $transaction = $this->transactionRepository->oPayment();
        return view('oPayment', [
            'transaction' => $transaction,
        ]);
    }

    public function ePayment(){
        $transaction = $this->transactionRepository->ePayment();
        return view('ePayment', [
            'transaction' => $transaction,
        ]);
    }

    public function vPayment(){
        $transaction = $this->transactionRepository->vPayment();
        return view('vPayment', [
            'transaction' => $transaction,
        ]);
    }
    
    public function expense(){
        $transaction = $this->transactionRepository->expense();
        return view('expense', [
            'transaction' => $transaction,
        ]);
    }

    public function bankBalance(){
        $transaction = $this->transactionRepository->bankBalance();
        return view('bankBalance', [
            'transaction' => $transaction,
        ]);
    }
    
    public function cashBalance(){
        $balance = $this->transactionRepository->cashBalance();
        $transaction = $this->transactionRepository->cashTransaction();
        return view('cashBalance', [
            'cashBalance' => $balance,
            'transaction' => $transaction,
        ]);
    }

    public function create(){}

    public function createBRS(){
        $bank = $this->bankRepository->self();
        return view('addBRS', [
            'bank' => $bank,
        ]);
    }
    
    public function createEPayment(){
        $bank = $this->bankRepository->self();
        $employee = $this->employeeRepository->all();
        return view('addPayEmployee', [
            'bank' => $bank,
            'employee' => $employee,
        ]);
    }
    
    public function createVPayment(){
        $bank = $this->bankRepository->self();
        $vendor = $this->vendorRepository->all();
        return view('addPayVendor', [
            'bank' => $bank,
            'vendor' => $vendor,
        ]);
    }

    public function createExpense(){
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');
        return view('addExpense', [
            'bank' => $bank,
            'expense' => $expense,
        ]);
    }

    public function createOPayment(){
        $customer = $this->customerRepository->all();
        $bank = $this->bankRepository->self();
        $order = $this->orderRepository->all();
        return view('addPayOrder', [
            'bank' => $bank,
            'order' => $order,
            'customer' => $customer,
        ]);
    }
    
    public function createCashBalance(){
        // Cash Balance
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');
        return view('addExpense', [
            'bank' => $bank,
            'expense' => $expense,
        ]);
    }

    public function ajaxBank(Request $request){
        $table = $request->input('table');
        $tableId = $request->input('tableId');
        $bank = $this->bankRepository->getBank($table, $tableId);
        return response()->json(['data' => $bank]);
    }
    
    public function ajaxOrder(Request $request){
        $customerId = $request->input('customerId');
        $order = $this->orderRepository->getOrder($customerId);
        return response()->json(['data' => $order]);
    }

    public function ajaxPurchase(Request $request){
        $tableId = $request->input('vendorId');
        error_log("Vendor ID: " . $tableId);
        $purchase = $this->purchaseRepository->getPurchase($tableId);
        return response()->json(['data' => $purchase]);
    }

    public function store(TransactionRequest $request){
        $validatedData = $request->validated();
        if ($validatedData['transaction_type'] == 'receiveAdvance' || 
            ($request->has('brs_type') && $request->input('brs_type') == 1)) {
            $validatedData['credit'] = $validatedData['debit'];
            $validatedData['debit'] = null;
        }
        $getId = $this->transactionRepository->store($validatedData);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'transaction', 'transactions', $getId);        
            }
        }
        if($request->input('transaction_to') == 'employee'){
            return redirect()->route('transaction.showEPayment', $getId)->with('success', 'Record Inserted Successfully');
        }elseif($request->input('transaction_to') == 'vendor'){
            return redirect()->route('transaction.showVPayment', $getId)->with('success', 'Record Inserted Successfully');
        }elseif($request->input('transaction_to') == 'customer'){
            return redirect()->route('transaction.showOPayment', $getId)->with('success', 'Record Inserted Successfully');
        }elseif($request->input('transaction_to') == 'expense'){
            return redirect()->route('transaction.showExpense', $getId)->with('success', 'Record Inserted Successfully');
        }else{
            return redirect()->route('transaction.addBRS')->with('success', 'Record Inserted Successfully');
        }
    }
    
    public function show($id){}

    public function showExpense($id){
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getExpense($id);
        return view('expenseInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showEPayment($id){
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getEPayment($id);
        return view('ePaymentInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showVPayment($id){
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getVPayment($id);
        return view('vPaymentInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showOPayment($id){
        $image = $this->imageRepository->image('transactions', $id);
        $transaction = $this->transactionRepository->getOPayment($id);
        return view('OPaymentInfo', [
            'transaction' => $transaction,
            'image' => $image,
        ]);
    }

    public function showBBalance($id){
        $bank = $this->bankRepository->get($id);
        $transaction = $this->transactionRepository->bankTransaction($id);
        $totalCredit = $transaction->sum('credit');
        $totalDebit = $transaction->sum('debit');
        $balance = $totalCredit - $totalDebit;
        return view('bankBalanceDetail', [
            'bank' => $bank,
            'balance' => $balance,
            'transaction' => $transaction,
        ]);
    }
    
    public function edit(Box $id){}

    public function editEPayment(Transaction $id){
        $bank = $this->bankRepository->self();
        $employee = $this->employeeRepository->all();
        return view('editPayEmployee', [
            'transaction' => $id,
            'bank' => $bank,
            'employee' => $employee,
        ]);
    }

    public function editVPayment(Transaction $id){
        $bank = $this->bankRepository->self();
        $vendor = $this->vendorRepository->all();
        return view('editPayVendor', [
            'transaction' => $id,
            'bank' => $bank,
            'vendor' => $vendor,
        ]);
    }

    public function editOPayment(Transaction $id){
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

    public function editExpense(Transaction $id){
        $bank = $this->bankRepository->self();
        $expense = $this->headRepository->get('7');
        return view('editExpense', [
            'transaction' => $id,
            'bank' => $bank,
            'expense' => $expense,
        ]);
    }

    public function update(Request $request, $id){
        $getId = $this->transactionRepository->update($id, $request->input());
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'transaction', 'transactions', $id);        
            }
        }
        if($request->input('transaction_to') == 'employee'){
            return redirect()->route('transaction.showEPayment', $id)->with('success', 'Record Updated Successfully');    
        }elseif($request->input('transaction_to') == 'vendor'){
            return redirect()->route('transaction.showVPayment', $id)->with('success', 'Record Updated Successfully');    
        }elseif($request->input('transaction_to') == 'customer'){
            return redirect()->route('transaction.showOPayment', $id)->with('success', 'Record Updated Successfully');
        }elseif($request->input('transaction_to') == 'expense'){
            return redirect()->route('transaction.showExpense', $id)->with('success', 'Record Updated Successfully');    
        }else{
            return redirect()->route('transaction')->with('success', 'Record Updated Successfully');    
        }
    }
    
    public function destroy(product $product){}
}
