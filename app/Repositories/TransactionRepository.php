<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements GlobalInterface {
    
    public function all(){
        return Transaction::orderBy('created_at', 'desc')->get();
    }

    public function oPayment(){
        return Transaction::where('transaction_to', 'customer')
        ->join('customers', 'customers.customer_id', '=', 'transactions.payee_id')
        ->join('orders', 'orders.order_id', '=', 'transactions.order_id')
        ->orderBy('transactions.created_at', 'desc')
        ->get();
    }
    
    public function ePayment(){
        return Transaction::where('transaction_to', 'employee')
        ->join('employees', 'employees.employee_id', '=', 'transactions.payee_id')
        ->orderBy('transactions.created_at', 'desc')
        ->get();
    }

    public function vPayment(){
        return Transaction::where('transaction_to', 'vendor')
        ->join('vendors', 'vendors.vendor_id', '=', 'transactions.payee_id')
        ->orderBy('transactions.created_at', 'desc')
        ->get();
    }

    public function expense(){
        return Transaction::where('transaction_to', 'expense')
        ->join('heads', 'heads.head_id', '=', 'transactions.payee_id')
        ->orderBy('transactions.created_at', 'desc')
        ->get();
    }

    public function cashTransaction(){
        return Transaction::where('transactions.bank_id', '0')
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function bankBalance(){
        return Transaction::where('transactions.bank_id', '>', '0')
        ->join('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->select('*', 'transactions.bank_id', 'heads.name as hname', 
            DB::raw('SUM(transactions.debit) AS tdebit'),         
            DB::raw('SUM(transactions.credit) AS tcredit'))
        ->groupBy('transactions.bank_id')
        ->get();
    }

    public function cashBalance(){
        $transaction = Transaction::where('transactions.bank_id', '0')
        ->select('*', 
            DB::raw('SUM(transactions.debit) AS tdebit'),         
            DB::raw('SUM(transactions.credit) AS tcredit'))
        ->groupBy('transactions.bank_id')
        ->first();

        return $transaction->tcredit - $transaction->tdebit;
    }

    public function get($id){}

    public function getExpense($id){
        return Transaction::where('transaction_id', $id)
        ->join('heads', 'heads.head_id', 'transactions.payee_id')
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->select('*', 'heads.name as hname', 'bhead.name as bname')
       ->first();
    }

    public function getEPayment($id){
        return Transaction::where('transaction_id', $id)
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
        ->join('employees', 'employees.employee_id', 'transactions.payee_id')
        ->select('*', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'transactions.description', 'transactions.bank_id', 'banks.account', 'banks.account_title')
       ->first();
    }

    public function getVPayment($id){
        return Transaction::where('transaction_id', $id)
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
        ->join('vendors', 'vendors.vendor_id', 'transactions.payee_id')
        ->select('*', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'transactions.description', 'transactions.bank_id', 'banks.account', 'banks.account_title')
       ->first();
    }
    
    public function getOPayment($id){
        return Transaction::where('transaction_id', $id)
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
        ->join('customers', 'customers.customer_id', 'transactions.payee_id')
        ->join('orders', 'orders.order_id', 'transactions.order_id')
        ->select('*', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'transactions.description', 'transactions.bank_id', 'banks.account', 'banks.account_title')
       ->first();
    }
    
    public function vDetail($id){
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(purchase_items.total) as credit'))
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->where('purchases.vendor_id', $id)
            ->groupBy('purchases.purchase_id')
            ->get();
        
        $purchaseReturns = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->select('*', 'returns.created_at as timestamp', \DB::raw('SUM(return_materials.quantity * purchase_items.price) as debit'))
            ->where('purchases.vendor_id', $id)
            ->groupBy('purchases.purchase_id')
            ->get();
        
        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'vendor')
            ->get();
            
        $return = $purchases->concat($purchaseReturns)->concat($transactions);
        $sorted = $return->sortByDesc('timestamp');
        return $sorted;
    }
    
    public function eDetail($id){
        return Transaction::where('transactions.payee_id', $id)
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'employee')
            ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Transaction::create($data);
        return $store->transaction_id;
    }

    public function update($id, array $data) {
        $update = Transaction::findOrFail($id);
        $update->update($data);
        return $update->product_id;
    }

    public function delete($id){}
}
