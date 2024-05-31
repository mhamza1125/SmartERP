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

    public function delivery($id){
        // Delivery Expense
        return Transaction::where('transactions.order_id', $id)
        // ->join('stocks', 'stocks.order_id', '=', 'transactions.order_id')
        // ->join('deliveries', 'deliveries.stock_id', 'stocks.stock_id')
        ->join('heads', 'heads.head_id', '=', 'transactions.payee_id')
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->where('transactions.transaction_to', 'expense')
        ->select('*', 'bhead.name as bname', 'heads.name', 'transactions.description')
        ->get();
    }

    public function cashTransaction(){
        return Transaction::where('transactions.bank_id', '0')
        ->where('transaction_type', '!=', 'openingBalance')
        ->orderBy('created_at')
        ->get();
    }

    public function cashTransactionFilter($dfrom, $dto) {
        // Transactions Before Date From
        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.bank_id', '0')
            ->where('transaction_type', '!=', 'openingBalance')
            ->where('transaction_date', '<', $dfrom)
            ->select(\DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
            ->first();
    
        $totalDebitBefore = $transactionsBefore->total_debit ?? 0;
        $totalCreditBefore = $transactionsBefore->total_credit ?? 0;
        $openingBalance = $totalCreditBefore - $totalDebitBefore;
    
        // Transactions Between Date From and Date To
        $transactionsBetween = \DB::table('transactions')
            ->where('transactions.bank_id', '0')
            ->where('transaction_type', '!=', 'openingBalance')
            ->whereBetween('transaction_date', [$dfrom, $dto])
            ->orderBy('created_at')
            ->get();
    
        // Transactions After Date To
        $transactionsAfter = \DB::table('transactions')
            ->where('transactions.bank_id', '0')
            ->where('transaction_type', '!=', 'openingBalance')
            ->where('transaction_date', '>', $dto)
            ->select(\DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
            ->first();
    
        $totalDebitAfter = $transactionsAfter->total_debit ?? 0;
        $totalCreditAfter = $transactionsAfter->total_credit ?? 0;
        $closingBalance = $totalCreditAfter - $totalDebitAfter;
    
        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance
        ];
    }

    public function bankTransaction($id){
        return Transaction::where('transactions.bank_id', $id)
        ->orderBy('created_at')
        ->get();
    }

    public function bankTransactionFilter($id, $dfrom, $dto) {
        // Transactions Before Date From
        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.bank_id', $id)
            ->where('transaction_date', '<', $dfrom)
            ->select(\DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
            ->first();
    
        $totalDebitBefore = $transactionsBefore->total_debit ?? 0;
        $totalCreditBefore = $transactionsBefore->total_credit ?? 0;
        $openingBalance = $totalCreditBefore - $totalDebitBefore;
    
        // Transactions Between Date From and Date To
        $transactionsBetween = \DB::table('transactions')
            ->where('transactions.bank_id', $id)
            ->whereBetween('transaction_date', [$dfrom, $dto])
            ->orderBy('created_at')
            ->get();
    
        // Transactions After Date To
        $transactionsAfter = \DB::table('transactions')
            ->where('transactions.bank_id', $id)
            ->where('transaction_date', '>', $dto)
            ->select(\DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
            ->first();
    
        $totalDebitAfter = $transactionsAfter->total_debit ?? 0;
        $totalCreditAfter = $transactionsAfter->total_credit ?? 0;
        $closingBalance = $totalCreditAfter - $totalDebitAfter;
    
        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance
        ];
    }    

    public function cashBalance(){
        $transaction = Transaction::where('transactions.bank_id', '0')
        ->select('*', 
            DB::raw('SUM(transactions.debit) AS tdebit'),         
            DB::raw('SUM(transactions.credit) AS tcredit'))
        ->where('transaction_type', '!=', 'openingBalance')
        ->groupBy('transactions.bank_id')
        ->first();
        $tcredit = $transaction->tcredit ?? 0;
        $tdebit = $transaction->tdebit ?? 0;

        return $tcredit - $tdebit;
    }

    public function bankBalance(){
        // Banks Balance All
        return DB::table('banks')->where('banks.banker_id', '0')
        ->leftJoin('transactions', 'banks.bank_id', '=', 'transactions.bank_id')
        ->join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->select('banks.*', 'heads.name as hname',
            DB::raw('SUM(transactions.debit) AS tdebit'),
            DB::raw('SUM(transactions.credit) AS tcredit'),
            DB::raw('COALESCE(SUM(transactions.debit - transactions.credit), 0) as balance'))
        ->groupBy('banks.bank_id')
        ->havingRaw('balance = 0')
        ->get();

        // Dosen't show the Banks with 0 Transactions
        return Transaction::where('transactions.bank_id', '>', '0')
        ->join('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->select('*', 'transactions.bank_id', 'heads.name as hname', 
            DB::raw('SUM(transactions.debit) AS tdebit'),         
            DB::raw('SUM(transactions.credit) AS tcredit'))
        ->groupBy('transactions.bank_id')
        ->get();
    }

    public function bankBalance2($id){
        return DB::table('banks')->where('banks.bank_id', $id)
        ->leftJoin('transactions', 'banks.bank_id', '=', 'transactions.bank_id')
        ->join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->select('banks.*', 'heads.name as hname',
            DB::raw('SUM(transactions.debit) AS tdebit'),
            DB::raw('SUM(transactions.credit) AS tcredit'),
            DB::raw('COALESCE(SUM(transactions.debit - transactions.credit), 0) as balance'))
        ->groupBy('banks.bank_id')
        ->havingRaw('balance = 0')
        ->first();
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

    public function getEPayment($id){ // Employee Payment
        return Transaction::where('transaction_id', $id)
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
        ->join('employees', 'employees.employee_id', 'transactions.payee_id')
        ->select('*', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'transactions.description', 'transactions.bank_id', 'banks.account', 'banks.account_title')
        ->first();
    }

    public function getVPayment($id){ // Vendor Payment
        return Transaction::where('transaction_id', $id)
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
        ->join('vendors', 'vendors.vendor_id', 'transactions.payee_id')
        ->leftJoin('purchases', 'purchases.purchase_id', 'transactions.order_id')
        ->select('*', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'transactions.description', 'transactions.bank_id', 'banks.account', 'banks.account_title')
        ->first();
    }
    
    public function getOPayment($id){ // Order Payment
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
    
    public function getPPayment($id){ // Purchase Payment
        return Transaction::where('order_id', $id)->get();
    }

    public function getBRS($id){
        return Transaction::where('transaction_id', $id)
        ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
        ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
        ->select('*', 'bhead.name as bname')
        ->first();
    }
 
    public function vDetail($id){
        // Vendor Ledger
        // $purchases = \DB::table('purchases') // Amount of Purchase Items, Regardless of Receiving
        //     ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(purchase_items.total) as credit'))
        //     ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
        //     ->where('purchases.vendor_id', $id)
        //     ->groupBy('purchases.purchase_id')
        //     ->get();

        // Vendor Ledger - Amount of Received Purchase Items
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.quantity * purchase_items.price) as credit'))
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->where('purchases.vendor_id', $id)
            ->groupBy('purchases.purchase_id')
            ->get();

        // Vendor Ledger - Purchase Returns
        $purchaseReturns = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->select('*', 'returns.created_at as timestamp', \DB::raw('SUM(return_materials.quantity * purchase_items.price) as debit'))
            ->where('purchases.vendor_id', $id)
            ->groupBy('purchases.purchase_id')
            ->get();

        // Vendor Ledger - Transactions
        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'vendor')
            ->get();
            
        $return = $purchases->concat($purchaseReturns)->concat($transactions);
        $sorted = $return->sortBy('timestamp');
        return $sorted;
    }

    public function vDetailFilter($id, $dfrom, $dto){
        // Vendor Ledger - Before Date From
        $purchasesBefore = \DB::table('purchases')
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->where('purchases.vendor_id', $id)
            ->where('purchases.purchase_date', '<', $dfrom)
            ->select(\DB::raw('SUM(receive_materials.quantity * purchase_items.price) as credit'))
            ->first();
    
        $purchaseReturnsBefore = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->where('purchases.vendor_id', $id)
            ->where('returns.return_date', '<', $dfrom)
            ->select(\DB::raw('SUM(return_materials.quantity * purchase_items.price) as debit'))
            ->first();
    
        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_date', '<', $dfrom)
            ->where('transactions.transaction_to', 'vendor')
            ->where('transactions.transaction_type', '!=', 'wages')
            ->select(\DB::raw('SUM(transactions.debit) as debit'), \DB::raw('SUM(transactions.credit) as credit'))
            ->first();
    
        $totalCreditBefore = ($purchasesBefore->credit ?? 0) + ($transactionsBefore->credit ?? 0);
        $totalDebitBefore = ($purchaseReturnsBefore->debit ?? 0) + ($transactionsBefore->debit ?? 0);
        $openingBalance = $totalCreditBefore - $totalDebitBefore;
    
        // Vendor Ledger - Between Date From and Date To
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.quantity * purchase_items.price) as credit'))
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->where('purchases.vendor_id', $id)
            ->whereBetween('purchases.purchase_date', [$dfrom, $dto])
            ->groupBy('purchases.purchase_id')
            ->get();
    
        $purchaseReturns = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->select('*', 'returns.created_at as timestamp', \DB::raw('SUM(return_materials.quantity * purchase_items.price) as debit'))
            ->where('purchases.vendor_id', $id)
            ->whereBetween('returns.return_date', [$dfrom, $dto])
            ->groupBy('purchases.purchase_id')
            ->get();
    
        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->where('transactions.transaction_to', 'vendor')
            ->get();
    
        $transactionsBetween = $purchases->concat($purchaseReturns)->concat($transactions)->sortBy('timestamp');
    
        // Vendor Ledger - After Date To
        $purchasesAfter = \DB::table('purchases')
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->where('purchases.vendor_id', $id)
            ->where('purchases.purchase_date', '>', $dto)
            ->select(\DB::raw('SUM(receive_materials.quantity * purchase_items.price) as credit'))
            ->first();
    
        $purchaseReturnsAfter = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->where('purchases.vendor_id', $id)
            ->where('returns.return_date', '>', $dto)
            ->select(\DB::raw('SUM(return_materials.quantity * purchase_items.price) as debit'))
            ->first();
    
        $transactionsAfter = \DB::table('transactions')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_date', '>', $dto)
            ->where('transactions.transaction_to', 'vendor')
            ->where('transactions.transaction_type', '!=', 'wages')
            ->select(\DB::raw('SUM(transactions.debit) as debit'), \DB::raw('SUM(transactions.credit) as credit'))
            ->first();
    
        $totalCreditAfter = ($purchasesAfter->credit ?? 0) + ($transactionsAfter->credit ?? 0);
        $totalDebitAfter = ($purchaseReturnsAfter->debit ?? 0) + ($transactionsAfter->debit ?? 0);
        $closingBalance = $totalCreditAfter - $totalDebitAfter;
    
        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance
        ];
    }

    public function cDetail($id){
        // Customer Ledger
        $orders = \DB::table('orders')
            ->select('orders.*', 'orders.created_at as timestamp', \DB::raw('SUM(order_items.total) as debit'))
            ->join('order_items', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.customer_id', $id)
            ->groupBy('orders.order_id')
            ->get();
    
        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'customer')
            ->get();
            
        $return = $orders->concat($transactions);
        $sorted = $return->sortBy('timestamp');
        return $sorted;
    }  

    public function cDetailFilter($id, $dfrom, $dto){
        // Customer Ledger - Before Date From
        $ordersBefore = \DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.customer_id', $id)
            ->where('orders.order_date', '<', $dfrom)
            ->select(\DB::raw('SUM(order_items.total) as debit'))
            ->first();

        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_date', '<', $dfrom)
            ->where('transactions.transaction_to', 'customer')
            ->select(\DB::raw('SUM(transactions.debit) as debit'))
            ->select(\DB::raw('SUM(transactions.credit) as credit'))
            ->first();

        $totalDebitBefore = ($ordersBefore->debit ?? 0) + ($transactionsBefore->debit ?? 0);
        $totalCreditBefore = $transactionsBefore->credit ?? 0;
        $openingBalance = $totalCreditBefore - $totalDebitBefore;

        // Customer Ledger - Between Date From and Date To
        $orders = \DB::table('orders')
            ->select('orders.*', 'orders.created_at as timestamp', \DB::raw('SUM(order_items.total) as debit'))
            ->join('order_items', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.customer_id', $id)
            ->whereBetween('orders.order_date', [$dfrom, $dto])
            ->groupBy('orders.order_id')
            ->get();

        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'customer')
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->get();

        $transactionsBetween = $orders->concat($transactions)->sortBy('timestamp');

        // Customer Ledger - After Date To
        $ordersAfter = \DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.customer_id', $id)
            ->where('orders.order_date', '>', $dto)
            ->select(\DB::raw('SUM(order_items.total) as debit'))
            ->first();

        $transactionsAfter = \DB::table('transactions')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_date', '>', $dto)
            ->where('transactions.transaction_to', 'customer')
            ->select(\DB::raw('SUM(transactions.debit) as debit'))
            ->select(\DB::raw('SUM(transactions.credit) as credit'))
            ->first();

        $totalDebitAfter = ($ordersAfter->debit ?? 0) + ($transactionsAfter->debit ?? 0);
        $totalCreditAfter = $transactionsAfter->credit ?? 0;
        $closingBalance = $totalCreditAfter - $totalDebitAfter;

        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance
        ];
    }   
    
    public function eDetail($id){
        // Employee Ledger
        return Transaction::where('transactions.payee_id', $id)
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'employee')
            ->orderBy('transactions.created_at')
            ->get();
    }

    public function eDetailFilter($id, $dfrom, $dto){
        // Employee Ledger - Before Date From
        $before = Transaction::where('transactions.payee_id', $id)
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'employee')
            ->where('transactions.transaction_date', '<', $dfrom)
            ->whereIn('transactions.transaction_type', ['advance', 'receiveAdvance', 'openingBalance'])
            ->select(\DB::raw('SUM(transactions.debit) as debit'),
                \DB::raw('SUM(transactions.credit) as credit'))
            ->first();
        
        $totalCreditBefore = $before->credit ?? 0;
        $totalDebitBefore = $before->debit ?? 0;
        $openingBalance = $totalCreditBefore - $totalDebitBefore;

        // Employee Ledger - Between Date From and Date To
        $between = Transaction::where('transactions.payee_id', $id)
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'employee')
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->get();

        // Employee Ledger - After Date To
        $after = Transaction::where('transactions.payee_id', $id)
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'employee')
            ->where('transactions.transaction_date', '>', $dto)
            ->whereIn('transactions.transaction_type', ['advance', 'receiveAdvance', 'openingBalance'])
            ->select(\DB::raw('SUM(transactions.debit) as debit'),
                \DB::raw('SUM(transactions.credit) as credit'))
            ->first();

        $totalCreditAfter = $after->credit ?? 0;
        $totalDebitAfter = $after->debit ?? 0;
        $closingBalance = $totalCreditAfter - $totalDebitAfter;
    
        return [
            'transactions' => $between,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance
        ];
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Transaction::create($data);
        return $store->transaction_id;
    }

    public function update($id, array $data) {
        $update = Transaction::findOrFail($id);
        $update->update($data);
        return $update->transaction_id;
    }

    public function updateOB($id, $tto, array $data) {
        // Opening Balance
        $update = Transaction::where('payee_id', $id)
            ->where('transaction_type', 'openingBalance')
            ->where('transaction_to', $tto)->first();
        if($update){
            $update->update($data);
            return $update->transaction_id;
        }else{
            $data['created_by'] = auth()->id();
            $store = Transaction::create($data);
            return $store->transaction_id;
        }
    }

    public function updateDE($id, array $data) {
        // Update Delivery Expense
        $existingItems = Transaction::where('order_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            // Check if combination does not exist in the provided data
            if (!in_array($existingItem->transaction_id, $data['transaction_id'])) {
                Transaction::where('transaction_id', $existingItem->transaction_id)->delete();
            }
        }
        // Assuming you have an array of product_type_ids and material_ids indexed similarly to quantities
        foreach ($data['debit'] as $key => $debit) {
            // Assuming you have these arrays in your $data and they are indexed accordingly
            $payee = $data['payee_id'][$key] ?? null;
            $bank = $data['bank_id'][$key] ?? 0;
            $remark = $data['remarks'][$key] ?? null;
            $tid = $data['transaction_id'][$key] ?? 0;

            // Validate that both $ptid and $mid are not null
            if ($debit !== null) {
                $tItems = [
                    'transaction_to' => 'expense',
                    'transaction_date' => $data['stock_date'],
                    'transaction_type' => 'deliveryExpense',
                    'order_id' => $id,
                    'bank_id' => $bank,
                    'debit' => $debit,
                    'payee_id' => $payee,
                    'payee_bank_id' => '0',
                    'description' => $remark,
                ];
                
                $transaction = Transaction::where('transaction_id', $tid)->first();
                if ($transaction) {
                    $transaction->update($tItems);
                } else {
                    if($debit > 0){
                        $tItems['created_by'] = auth()->id();
                        Transaction::create($tItems);
                    }
                }
            }
        }
    }

    public function delete($id){}
}
