<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Collection;
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

        if (! empty($dfrom) && ! empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (! empty($customer_id)) {
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

        if (! empty($dfrom) && ! empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (! empty($employee_id)) {
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

        if (! empty($dfrom) && ! empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (! empty($vendor_id)) {
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

        if (! empty($dfrom) && ! empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (! empty($contractor_id)) {
            $query->where('transactions.payee_id', $contractor_id);
        }

        return $query->orderBy('transactions.created_at', 'desc')->get();
    }

    public function expense()
    {
        return Transaction::whereIn('transaction_to', ['expense', 'bankCharges'])
            ->join('heads', 'heads.head_id', '=', 'transactions.payee_id')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    public function expenseFilter($dfrom, $dto, $head_id)
    {
        $query = Transaction::where('transaction_to', 'expense')
            ->join('heads', 'heads.head_id', '=', 'transactions.payee_id');

        if (! empty($dfrom) && ! empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (! empty($head_id)) {
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

    public function get($id) {}

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
            ->leftJoin('heads as rhead', 'rhead.head_id', 'rbank.head_id')
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
            ->leftJoin('heads as rhead', 'rhead.head_id', 'rbank.head_id')
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
            ->leftJoin('heads as rhead', 'rhead.head_id', 'rbank.head_id')
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
                \DB::raw('COALESCE(MAX(deliveries.freight_charges), 0) as freight_charges'),
                \DB::raw('COALESCE(SUM(order_items.price * stock_items.quantity), 0) + COALESCE(MAX(deliveries.freight_charges), 0) as debit')
            )
            ->join('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('order_items', function ($join) {
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
            ->leftJoin('order_items', function ($join) {
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

        $totalDebitBefore = ($deliveriesBefore->debit ?? 0) + $this->customerFreight($id, fn ($q) => $q->where('stocks.stock_date', '<', $dfrom)) + ($transactionsBefore->debit ?? 0);
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
                \DB::raw('COALESCE(MAX(deliveries.freight_charges), 0) as freight_charges'),
                \DB::raw('COALESCE(SUM(order_items.price * stock_items.quantity), 0) + COALESCE(MAX(deliveries.freight_charges), 0) as debit')
            )
            ->join('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->leftJoin('order_items', function ($join) {
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
            ->leftJoin('order_items', function ($join) {
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

        $totalDebitAfter = ($deliveriesAfter->debit ?? 0) + $this->customerFreight($id, fn ($q) => $q->where('stocks.stock_date', '>', $dto)) + ($transactionsAfter->debit ?? 0);
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

    public function delete($id) {}

    /**
     * Total freight charged on a customer's deliveries (stock_status 3), narrowed by $filter.
     * Kept separate from the item sums because those are joined per stock line.
     */
    private function customerFreight($customerId, callable $filter): float
    {
        $query = \DB::table('deliveries')
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->join('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->where('orders.customer_id', $customerId)
            ->where('stocks.stock_status', '=', 3);
        $filter($query);

        return (float) $query->sum('deliveries.freight_charges');
    }

    /** Display names of transaction_type values */
    public const TYPE_LABELS = [
        'payment' => 'Payment',
        'wages' => 'Wages',
        'salary' => 'Salary',
        'advance' => 'Loan Given',
        'receiveAdvance' => 'Loan Received',
        'openingBalance' => 'Opening Balance',
        'orderPayment' => 'Order Payment',
        'expense' => 'Expense',
        'bankCharges' => 'Bank Charges',
        'deliveryExpense' => 'Delivery Expense',
        'transfer' => 'Bank / Cash Transfer',
        'brs' => 'Balance Adjustment',
        'generalVoucher' => 'General Voucher',
    ];

    /**
     * What each transaction was about, for printed lists, ledgers and vouchers where
     * nothing can be clicked: who it was with, what it was for (purchase / order /
     * delivery / expense head), which account it went through, the cheque number
     * (stored in the description as "Check No: x") and the rest of the description.
     *
     * Looks everything up in batches. Keyed by transaction_id; each value:
     *   type       type label ("Payment", "Loan Given", ...)
     *   party      "V0003 - Ashraf" / expense head name / ... (null when none)
     *   cheque_no  cheque number or null
     *   note       description without the cheque part, or null
     *   narration  one line: "Payment to V0003 - Ashraf · Purchase P2611003 · via Meezan Bank - ... · Cheque 123 · note"
     *   summary    the narration without its leading "Payment to V0003 - Ashraf" part
     *   details    [label => value] for vouchers (empty values left out)
     */
    public function describe(iterable $transactionIds): Collection
    {
        $ids = collect($transactionIds)->filter()->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $rows = DB::table('transactions')->whereIn('transaction_id', $ids)->get()->keyBy('transaction_id');
        $pairIds = $rows->pluck('transfer_pair_id')->filter()->map(fn ($id) => (int) $id)->diff($ids);
        $pairs = $pairIds->isEmpty() ? collect() : DB::table('transactions')->whereIn('transaction_id', $pairIds)->get()->keyBy('transaction_id');
        $all = $rows->union($pairs);

        $of = fn (array $tos) => $rows->filter(fn ($row) => in_array($row->transaction_to, $tos, true));
        $banks = DB::table('banks')
            ->leftJoin('heads', 'heads.head_id', '=', 'banks.head_id')
            ->whereIn('banks.bank_id', $all->pluck('bank_id')->merge($all->pluck('payee_bank_id'))->filter()->unique())
            ->get(['banks.bank_id', 'banks.account', 'banks.account_title', 'heads.name as bank_name'])
            ->keyBy('bank_id');
        $vendors = DB::table('vendors')->whereIn('vendor_id', $of(['vendor', 'contractor'])->pluck('payee_id'))->get(['vendor_id', 'vendor_no', 'fname'])->keyBy('vendor_id');
        $employees = DB::table('employees')->whereIn('employee_id', $of(['employee'])->pluck('payee_id'))->get(['employee_id', 'employee_no', 'name'])->keyBy('employee_id');
        $customers = DB::table('customers')->whereIn('customer_id', $of(['customer'])->pluck('payee_id'))->get(['customer_id', 'customer_no', 'fname', 'lname'])->keyBy('customer_id');
        $heads = DB::table('heads')->whereIn('head_id', $of(['expense'])->pluck('payee_id'))->pluck('name', 'head_id');
        $purchases = DB::table('purchases')->whereIn('purchase_id', $of(['vendor', 'contractor'])->pluck('order_id')->filter())->pluck('purchase_no', 'purchase_id');
        $orders = DB::table('orders')->whereIn('order_id', $of(['customer'])->pluck('order_id')->filter())->get(['order_id', 'order_no', 'job_no'])->keyBy('order_id');
        $deliveries = DB::table('deliveries')
            ->whereIn('delivery_id', $rows->where('transaction_type', 'deliveryExpense')->pluck('order_id')->filter())
            ->pluck('delivery_no', 'delivery_id');

        // "Cash" for bank_id 0, otherwise "Bank name - Account title - A/C number"
        $account = function ($bankId) use ($banks) {
            if (! $bankId) {
                return 'Cash';
            }
            $bank = $banks->get($bankId);

            return $bank ? collect([$bank->bank_name, $bank->account_title, $bank->account ? 'A/C '.$bank->account : null])->filter()->implode(' - ') : null;
        };
        $amount = fn ($value) => $value !== null && (float) $value != 0 ? number_format((float) $value, 2) : null;

        return $rows->map(function ($row) use ($all, $account, $amount, $vendors, $employees, $customers, $heads, $purchases, $orders, $deliveries) {
            [$note, $cheque] = $this->splitCheque($row->description);
            $type = self::TYPE_LABELS[$row->transaction_type] ?? ucfirst((string) $row->transaction_type);

            $party = null;
            if (in_array($row->transaction_to, ['vendor', 'contractor'], true) && ($vendor = $vendors->get($row->payee_id))) {
                $party = trim($vendor->vendor_no.' - '.$vendor->fname);
            } elseif ($row->transaction_to === 'employee' && ($employee = $employees->get($row->payee_id))) {
                $party = trim($employee->employee_no.' - '.$employee->name);
            } elseif ($row->transaction_to === 'customer' && ($customer = $customers->get($row->payee_id))) {
                $party = trim($customer->customer_no.' - '.$customer->fname.' '.$customer->lname);
            } elseif ($row->transaction_to === 'expense') {
                $party = $heads->get($row->payee_id);
            }
            $partyLabel = [
                'vendor' => 'Vendor', 'contractor' => 'Contractor', 'employee' => 'Employee',
                'customer' => 'Customer', 'expense' => 'Expense Head',
            ][$row->transaction_to] ?? 'Party';
            $incoming = in_array($row->transaction_type, ['receiveAdvance', 'orderPayment'], true);
            $noAccount = in_array($row->transaction_type, ['openingBalance', 'generalVoucher'], true);

            $details = ['Type' => $type, $partyLabel => $party];
            $parts = [];

            if ($row->transaction_type === 'transfer') {
                $pair = $all->get($row->transfer_pair_id);
                // The credit side is the account the money left
                $outRow = $row->credit !== null && (float) $row->credit != 0 ? $row : $pair;
                $inRow = $outRow === $row ? $pair : $row;
                $from = $outRow ? $account($outRow->bank_id) : null;
                $to = $inRow ? $account($inRow->bank_id) : null;
                $details['From Account'] = $from;
                $details['To Account'] = $to;
                $parts[] = 'Transfer from '.($from ?? '-').' to '.($to ?? '-');
            } elseif ($row->transaction_to === 'brs') {
                $details['Account'] = $account($row->bank_id);
                $parts[] = 'Balance adjustment on '.$account($row->bank_id).' ('.((float) $row->debit > 0 ? 'increase' : 'decrease').')';
            } elseif ($row->transaction_to === 'expense') {
                $parts[] = $type.($party && strcasecmp(trim($party), $type) !== 0 ? ': '.$party : '');
                if ($row->transaction_type === 'deliveryExpense' && $deliveries->get($row->order_id)) {
                    $details['Delivery No'] = $deliveries->get($row->order_id);
                    $parts[] = 'Delivery '.$deliveries->get($row->order_id);
                }
                $details['Paid From'] = $account($row->bank_id);
                $parts[] = 'paid from '.$account($row->bank_id);
            } else {
                $parts[] = $type.($party ? ($noAccount ? ' - ' : ($incoming ? ' from ' : ' to ')).$party : '');
                if (in_array($row->transaction_to, ['vendor', 'contractor'], true) && $purchases->get($row->order_id)) {
                    $details['Purchase No'] = $purchases->get($row->order_id);
                    $parts[] = 'Purchase '.$purchases->get($row->order_id);
                }
                if ($row->transaction_to === 'customer' && ($order = $orders->get($row->order_id))) {
                    $details['Order No'] = $order->order_no;
                    $details['Job No'] = $order->job_no;
                    $parts[] = 'Order '.$order->order_no.($order->job_no ? ' ('.$order->job_no.')' : '');
                }
                if (! $noAccount) {
                    $details[$incoming ? 'Received Into' : 'Paid From'] = $account($row->bank_id);
                    $parts[] = ($incoming ? 'into ' : 'via ').$account($row->bank_id);
                    if ($row->payee_bank_id) {
                        $details[$incoming ? 'Party Account (From)' : 'Party Account (To)'] = $account($row->payee_bank_id);
                    }
                }
                if ($row->transaction_to === 'customer') {
                    $details['Amount (Customer Currency)'] = $amount($row->cc_amount);
                    $details['Bank Fees'] = $amount($row->fb_charges);
                    if ($amount($row->fb_charges)) {
                        $parts[] = 'fees '.$amount($row->fb_charges);
                    }
                }
            }

            $details['Cheque No'] = $cheque;
            $details['Description'] = $note;
            if ($cheque) {
                $parts[] = 'Cheque '.$cheque;
            }
            if ($note) {
                $parts[] = $note;
            }

            return (object) [
                'type' => $type,
                'party' => $party,
                'cheque_no' => $cheque,
                'note' => $note,
                'narration' => implode(' · ', array_filter($parts, fn ($part) => trim((string) $part) !== '')),
                // Without the leading "Type to party" part, for lists that already show both
                'summary' => implode(' · ', array_filter(array_slice($parts, 1), fn ($part) => trim((string) $part) !== '')),
                'details' => array_filter($details, fn ($value) => trim((string) $value) !== ''),
            ];
        });
    }

    /**
     * Description without its "Check No: x" / "Cheque No: x" part, and that cheque number.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function splitCheque(?string $description): array
    {
        $text = trim(strip_tags((string) $description));
        if (preg_match('/\s*-?\s*\b(?:check|cheque|chq)\b\s*(?:no\.?|number|#)?\s*[:#]?\s*([A-Za-z0-9\-\/]+)/i', $text, $match)) {
            $text = trim(str_replace($match[0], '', $text), " -\t\n");

            return [$text !== '' ? $text : null, $match[1]];
        }

        return [$text !== '' ? $text : null, null];
    }

    /**
     * A readable description for every row of a people ledger (customer / vendor / contractor /
     * employee), for the printed ledger where rows cannot be opened:
     *   transaction       type + purchase / order, account, cheque, note (see describe(); the party is the ledger itself)
     *   purchase (vendor) "Purchase P2611003 received: Spatula Plastic Handle (160 MM) x 100, ..."
     *   return (vendor)   "Return R-1 on purchase P2611003: Aluminium x 25"
     *   delivery          "Delivery SLE-D001-26 · Order X (job) · Funnel (20MM) x 10 · incl. freight 20.00"
     *
     * @return Collection<int|string, string> keyed like $rows
     */
    public function ledgerDescriptions(iterable $rows): Collection
    {
        $rows = collect($rows);
        $info = $this->describe($rows->pluck('transaction_id')->filter());

        $purchaseIds = $rows->filter(fn ($row) => empty($row->transaction_id) && ! empty($row->purchase_id) && empty($row->return_id))->pluck('purchase_id');
        $returnIds = $rows->filter(fn ($row) => empty($row->transaction_id) && ! empty($row->return_id))->pluck('return_id');
        $deliveryStockIds = $rows->filter(fn ($row) => empty($row->transaction_id) && ! empty($row->delivery_id))->pluck('stock_id');

        $itemName = "COALESCE(materials.name, CONCAT(products.name, IF(size.name IS NULL, '', CONCAT(' (', size.name, ')'))))";
        $withNames = fn ($query, $productColumn, $materialColumn) => $query
            ->leftJoin('materials', function ($join) use ($materialColumn) {
                $join->on('materials.material_id', '=', $materialColumn)->where($materialColumn, '>', 0);
            })
            ->leftJoin('product_types', 'product_types.product_type_id', '=', $productColumn)
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as size', 'size.head_id', '=', 'product_types.size_id');

        $purchaseItems = $purchaseIds->isEmpty() ? collect() : $withNames(
            DB::table('purchase_items')->join('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id'),
            'purchase_items.product_type_id', 'purchase_items.material_id'
        )
            ->whereIn('purchase_items.purchase_id', $purchaseIds)
            ->groupBy('purchase_items.purchase_item_id', 'purchase_items.purchase_id', DB::raw($itemName))
            ->selectRaw("purchase_items.purchase_id as doc_id, {$itemName} as item, SUM(receive_materials.quantity) as qty")
            ->get()->groupBy('doc_id');

        $returnItems = $returnIds->isEmpty() ? collect() : $withNames(
            DB::table('return_materials')
                ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
                ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id'),
            'purchase_items.product_type_id', 'purchase_items.material_id'
        )
            ->whereIn('return_materials.return_id', $returnIds)
            ->groupBy('return_materials.return_id', 'purchase_items.purchase_item_id', DB::raw($itemName))
            ->selectRaw("return_materials.return_id as doc_id, {$itemName} as item, SUM(return_materials.quantity) as qty")
            ->get()->groupBy('doc_id');

        $deliveryItems = $deliveryStockIds->isEmpty() ? collect() : $withNames(
            DB::table('stock_items'),
            'stock_items.product_type_id', 'stock_items.material_id'
        )
            ->whereIn('stock_items.stock_id', $deliveryStockIds)
            ->groupBy('stock_items.stock_id', 'stock_items.product_type_id', 'stock_items.material_id', DB::raw($itemName))
            ->selectRaw("stock_items.stock_id as doc_id, {$itemName} as item, SUM(stock_items.quantity) as qty")
            ->get()->groupBy('doc_id');
        $deliveries = $deliveryStockIds->isEmpty() ? collect() : DB::table('deliveries')
            ->join('stocks', 'stocks.stock_id', '=', 'deliveries.stock_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->whereIn('deliveries.stock_id', $deliveryStockIds)
            ->get(['deliveries.stock_id', 'deliveries.delivery_no', 'deliveries.freight_charges', 'orders.order_no', 'orders.job_no'])
            ->keyBy('stock_id');

        // "Item x 10, Item x 5, +2 more"
        $itemList = function ($items) {
            if (! $items || $items->isEmpty()) {
                return null;
            }
            $shown = $items->take(4)->map(fn ($item) => trim($item->item ?? 'Item').' x '.(float) $item->qty)->implode(', ');

            return $shown.($items->count() > 4 ? ', +'.($items->count() - 4).' more' : '');
        };

        return $rows->map(function ($row) use ($info, $purchaseItems, $returnItems, $deliveryItems, $deliveries, $itemList) {
            if (! empty($row->transaction_id)) {
                $item = $info->get($row->transaction_id);

                return $item ? trim($item->type.($item->summary !== '' ? ' · '.$item->summary : '')) : 'Transaction';
            }
            if (! empty($row->return_id)) {
                $items = $itemList($returnItems->get($row->return_id));

                return 'Return '.($row->return_no ?? '').' on purchase '.($row->purchase_no ?? '-').($items ? ': '.$items : '');
            }
            if (! empty($row->purchase_id)) {
                $items = $itemList($purchaseItems->get($row->purchase_id));

                return 'Purchase '.($row->purchase_no ?? '').' received'.($items ? ': '.$items : '');
            }
            if (! empty($row->delivery_id)) {
                $delivery = $deliveries->get($row->stock_id);
                $parts = ['Delivery '.($delivery->delivery_no ?? $row->stock_no ?? '')];
                if ($delivery && $delivery->order_no) {
                    $parts[] = 'Order '.$delivery->order_no.($delivery->job_no ? ' ('.$delivery->job_no.')' : '');
                }
                $parts[] = $itemList($deliveryItems->get($row->stock_id));
                if ($delivery && (float) $delivery->freight_charges > 0) {
                    $parts[] = 'incl. freight '.number_format((float) $delivery->freight_charges, 2);
                }

                return implode(' · ', array_filter($parts));
            }

            return trim(strip_tags((string) ($row->description ?? ''))) ?: 'Transaction';
        });
    }
}
