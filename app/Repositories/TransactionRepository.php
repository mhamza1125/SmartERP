<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements GlobalInterface
{
    public function all()
    {
        return Transaction::select(
            'transactions.*', 'vendor_no', 'vendors.fname', 'employee_no', 'employees.name', 'order_no', 'job_no'
        )
            ->leftJoin('vendors', function ($join) {
                $join->on('transactions.payee_id', '=', 'vendors.vendor_id')
                    ->whereIn('transactions.transaction_to', ['vendor', 'contractor']);
                // ->where('transactions.transaction_to', '=', 'vendor');
            })
            ->leftJoin('employees', function ($join) {
                $join->on('transactions.payee_id', '=', 'employees.employee_id')
                    ->where('transactions.transaction_to', '=', 'employee');
            })
            ->leftJoin('orders', function ($join) {
                $join->on('transactions.order_id', '=', 'orders.order_id')
                    ->where('transactions.transaction_to', '=', 'customer');
            })
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->orderBy('transactions.created_at', 'desc')
            ->get();
        // return Transaction::orderBy('created_at', 'desc')->get();
    }

    public function filterByDate($dfrom, $dto)
    {
        return Transaction::select(
            'transactions.*', 'vendor_no', 'vendors.fname', 'employee_no', 'employees.name', 'order_no', 'job_no'
        )
            ->leftJoin('vendors', function ($join) {
                $join->on('transactions.payee_id', '=', 'vendors.vendor_id')
                    ->whereIn('transactions.transaction_to', ['vendor', 'contractor']);
            })
            ->leftJoin('employees', function ($join) {
                $join->on('transactions.payee_id', '=', 'employees.employee_id')
                    ->where('transactions.transaction_to', '=', 'employee');
            })
            ->leftJoin('orders', function ($join) {
                $join->on('transactions.order_id', '=', 'orders.order_id')
                    ->where('transactions.transaction_to', '=', 'customer');
            })
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    public function oPayment()
    {
        return Transaction::where('transaction_to', 'customer')
            ->join('customers', 'customers.customer_id', '=', 'transactions.payee_id')
            ->join('orders', 'orders.order_id', '=', 'transactions.order_id')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    public function oPaymentFilter($dfrom, $dto, $customer_id)
    {
        $query = Transaction::where('transaction_to', 'customer')
            ->join('customers', 'customers.customer_id', '=', 'transactions.payee_id')
            ->join('orders', 'orders.order_id', '=', 'transactions.order_id');

        if (!empty($dfrom) && !empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (!empty($customer_id)) {
            $query->where('transactions.payee_id', $customer_id);
        }

        return $query->orderBy('transactions.created_at', 'desc')->get();
    }

    public function ePayment()
    {
        return Transaction::where('transaction_to', 'employee')
            ->join('employees', 'employees.employee_id', '=', 'transactions.payee_id')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    public function ePaymentFilter($dfrom, $dto, $employee_id)
    {
        $query = Transaction::where('transaction_to', 'employee')
            ->join('employees', 'employees.employee_id', '=', 'transactions.payee_id');

        if (!empty($dfrom) && !empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (!empty($employee_id)) {
            $query->where('transactions.payee_id', $employee_id);
        }

        return $query->orderBy('transactions.created_at', 'desc')->get();
    }

    public function vPayment()
    {
        return Transaction::where('transaction_to', 'vendor')
            ->join('vendors', 'vendors.vendor_id', '=', 'transactions.payee_id')
            ->orderBy('transactions.created_at', 'desc')
            ->where('vendor_type', '0')
            ->get();
    }

    public function vPaymentFilter($dfrom, $dto, $vendor_id)
    {
        $query = Transaction::where('transaction_to', 'vendor')
            ->join('vendors', 'vendors.vendor_id', '=', 'transactions.payee_id')
            ->where('vendor_type', '0');

        if (!empty($dfrom) && !empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (!empty($vendor_id)) {
            $query->where('transactions.payee_id', $vendor_id);
        }

        return $query->orderBy('transactions.created_at', 'desc')->get();
    }

    public function cPayment()
    {
        return Transaction::where('transaction_to', 'contractor')
            ->join('vendors', 'vendors.vendor_id', '=', 'transactions.payee_id')
            ->orderBy('transactions.created_at', 'desc')
            ->where('vendor_type', '1')
            ->get();
    }

    public function cPaymentFilter($dfrom, $dto, $contractor_id)
    {
        $query = Transaction::where('transaction_to', 'contractor')
            ->join('vendors', 'vendors.vendor_id', '=', 'transactions.payee_id')
            ->where('vendor_type', '1');

        if (!empty($dfrom) && !empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (!empty($contractor_id)) {
            $query->where('transactions.payee_id', $contractor_id);
        }

        return $query->orderBy('transactions.created_at', 'desc')->get();
    }

    public function expense()
    {
        return Transaction::where('transaction_to', 'expense')
            ->join('heads', 'heads.head_id', '=', 'transactions.payee_id')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    public function expenseFilter($dfrom, $dto, $head_id)
    {
        $query = Transaction::where('transaction_to', 'expense')
            ->join('heads', 'heads.head_id', '=', 'transactions.payee_id');

        if (!empty($dfrom) && !empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (!empty($head_id)) {
            $query->where('transactions.payee_id', $head_id);
        }

        return $query->orderBy('transactions.created_at', 'desc')->get();
    }

    public function generalVoucher()
    {
        return Transaction::where('transaction_type', 'generalVoucher')
            ->leftJoin('vendors', function ($join) {
                $join->on('transactions.payee_id', '=', 'vendors.vendor_id')
                    ->whereIn('transactions.transaction_to', ['vendor', 'contractor']);
            })
            ->leftJoin('employees', function ($join) {
                $join->on('transactions.payee_id', '=', 'employees.employee_id')
                    ->where('transactions.transaction_to', '=', 'employee');
            })
            ->leftJoin('customers', function ($join) {
                $join->on('transactions.payee_id', '=', 'customers.customer_id')
                    ->where('transactions.transaction_to', '=', 'customer');
            })
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    public function delivery($id)
    {
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

    public function cashTransaction()
    {
        return Transaction::where('transactions.bank_id', '0')
            ->where('transaction_type', '!=', 'openingBalance')
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->orderBy('created_at')
            ->get();
    }

    public function cashTransactionFilter($dfrom, $dto)
    {
        // Transactions Before Date From
        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.bank_id', '0')
            ->where('transaction_type', '!=', 'openingBalance')
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
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
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->whereBetween('transaction_date', [$dfrom, $dto])
            ->orderBy('created_at')
            ->get();

        // Transactions After Date To
        $transactionsAfter = \DB::table('transactions')
            ->where('transactions.bank_id', '0')
            ->where('transaction_type', '!=', 'openingBalance')
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->where('transaction_date', '>', $dto)
            ->select(\DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
            ->first();

        $totalDebitAfter = $transactionsAfter->total_debit ?? 0;
        $totalCreditAfter = $transactionsAfter->total_credit ?? 0;
        $closingBalance = $totalCreditAfter - $totalDebitAfter;

        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    public function bankTransaction($id)
    {
        return Transaction::where('transactions.bank_id', $id)
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->orderBy('created_at')
            ->get();
    }

    public function bankTransactionFilter($id, $dfrom, $dto)
    {
        // Transactions Before Date From
        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.bank_id', $id)
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->where('transaction_date', '<', $dfrom)
            ->select(\DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
            ->first();

        $totalDebitBefore = $transactionsBefore->total_debit ?? 0;
        $totalCreditBefore = $transactionsBefore->total_credit ?? 0;
        $openingBalance = $totalCreditBefore - $totalDebitBefore;

        // Transactions Between Date From and Date To
        $transactionsBetween = \DB::table('transactions')
            ->where('transactions.bank_id', $id)
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->whereBetween('transaction_date', [$dfrom, $dto])
            ->orderBy('created_at')
            ->get();

        // Transactions After Date To
        $transactionsAfter = \DB::table('transactions')
            ->where('transactions.bank_id', $id)
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->where('transaction_date', '>', $dto)
            ->select(\DB::raw('SUM(debit) as total_debit'), \DB::raw('SUM(credit) as total_credit'))
            ->first();

        $totalDebitAfter = $transactionsAfter->total_debit ?? 0;
        $totalCreditAfter = $transactionsAfter->total_credit ?? 0;
        $closingBalance = $totalCreditAfter - $totalDebitAfter;

        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    public function cashBalance()
    {
        $transaction = Transaction::where('transactions.bank_id', '0')
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->select('*',
                DB::raw('SUM(transactions.debit) AS tdebit'),
                DB::raw('SUM(transactions.credit) AS tcredit'))
            ->where('transaction_type', '!=', 'openingBalance')
            ->groupBy('transactions.bank_id')
            ->first();
        $tcredit = $transaction->tcredit ?? 0;
        $tdebit = $transaction->tdebit ?? 0;

        return $tdebit - $tcredit;
    }

    public function bankBalance()
    {
        // Banks Balance All
        return DB::table('banks')->where('banks.banker_id', '0')
            ->leftJoin('transactions', 'banks.bank_id', '=', 'transactions.bank_id')
            ->where('transactions.ledger_flag', '!=', 0)  // Exclude general vouchers
            ->join('heads', 'heads.head_id', '=', 'banks.head_id')
            ->select('banks.*', 'heads.name as hname',
                DB::raw('SUM(transactions.debit) AS tdebit'),
                DB::raw('SUM(transactions.credit) AS tcredit'),
                DB::raw('COALESCE(SUM(CAST(transactions.debit AS SIGNED) - CAST(transactions.credit AS SIGNED)), 0) as balance')
                // DB::raw('COALESCE(SUM(transactions.debit - transactions.credit), 0) as balance'))
            )
            ->groupBy('banks.bank_id')
            // ->havingRaw('balance = 0')
            ->get();

        // Dosen't show the Banks with 0 Transactions
        return Transaction::where('transactions.bank_id', '>', '0')
            ->where('ledger_flag', '!=', 0)  // Exclude general vouchers
            ->join('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->join('heads', 'heads.head_id', '=', 'banks.head_id')
            ->select('*', 'transactions.bank_id', 'heads.name as hname',
                DB::raw('SUM(transactions.debit) AS tdebit'),
                DB::raw('SUM(transactions.credit) AS tcredit'))
            ->groupBy('transactions.bank_id')
            ->get();
    }

    public function bankBalance2($id)
    {
        return DB::table('banks')->where('banks.bank_id', $id)
            ->leftJoin('transactions', 'banks.bank_id', '=', 'transactions.bank_id')
            ->where('transactions.ledger_flag', '!=', 0)  // Exclude general vouchers
            ->join('heads', 'heads.head_id', '=', 'banks.head_id')
            ->select('banks.*', 'heads.name as hname',
                DB::raw('SUM(transactions.debit) AS tdebit'),
                DB::raw('SUM(transactions.credit) AS tcredit'),
                DB::raw('COALESCE(SUM(CAST(transactions.debit AS SIGNED) - CAST(transactions.credit AS SIGNED)), 0) as balance'))
            ->groupBy('banks.bank_id')
            ->first();
    }

    public function get($id)
    {
    }

    public function getExpense($id)
    {
        return Transaction::where('transaction_id', $id)
            ->join('heads', 'heads.head_id', 'transactions.payee_id')
            ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
            ->select('transactions.*', 'heads.name as hname', 'bhead.name as bname', 'banks.account', 'banks.account_title')
            ->first();
    }

    public function getEPayment($id) // Employee Payment
    {
        return Transaction::where('transaction_id', $id)
            ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
            ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
            ->join('employees', 'employees.employee_id', 'transactions.payee_id')
            ->select('transactions.*', 'employees.employee_no', 'employees.name', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'banks.account', 'banks.account_title')
            ->first();
    }

    public function getVPayment($id) // Vendor Payment
    {
        return Transaction::where('transaction_id', $id)
            ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
            ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
            ->join('vendors', 'vendors.vendor_id', 'transactions.payee_id')
            ->leftJoin('purchases', 'purchases.purchase_id', 'transactions.order_id')
            ->select('transactions.*', 'vendors.vendor_no', 'vendors.fname', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'banks.account', 'banks.account_title', 'purchases.purchase_no')
            ->first();
    }

    public function getOPayment($id) // Order Payment
    {
        return Transaction::where('transaction_id', $id)
            ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->leftJoin('banks as rbank', 'rbank.bank_id', '=', 'transactions.payee_bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
            ->leftJoin('heads as rhead', 'rhead.head_id', 'banks.head_id')
            ->join('customers', 'customers.customer_id', 'transactions.payee_id')
            ->leftJoin('heads as chead', 'chead.head_id', '=', 'customers.currency_id')
            ->join('orders', 'orders.order_id', 'transactions.order_id')
            ->select('transactions.*', 'customers.customer_no', 'customers.fname', 'orders.order_date', 'orders.order_no', 'chead.name as cname', 'rhead.name as rname', 'bhead.name as bname', 'rbank.account as raccount', 'rbank.account_title as raccount_title', 'banks.account', 'banks.account_title')
            ->first();
    }

    public function getGeneralVoucher($id) // General Voucher
    {
        return Transaction::where('transaction_id', $id)
            ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
            ->leftJoin('vendors', function ($join) {
                $join->on('transactions.payee_id', '=', 'vendors.vendor_id')
                    ->whereIn('transactions.transaction_to', ['vendor', 'contractor']);
            })
            ->leftJoin('employees', function ($join) {
                $join->on('transactions.payee_id', '=', 'employees.employee_id')
                    ->where('transactions.transaction_to', '=', 'employee');
            })
            ->leftJoin('customers', function ($join) {
                $join->on('transactions.payee_id', '=', 'customers.customer_id')
                    ->where('transactions.transaction_to', '=', 'customer');
            })
            ->select('transactions.*', 'vendors.vendor_no', 'vendors.fname as vendor_name', 'employees.employee_no', 'employees.name as employee_name', 'customers.customer_no', 'customers.fname as customer_name', 'bhead.name as bname', 'banks.account', 'banks.account_title')
            ->first();
    }

    public function getPPayment($id) // Purchase Payment
    {
        // Get all vendor/contractor payments for a specific purchase
        // Payments are stored with transaction_to='vendor' or 'contractor' and order_id=purchase_id
        return Transaction::where('order_id', $id)
            ->whereIn('transaction_to', ['vendor', 'contractor'])
            ->orderBy('transaction_date', 'asc')
            ->get();
    }

    public function getBRS($id)
    {
        return Transaction::where('transaction_id', $id)
            ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
            ->select('transactions.*', 'bhead.name as bname', 'banks.account', 'banks.account_title')
            ->first();
    }

    public function getTransfer($id)
    {
        return Transaction::where('transaction_id', $id)
            ->leftJoin('banks', 'banks.bank_id', '=', 'transactions.bank_id')
            ->leftJoin('heads as bhead', 'bhead.head_id', 'banks.head_id')
            ->select('transactions.*', 'bhead.name as bname', 'banks.account', 'banks.account_title')
            ->first();
    }

    public function vDetail($id)
    {
        // Vendor Ledger
        // $purchases = \DB::table('purchases') // Amount of Purchase Items, Regardless of Receiving
        //     ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(purchase_items.total) as credit'))
        //     ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
        //     ->where('purchases.vendor_id', $id)
        //     ->groupBy('purchases.purchase_id')
        //     ->get();

        // Vendor Ledger - Amount of Received Purchase Items
        // Purchases increase liability, so store as debit (displays in Credit column after reversal)
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.quantity * purchase_items.price) as debit'))
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->where('purchases.vendor_id', $id)
            ->groupBy('purchases.purchase_id')
            ->get();

        // Vendor Ledger - Purchase Returns
        // Purchase returns reduce liability, so store as credit (displays in Debit column after reversal)
        $purchaseReturns = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->select('*', 'returns.created_at as timestamp', \DB::raw('SUM(return_materials.quantity * purchase_items.price) as credit'))
            ->where('purchases.vendor_id', $id)
            ->groupBy('purchases.purchase_id')
            ->get();

        // Vendor Ledger - Transactions
        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->whereIn('transactions.transaction_to', ['vendor', 'contractor'])
            // ->where('transactions.transaction_to', 'vendor')
            ->get();

        $return = $purchases->concat($purchaseReturns)->concat($transactions);
        $sorted = $return->sortBy('timestamp');

        return $sorted;
    }

    public function vDetailFilter($id, $dfrom, $dto)
    {
        // Vendor Ledger - Before Date From (Opening Balance)
        // For vendor/contractor ledger (liability account):
        // DB debit = purchases/work done, increases liability = ADD to balance
        // DB credit = payments made, decreases liability = SUBTRACT from balance
        // Include ALL transaction types - no filtering

        // Purchases increase liability (stored as debit)
        $purchasesBefore = \DB::table('purchases')
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->where('purchases.vendor_id', $id)
            ->where('purchases.purchase_date', '<', $dfrom)
            ->select(\DB::raw('SUM(receive_materials.quantity * purchase_items.price) as debit'))
            ->first();

        // Purchase returns decrease liability (stored as credit conceptually, but reduces debit)
        $purchaseReturnsBefore = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->where('purchases.vendor_id', $id)
            ->where('returns.return_date', '<', $dfrom)
            ->select(\DB::raw('SUM(return_materials.quantity * purchase_items.price) as credit'))
            ->first();

        // Transactions - include ALL types (no wages filter)
        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_date', '<', $dfrom)
            ->whereIn('transactions.transaction_to', ['vendor', 'contractor'])
            ->select(\DB::raw('SUM(transactions.debit) as debit'), \DB::raw('SUM(transactions.credit) as credit'))
            ->first();

        // Opening Balance = DB Debit - DB Credit (for liability accounts)
        $totalDebitBefore = ($purchasesBefore->debit ?? 0) + ($transactionsBefore->debit ?? 0);
        $totalCreditBefore = ($purchaseReturnsBefore->credit ?? 0) + ($transactionsBefore->credit ?? 0);
        $openingBalance = $totalDebitBefore - $totalCreditBefore;

        // Vendor Ledger - Between Date From and Date To
        // Purchases increase liability, so store as debit (displays in Credit column after reversal)
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.quantity * purchase_items.price) as debit'))
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
            ->select('*', 'returns.created_at as timestamp', \DB::raw('SUM(return_materials.quantity * purchase_items.price) as credit'))
            ->where('purchases.vendor_id', $id)
            ->whereBetween('returns.return_date', [$dfrom, $dto])
            ->groupBy('purchases.purchase_id')
            ->get();

        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->whereIn('transactions.transaction_to', ['vendor', 'contractor'])
            // ->where('transactions.transaction_to', 'vendor')
            ->get();

        $transactionsBetween = $purchases->concat($purchaseReturns)->concat($transactions)->sortBy('timestamp');

        // Closing balance is not needed - the running balance in the view handles it
        $closingBalance = 0;

        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    public function cDetail($id)
    {
        // Customer Ledger - Show deliveries in customer currency
        // Get deliveries with their delivered amounts based on stocks and stock_items
        // price is now customer currency (was price2 before)
        $deliveries = \DB::table('stocks')
            ->select(
                'stocks.stock_id',
                'stocks.stock_no',
                'stocks.stock_date',
                'deliveries.delivery_id',
                'stocks.created_at as timestamp',
                \DB::raw('COALESCE(SUM(order_items.price * stock_items.quantity), 0) as debit')
            )
            ->join('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('order_items', function($join) {
                $join->on('order_items.order_id', '=', 'stocks.order_id')
                    ->on('order_items.product_type_id', '=', 'stock_items.product_type_id');
            })
            ->where('orders.customer_id', $id)
            ->where('stocks.stock_status', '=', 3) // Delivery status
            ->groupBy('stocks.stock_id')
            ->get();

        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'customer')
            ->get();

        $return = $deliveries->concat($transactions);
        $sorted = $return->sortBy('timestamp');

        return $sorted;
    }

    public function cDetailFilter($id, $dfrom, $dto)
    {
        // Customer Ledger - Before Date From (show only deliveries and transactions)
        // For general vouchers with cc_amount, we need to handle them separately
        $deliveriesBefore = \DB::table('stocks')
            ->join('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('order_items', function($join) {
                $join->on('order_items.order_id', '=', 'stocks.order_id')
                    ->on('order_items.product_type_id', '=', 'stock_items.product_type_id');
            })
            ->where('orders.customer_id', $id)
            ->where('stocks.stock_date', '<', $dfrom)
            ->where('stocks.stock_status', '=', 3) // Delivery status
            ->select(\DB::raw('COALESCE(SUM(order_items.price * stock_items.quantity), 0) as debit'))
            ->first();

        $transactionsBefore = \DB::table('transactions')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_date', '<', $dfrom)
            ->where('transactions.transaction_to', 'customer')
            ->select(
                \DB::raw('SUM(transactions.debit) as debit'),
                \DB::raw('SUM(transactions.credit) as credit')
            )
            ->first();

        $totalDebitBefore = ($deliveriesBefore->debit ?? 0) + ($transactionsBefore->debit ?? 0);
        $totalCreditBefore = $transactionsBefore->credit ?? 0;
        $openingBalance = $totalCreditBefore - $totalDebitBefore;

        // Customer Ledger - Between Date From and Date To
        $deliveries = \DB::table('stocks')
            ->select(
                'stocks.stock_id',
                'stocks.stock_no',
                'stocks.stock_date',
                'deliveries.delivery_id',
                'stocks.created_at as timestamp',
                \DB::raw('COALESCE(SUM(order_items.price * stock_items.quantity), 0) as debit')
            )
            ->join('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('order_items', function($join) {
                $join->on('order_items.order_id', '=', 'stocks.order_id')
                    ->on('order_items.product_type_id', '=', 'stock_items.product_type_id');
            })
            ->where('orders.customer_id', $id)
            ->whereBetween('stocks.stock_date', [$dfrom, $dto])
            ->where('stocks.stock_status', '=', 3) // Delivery status
            ->groupBy('stocks.stock_id')
            ->get();

        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'customer')
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->get();

        $transactionsBetween = $deliveries->concat($transactions)->sortBy('timestamp');

        // Customer Ledger - After Date To (show only deliveries and transactions)
        $deliveriesAfter = \DB::table('stocks')
            ->join('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('order_items', function($join) {
                $join->on('order_items.order_id', '=', 'stocks.order_id')
                    ->on('order_items.product_type_id', '=', 'stock_items.product_type_id');
            })
            ->where('orders.customer_id', $id)
            ->where('stocks.stock_date', '>', $dto)
            ->where('stocks.stock_status', '=', 3) // Delivery status
            ->select(\DB::raw('COALESCE(SUM(order_items.price * stock_items.quantity), 0) as debit'))
            ->first();

        $transactionsAfter = \DB::table('transactions')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_date', '>', $dto)
            ->where('transactions.transaction_to', 'customer')
            ->select(
                \DB::raw('SUM(transactions.debit) as debit'),
                \DB::raw('SUM(transactions.credit) as credit')
            )
            ->first();

        $totalDebitAfter = ($deliveriesAfter->debit ?? 0) + ($transactionsAfter->debit ?? 0);
        $totalCreditAfter = $transactionsAfter->credit ?? 0;
        $closingBalance = $totalCreditAfter - $totalDebitAfter;

        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }





    public function eDetail($id)
    {
        // Employee Ledger
        return Transaction::where('transactions.payee_id', $id)
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'employee')
            ->orderBy('transactions.created_at')
            ->get();
    }

    public function eDetailFilter($id, $dfrom, $dto)
    {
        // Employee Ledger - Before Date From (Opening Balance)
        // For employee ledger (liability account):
        // DB debit = work done, increases liability = ADD to balance
        // DB credit = payments made, decreases liability = SUBTRACT from balance
        // Include ALL transaction types - no filtering
        $before = Transaction::where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'employee')
            ->where('transactions.transaction_date', '<', $dfrom)
            ->select(\DB::raw('SUM(transactions.debit) as debit'),
                \DB::raw('SUM(transactions.credit) as credit'))
            ->first();

        $totalDebitBefore = $before->debit ?? 0;
        $totalCreditBefore = $before->credit ?? 0;
        $openingBalance = $totalDebitBefore - $totalCreditBefore;

        // Employee Ledger - Between Date From and Date To
        $between = Transaction::where('transactions.payee_id', $id)
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'employee')
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->get();

        // Closing balance is not needed - the running balance in the view handles it
        $closingBalance = 0;

        return [
            'transactions' => $between,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    public function sAdvance($dfrom, $dto)
    {
        // Employee Ledger - Between Date From and Date To
        return Transaction::groupBy('payee_id', 'transaction_to')
            ->where('transactions.transaction_to', 'employee')
            ->whereBetween('transactions.transaction_date', [$dfrom, $dto])
            ->where('transactions.transaction_type', 'salaryAdvance')
            ->select('transactions.*', \DB::raw('SUM(transactions.debit) as debit'),
                \DB::raw('SUM(transactions.credit) as credit'))
            ->get();
    }

    public function pLedger()
    {
        // Purchase Ledger - All purchases, returns, and vendor payments
        // Purchases increase liability, so store as debit (displays in Debit column)
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.quantity * purchase_items.price) as debit'), \DB::raw('0 as credit'))
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->groupBy('purchases.purchase_id')
            ->get();

        // Purchase Returns - reduce liability, so store as credit (displays in Credit column)
        $purchaseReturns = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->select('returns.*', 'returns.created_at as timestamp', \DB::raw('0 as debit'), \DB::raw('SUM(return_materials.quantity * purchase_items.price) as credit'))
            ->groupBy('returns.return_id')
            ->get();

        // Vendor Payments - reduce liability, so store as credit (displays in Credit column)
        $transactions = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.transaction_to', 'vendor')
            ->get();

        $return = $purchases->concat($purchaseReturns)->concat($transactions);
        $sorted = $return->sortBy('timestamp');

        return $sorted;
    }

    public function pLedgerFilter($dfrom, $dto)
    {
        // Purchase Ledger - Before Date From (Opening Balance)
        // For purchase ledger (liability account):
        // DB debit = purchases, increases liability = ADD to balance
        // DB credit = returns/payments, decreases liability = SUBTRACT from balance
        $before = \DB::table('purchases')
            ->select(\DB::raw('SUM(receive_materials.quantity * purchase_items.price) as debit'), \DB::raw('0 as credit'))
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->where('purchases.purchase_date', '<', $dfrom)
            ->first();

        $returnsBefore = \DB::table('returns')
            ->select(\DB::raw('0 as debit'), \DB::raw('SUM(return_materials.quantity * purchase_items.price) as credit'))
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->where('returns.created_at', '<', $dfrom)
            ->first();

        $transactionsBefore = Transaction::where('transaction_to', 'vendor')
            ->where('transaction_date', '<', $dfrom)
            ->select(\DB::raw('SUM(debit) as debit'), \DB::raw('SUM(credit) as credit'))
            ->first();

        $totalDebitBefore = ($before->debit ?? 0) + ($transactionsBefore->debit ?? 0);
        $totalCreditBefore = ($returnsBefore->credit ?? 0) + ($transactionsBefore->credit ?? 0);
        $openingBalance = $totalDebitBefore - $totalCreditBefore;

        // Purchase Ledger - Between Date From and Date To
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.quantity * purchase_items.price) as debit'), \DB::raw('0 as credit'))
            ->join('purchase_items', 'purchase_items.purchase_id', '=', 'purchases.purchase_id')
            ->join('receive_materials', 'purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->whereBetween('purchases.purchase_date', [$dfrom, $dto])
            ->groupBy('purchases.purchase_id')
            ->get();

        $purchaseReturns = \DB::table('returns')
            ->join('return_materials', 'return_materials.return_id', '=', 'returns.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->select('returns.*', 'returns.created_at as timestamp', \DB::raw('0 as debit'), \DB::raw('SUM(return_materials.quantity * purchase_items.price) as credit'))
            ->whereBetween('returns.created_at', [$dfrom, $dto])
            ->groupBy('returns.return_id')
            ->get();

        $transactions = Transaction::where('transaction_to', 'vendor')
            ->whereBetween('transaction_date', [$dfrom, $dto])
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->get();

        $return = $purchases->concat($purchaseReturns)->concat($transactions);
        $sorted = $return->sortBy('timestamp');

        $closingBalance = 0;

        return [
            'transactions' => $sorted,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Transaction::create($data);

        return $store->transaction_id;
    }

    public function update($id, array $data)
    {
        $update = Transaction::findOrFail($id);
        $update->update($data);

        return $update->transaction_id;
    }

    public function getOB($id, $tto)
    {
        // Fetch opening balance transaction for a specific payee
        return Transaction::where('payee_id', $id)
            ->where('transaction_type', 'openingBalance')
            ->where('transaction_to', $tto)->first();
    }

    public function updateOB($id, $tto, array $data)
    {
        // Opening Balance
        $update = Transaction::where('payee_id', $id)
            ->where('transaction_type', 'openingBalance')
            ->where('transaction_to', $tto)->first();
        if ($update) {
            $update->update($data);

            return $update->transaction_id;
        } else {
            $data['created_by'] = auth()->id();
            $store = Transaction::create($data);

            return $store->transaction_id;
        }
    }

    public function updateDE($id, array $data)
    {
        // Update Delivery Expense
        $existingItems = Transaction::where('order_id', $id)->get();

        // Get transaction IDs from data (if they exist)
        $dataTransactionIds = $data['transaction_id'] ?? [];

        foreach ($existingItems as $existingItem) {
            // Check if combination does not exist in the provided data
            if (! in_array($existingItem->transaction_id, $dataTransactionIds)) {
                Transaction::where('transaction_id', $existingItem->transaction_id)->delete();
            }
        }

        // Check if debit array exists before processing
        if (isset($data['debit']) && is_array($data['debit'])) {
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
                        if ($debit > 0) {
                            $tItems['created_by'] = auth()->id();
                            Transaction::create($tItems);
                        }
                    }
                }
            }
        }
    }

    public function delete($id)
    {
    }
}
