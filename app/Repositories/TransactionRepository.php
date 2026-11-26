<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements GlobalInterface
{
    use ChecksReferences;

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
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
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
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
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
        return $this->expenseRows()
            ->select('transactions.*', 'heads.name')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    /** Same rows as expense() (bank charges included), narrowed by date and / or expense head. */
    public function expenseFilter($dfrom, $dto, $head_id)
    {
        $query = $this->expenseRows();

        if (! empty($dfrom) && ! empty($dto)) {
            $query->whereBetween('transactions.transaction_date', [$dfrom, $dto]);
        }

        if (! empty($head_id)) {
            $query->where('heads.head_id', $head_id);
        }

        return $query->select('transactions.*', 'heads.name')->orderBy('transactions.created_at', 'desc')->get();
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
            // Optional Expense Head (a sub-select so no joined column is overwritten)
            ->select('*')
            ->selectSub(DB::table('heads')->select('name')->whereColumn('heads.head_id', 'transactions.expense_head_id')->limit(1), 'expense_head_name')
            ->orderBy('transactions.created_at', 'desc')
            ->get();
    }

    /**
     * Journal vouchers that put an amount on a vendor's / contractor's payable balance (credit
     * vouchers, amount in debit) with what has been paid against them (payments with voucher_id),
     * for Pay Vendor > Payment For. $excludePaymentId leaves out the payment being edited.
     */
    public function vendorVouchers($vendorId, $excludePaymentId = null): Collection
    {
        return Transaction::where('transactions.transaction_type', 'generalVoucher')
            ->whereIn('transactions.transaction_to', ['vendor', 'contractor'])
            ->where('transactions.payee_id', $vendorId)
            ->where('transactions.debit', '>', 0)
            ->leftJoin('heads as ehead', 'ehead.head_id', '=', 'transactions.expense_head_id')
            ->select('transactions.transaction_id', 'transactions.transaction_date', 'transactions.debit as amount',
                'transactions.description', 'transactions.expense_head_id', 'ehead.name as expense_head_name')
            ->selectSub(fn ($q) => $q->from('transactions as pay')
                ->whereColumn('pay.voucher_id', 'transactions.transaction_id')
                ->where('pay.transaction_type', 'payment')
                ->when($excludePaymentId, fn ($q) => $q->where('pay.transaction_id', '!=', $excludePaymentId))
                ->selectRaw('COALESCE(SUM(pay.credit), 0)'), 'paid')
            ->orderBy('transactions.transaction_date')
            ->get()
            ->map(function ($voucher) {
                $voucher->outstanding = round((float) $voucher->amount - (float) $voucher->paid, 2);

                return $voucher;
            });
    }

    /**
     * A vendor payment made against a journal voucher: "Payment For" posts "jv:{id}". Sets
     * voucher_id, order_id 0 (no purchase) and the voucher's expense head, so the payment is
     * reported under that head (Expense page) when the money is actually paid.
     * Any other payment is left unlinked (voucher_id NULL).
     *
     * @param  array  $data  the payment as it will be saved (amount already in credit)
     * @return array|string the data to save, or why the payment cannot be linked
     */
    public function linkVoucherPayment(array $data, $paymentId = null): array|string
    {
        $data['voucher_id'] = null;
        $orderId = (string) ($data['order_id'] ?? '');
        if (! str_starts_with($orderId, 'jv:')) {
            // A payment no longer made against a voucher is not reported under its expense head
            if (($data['transaction_type'] ?? '') === 'payment' && in_array($data['transaction_to'] ?? '', ['vendor', 'contractor'], true)) {
                $data['expense_head_id'] = null;
            }

            return $data;
        }
        if (($data['transaction_type'] ?? '') !== 'payment' || ! in_array($data['transaction_to'] ?? '', ['vendor', 'contractor'], true)) {
            return 'Only a vendor payment can be made against a journal voucher.';
        }

        $voucherId = (int) substr($orderId, 3);
        $voucher = $this->vendorVouchers($data['payee_id'] ?? 0, $paymentId)->firstWhere('transaction_id', $voucherId);
        if (! $voucher) {
            return 'The selected journal voucher does not belong to this vendor.';
        }
        $amount = (float) ($data['credit'] ?? 0);
        if ($amount - $voucher->outstanding > 0.005) {
            return 'The payment ('.number_format($amount, 2).') is more than what is still unpaid on journal voucher #'
                .$voucherId.' ('.number_format(max(0, $voucher->outstanding), 2).').';
        }

        $data['voucher_id'] = $voucherId;
        $data['order_id'] = 0;
        $data['expense_head_id'] = $voucher->expense_head_id;

        return $data;
    }

    /**
     * Expense page rows: expenses and bank charges, plus vendor / contractor payments made against
     * a journal voucher that has an expense head (reported under that head when paid).
     * The head joined is the expense's own head (payee_id) or the payment's expense_head_id.
     */
    private function expenseRows()
    {
        return Transaction::where(function ($q) {
            $q->whereIn('transactions.transaction_to', ['expense', 'bankCharges'])
                ->orWhere(fn ($q) => $q->whereIn('transactions.transaction_to', ['vendor', 'contractor'])
                    ->where('transactions.transaction_type', 'payment')
                    ->whereNotNull('transactions.voucher_id')
                    ->whereNotNull('transactions.expense_head_id'));
        })
            ->join('heads', 'heads.head_id', '=', DB::raw("CASE WHEN transactions.transaction_to IN ('expense', 'bankCharges') THEN transactions.payee_id ELSE transactions.expense_head_id END"));
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
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
            ->orderBy('transaction_date')->orderBy('transaction_id')
            ->get();
    }

    /**
     * Cash book for a date range: opening = cash (debit - credit) before $dfrom, the range's
     * entries in date order, closing = opening + the range's movements (the cash held at $dto).
     */
    public function cashTransactionFilter($dfrom, $dto)
    {
        $cash = fn () => DB::table('transactions')
            ->where('transactions.bank_id', '0')
            ->where('transaction_type', '!=', 'openingBalance')
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher');  // Journal vouchers move no cash / bank money

        return $this->bookForRange($cash, $dfrom, $dto);
    }

    public function bankTransaction($id)
    {
        return Transaction::where('transactions.bank_id', $id)
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
            ->orderBy('transaction_date')->orderBy('transaction_id')
            ->get();
    }

    /** Bank ledger for a date range: same rules as cashTransactionFilter(). */
    public function bankTransactionFilter($id, $dfrom, $dto)
    {
        $bank = fn () => DB::table('transactions')
            ->where('transactions.bank_id', $id)
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher');  // Journal vouchers move no cash / bank money

        return $this->bookForRange($bank, $dfrom, $dto);
    }

    /**
     * Opening balance (debit - credit before $dfrom), the range's rows in date order and the
     * closing balance (opening + the range's debit - credit), for a cash or bank book query.
     */
    private function bookForRange(callable $book, $dfrom, $dto): array
    {
        $net = fn ($query) => (float) $query->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as net')->value('net');

        $openingBalance = $net($book()->where('transaction_date', '<', $dfrom));
        $closingBalance = $openingBalance + $net($book()->whereBetween('transaction_date', [$dfrom, $dto]));

        return [
            'transactions' => $book()->whereBetween('transaction_date', [$dfrom, $dto])
                ->orderBy('transaction_date')->orderBy('transaction_id')->get(),
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    public function cashBalance()
    {
        $transaction = Transaction::where('transactions.bank_id', '0')
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
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
            ->where('transactions.ledger_flag', '!=', 0)->where('transactions.transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
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
            ->where('ledger_flag', '!=', 0)->where('transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
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
            ->where('transactions.ledger_flag', '!=', 0)->where('transactions.transaction_type', '!=', 'generalVoucher')  // Journal vouchers move no cash / bank money
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

    public function getGeneralVoucher($id) // Journal Voucher (stored as generalVoucher)
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
            ->leftJoin('heads as ehead', 'ehead.head_id', '=', 'transactions.expense_head_id')
            ->select('transactions.*', 'vendors.vendor_no', 'vendors.fname as vendor_name', 'employees.employee_no', 'employees.name as employee_name', 'customers.customer_no', 'customers.fname as customer_name', 'bhead.name as bname', 'banks.account', 'banks.account_title', 'ehead.name as expense_head_name')
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
        // Vendor Ledger - Amount of Received Purchase Items
        // Purchases increase liability, so store as debit (displays in Credit column after reversal)
        $purchases = \DB::table('purchases')
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.approved_qty * purchase_items.price) as debit'))
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
            ->select(\DB::raw('SUM(receive_materials.approved_qty * purchase_items.price) as debit'))
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
            ->select('purchases.*', 'purchases.created_at as timestamp', \DB::raw('SUM(receive_materials.approved_qty * purchase_items.price) as debit'))
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
                $join->on('order_items.order_id', '=', \DB::raw('COALESCE(stock_items.order_id, stocks.order_id)'))
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
        // Customer ledger for a date range. Opening / closing are the running balance of the
        // customer ledger view (customerDetail) before $dfrom / at $dto: the same rows and the
        // same row rule (customerLedgerAmount), so filtered and unfiltered balances agree.
        $deliveries = fn (callable $dates) => $dates(\DB::table('stocks')
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
                $join->on('order_items.order_id', '=', \DB::raw('COALESCE(stock_items.order_id, stocks.order_id)'))
                    ->on('order_items.product_type_id', '=', 'stock_items.product_type_id');
            })
            ->where('orders.customer_id', $id)
            ->where('stocks.stock_status', '=', 3) // Delivery status
            ->groupBy('stocks.stock_id'))
            ->get();

        $transactions = fn (callable $dates) => $dates(\DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as timestamp')
            ->where('transactions.payee_id', $id)
            ->where('transactions.transaction_to', 'customer'))
            ->get();

        $before = $deliveries(fn ($q) => $q->where('stocks.stock_date', '<', $dfrom))
            ->concat($transactions(fn ($q) => $q->where('transactions.transaction_date', '<', $dfrom)));
        $transactionsBetween = $deliveries(fn ($q) => $q->whereBetween('stocks.stock_date', [$dfrom, $dto]))
            ->concat($transactions(fn ($q) => $q->whereBetween('transactions.transaction_date', [$dfrom, $dto])))
            ->sortBy('timestamp');

        $openingBalance = $before->sum(fn ($row) => $this->customerLedgerAmount($row));
        $closingBalance = $openingBalance + $transactionsBetween->sum(fn ($row) => $this->customerLedgerAmount($row));

        return [
            'transactions' => $transactionsBetween,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
        ];
    }

    /**
     * What one customer ledger row adds to the running balance (customerDetail.blade.php rule):
     * a journal voucher +/- its cc_amount (credit voucher +, charge -), a payment + its customer
     * currency amount (cc_amount, else debit), anything else (delivery, opening) - debit + credit.
     */
    public function customerLedgerAmount($row): float
    {
        $type = $row->transaction_type ?? null;
        if ($type === 'generalVoucher' && ! empty($row->cc_amount)) {
            if (is_null($row->credit)) {
                return (float) $row->cc_amount;
            }

            return is_null($row->debit) ? -(float) $row->cc_amount : 0.0;
        }
        if ($type === 'orderPayment') {
            return (float) (! empty($row->cc_amount) ? $row->cc_amount : ($row->debit ?? 0));
        }

        return (float) ($row->credit ?? 0) - (float) ($row->debit ?? 0);
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

    /**
     * Row-locks the money accounts until the transaction ends, so balance checks and the entries
     * that follow are done one save at a time per account. A bank account locks its `banks` row;
     * cash (bank_id 0) has no row of its own and locks the single `company` row instead.
     * Order: cash first, then banks by id (no deadlocks). Call it as the FIRST read of the
     * transaction: the REPEATABLE READ snapshot of the balance query is taken after the lock.
     */
    public function lockMoneyAccounts(array $bankIds): void
    {
        $ids = collect($bankIds)->map(fn ($id) => (int) $id)->unique()->sort()->values();
        if ($ids->contains(0)) {
            DB::table('company')->orderBy('id')->limit(1)->lockForUpdate()->pluck('id');
        }
        $banks = $ids->filter(fn ($id) => $id > 0)->all();
        if ($banks !== []) {
            DB::table('banks')->whereIn('bank_id', $banks)->orderBy('bank_id')->lockForUpdate()->pluck('bank_id');
        }
    }

    /** Cash (bank_id 0) or bank account balance, as the cash book / bank ledger show it (debit - credit). */
    public function accountBalance(int $bankId): float
    {
        if ($bankId === 0) {
            return (float) $this->cashBalance();
        }
        $row = $this->bankBalance2($bankId);

        return (float) ($row->tdebit ?? 0) - (float) ($row->tcredit ?? 0);
    }

    /**
     * What an existing entry adds to its account's balance today (0 when it is not counted in
     * the cash book / bank ledger: ledger_flag 0 or a journal voucher), so an edit can treat
     * its own old amount as available again.
     */
    public function balanceEffect(Transaction $transaction, int $bankId): float
    {
        if ((int) $transaction->bank_id !== $bankId || (int) $transaction->ledger_flag === 0 || $transaction->transaction_type === 'generalVoucher') {
            return 0.0;
        }

        return (float) $transaction->debit - (float) $transaction->credit;
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

    /**
     * Transactions that are part of another record are refused (payroll postings, automatic bank
     * charges, delivery expenses): they are removed with that record. A transfer is deleted with
     * its paired entry, an order payment with its bank charges, and a loan only while the loan
     * repaid so far stays covered.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $transaction = Transaction::findOrFail($id);
        $type = $transaction->transaction_type;
        $impact = DeletionImpact::for(transactionLabel($type).' #'.$id);

        $payroll = DB::table('payroll_items')->join('payrolls', 'payrolls.payroll_id', '=', 'payroll_items.payroll_id')
            ->where(fn ($q) => $q->where('salary_transaction_id', $id)->orWhere('temp_deduction_transaction_id', $id)->orWhere('fixed_deduction_transaction_id', $id))
            ->value('payrolls.month');
        if ($payroll) {
            return $impact->block('it was posted by the monthly payroll of '.$payroll.' (edit or delete that payroll instead)');
        }
        if ($type === 'bankCharges' && $transaction->order_id) {
            return $impact->block('it was created with order payment #'.$transaction->order_id.' (edit or delete that payment instead)');
        }
        if ($type === 'deliveryExpense') {
            return $impact->block('it is an expense of a delivery (edit or delete that delivery instead)');
        }
        if (in_array($transaction->transaction_to, ['asset', 'liability'], true)) {
            $register = $transaction->transaction_to === 'asset' ? ['Asset', 'Assets'] : ['Liability', 'Liabilities'];
            $name = DB::table('assets')->where('transaction_id', $id)->value('asset_name');

            return $impact->block('it was posted by '.$register[0].' "'.$name.'" (edit or delete that entry under Finance > '.$register[1].' instead)');
        }
        if ($type === 'generalVoucher') {
            $payments = Transaction::where('voucher_id', $id)->where('transaction_type', 'payment')->pluck('transaction_id');
            if ($payments->isNotEmpty()) {
                return $impact->block($payments->count().' vendor payment(s) were made against this journal voucher (#'
                    .$payments->implode(', #').'); delete those payments first');
            }
        }

        if ($type === 'advance' && in_array($transaction->transaction_to, ['employee', 'vendor', 'contractor'], true)) {
            $kind = $transaction->transaction_to === 'employee' ? ($transaction->advance_type ?: Transaction::ADVANCE_FIXED) : 'loan';
            $left = $this->loanSummary($transaction->transaction_to, (int) $transaction->payee_id, (int) $id)[$kind] ?? null;
            if ($left && $left['repaid'] - $left['given'] > 0.005) {
                $impact->block('loan repayments of '.number_format($left['repaid'], 2).' would exceed the loans given ('.number_format($left['given'], 2).'); delete the repayments first');
            }
        }

        $impact->cascadeIf(count($this->linkedIds($transaction)), 'linked entry (other side of the transfer / bank charges)', 'linked entries (other side of the transfer / bank charges)');
        $impact->cascadeIf(app(ImageRepository::class)->countFor('transactions', $id), 'attachment');
        if ($type === 'openingBalance') {
            $impact->note('This changes the opening balance of the account / party ledger.');
        } else {
            $impact->note('Cash / bank balances and ledgers are recalculated without this entry.');
        }

        return $impact;
    }

    /**
     * Entries that only exist together with this one: the other half of a transfer and the bank
     * charges booked with an order payment.
     */
    private function linkedIds(Transaction $transaction): array
    {
        $ids = [];
        if ($transaction->transfer_pair_id) {
            $ids[] = (int) $transaction->transfer_pair_id;
        }
        $ids = array_merge($ids, Transaction::where('transfer_pair_id', $transaction->transaction_id)->pluck('transaction_id')->all());
        if ($transaction->transaction_type === 'orderPayment') {
            $ids = array_merge($ids, Transaction::where('transaction_type', 'bankCharges')->where('order_id', $transaction->transaction_id)->pluck('transaction_id')->all());
        }

        return array_values(array_diff(array_unique(array_map('intval', $ids)), [(int) $transaction->transaction_id]));
    }

    public function delete($id)
    {
        $transaction = Transaction::findOrFail($id);
        $ids = array_merge([(int) $id], $this->linkedIds($transaction));
        foreach ($ids as $transactionId) {
            app(ImageRepository::class)->deleteFor('transactions', $transactionId);
        }

        return Transaction::whereIn('transaction_id', $ids)->delete();
    }

    /**
     * Loans of one party (employee / vendor / contractor), from the Give Loan (advance, amount
     * in credit) and Receive Loan (receiveAdvance, amount in debit) entries - payroll loan
     * deductions are Receive Loan entries too, so they count as repaid.
     *
     * Employees have two kinds: "fixed" (instalment loan; older entries without a type count as
     * fixed, as in payroll) and "temporary" (advance against salary). Vendors and contractors
     * have one kind, "loan".
     *
     * @return array<string, array{given: float, repaid: float, outstanding: float}>
     */
    public function loanSummary(string $party, int $partyId, ?int $excludeTransactionId = null): array
    {
        $kind = $party === 'employee'
            ? "COALESCE(advance_type, '".Transaction::ADVANCE_FIXED."')"
            : "'loan'";

        $rows = Transaction::where('transaction_to', $party)
            ->where('payee_id', $partyId)
            ->whereIn('transaction_type', ['advance', 'receiveAdvance'])
            ->when($excludeTransactionId, fn ($query) => $query->where('transaction_id', '!=', $excludeTransactionId))
            ->selectRaw("{$kind} as kind")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'advance' THEN credit ELSE 0 END), 0) as given")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'receiveAdvance' THEN debit ELSE 0 END), 0) as repaid")
            ->groupByRaw($kind)
            ->get();

        $kinds = $party === 'employee' ? [Transaction::ADVANCE_FIXED, Transaction::ADVANCE_TEMPORARY] : ['loan'];
        $summary = [];
        foreach ($kinds as $name) {
            $row = $rows->firstWhere('kind', $name);
            $given = round((float) ($row->given ?? 0), 2);
            $repaid = round((float) ($row->repaid ?? 0), 2);
            $summary[$name] = ['given' => $given, 'repaid' => $repaid, 'outstanding' => max(0, round($given - $repaid, 2))];
        }

        return $summary;
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
        'generalVoucher' => 'Journal Voucher',
        'asset' => 'Asset',
        'liability' => 'Liability',
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
        $expenseHeads = DB::table('heads')->whereIn('head_id', $rows->pluck('expense_head_id')->filter())->pluck('name', 'head_id');
        $registerNames = DB::table('assets')->whereIn('asset_id', $of(['asset', 'liability'])->pluck('payee_id'))->pluck('asset_name', 'asset_id');

        // "Cash" for bank_id 0, otherwise "Bank name - Account title - A/C number"
        $account = function ($bankId) use ($banks) {
            if (! $bankId) {
                return 'Cash';
            }
            $bank = $banks->get($bankId);

            return $bank ? collect([$bank->bank_name, $bank->account_title, $bank->account ? 'A/C '.$bank->account : null])->filter()->implode(' - ') : null;
        };
        $amount = fn ($value) => $value !== null && (float) $value != 0 ? number_format((float) $value, 2) : null;

        return $rows->map(function ($row) use ($all, $account, $amount, $vendors, $employees, $customers, $heads, $purchases, $orders, $deliveries, $expenseHeads, $registerNames) {
            [$note, $cheque] = $this->splitCheque($row->description);
            $type = self::TYPE_LABELS[$row->transaction_type] ?? ucfirst((string) $row->transaction_type);
            // Employee loans carry their kind: a temporary advance against salary or a fixed (instalment) loan
            if (($row->advance_type ?? null) === Transaction::ADVANCE_TEMPORARY) {
                $type = $row->transaction_type === 'advance' ? 'Temporary Advance' : 'Temporary Advance Deduction';
            } elseif (($row->advance_type ?? null) === Transaction::ADVANCE_FIXED) {
                $type = $row->transaction_type === 'advance' ? 'Fixed Loan Given' : 'Fixed Loan Repayment';
            }

            $party = null;
            if (in_array($row->transaction_to, ['vendor', 'contractor'], true) && ($vendor = $vendors->get($row->payee_id))) {
                $party = trim($vendor->vendor_no.' - '.$vendor->fname);
            } elseif ($row->transaction_to === 'employee' && ($employee = $employees->get($row->payee_id))) {
                $party = trim($employee->employee_no.' - '.$employee->name);
            } elseif ($row->transaction_to === 'customer' && ($customer = $customers->get($row->payee_id))) {
                $party = trim($customer->customer_no.' - '.$customer->fname.' '.$customer->lname);
            } elseif ($row->transaction_to === 'expense') {
                $party = $heads->get($row->payee_id);
            } elseif (in_array($row->transaction_to, ['asset', 'liability'], true)) {
                $party = $registerNames->get($row->payee_id);
            }
            $partyLabel = [
                'vendor' => 'Vendor', 'contractor' => 'Contractor', 'employee' => 'Employee',
                'customer' => 'Customer', 'expense' => 'Expense Head', 'asset' => 'Asset', 'liability' => 'Liability',
            ][$row->transaction_to] ?? 'Party';
            $incoming = in_array($row->transaction_type, ['receiveAdvance', 'orderPayment'], true)
                || (in_array($row->transaction_to, ['asset', 'liability'], true) && (float) $row->debit > 0);
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
                $named = $noAccount || in_array($row->transaction_to, ['asset', 'liability'], true); // "Asset - Delivery Van"
                $parts[] = $type.($party ? ($named ? ' - ' : ($incoming ? ' from ' : ' to ')).$party : '');
                if (in_array($row->transaction_to, ['vendor', 'contractor'], true) && $purchases->get($row->order_id)) {
                    $details['Purchase No'] = $purchases->get($row->order_id);
                    $parts[] = 'Purchase '.$purchases->get($row->order_id);
                } elseif (in_array($row->transaction_to, ['vendor', 'contractor'], true) && $row->transaction_type === 'payment' && ! empty($row->voucher_id)) {
                    // Payment of a journal voucher (the voucher put the amount on the payable balance)
                    $details['Payment For'] = 'Journal Voucher #'.$row->voucher_id;
                    $parts[] = 'Against Journal Voucher #'.$row->voucher_id;
                } elseif ($row->transaction_to === 'vendor' && $row->transaction_type === 'payment') {
                    // Vendor payment not linked to a purchase
                    $details['Payment For'] = 'General Payment';
                    $parts[] = 'General Payment';
                }
                // Expense head: a journal voucher's own, or the one a voucher payment is reported under
                if (in_array($row->transaction_type, ['generalVoucher', 'payment'], true) && ! empty($row->expense_head_id) && $expenseHeads->get($row->expense_head_id)) {
                    $details['Expense Head'] = $expenseHeads->get($row->expense_head_id);
                    $parts[] = 'Expense head: '.$expenseHeads->get($row->expense_head_id);
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
    public function splitCheque(?string $description): array
    {
        $text = trim(strip_tags((string) $description));
        // Written by the transfer form at the end: "note - Cheque No: <cheque>". The cheque number is
        // everything after the label (it may contain spaces), so it is never split into the note.
        if (preg_match('/^(.*?)\s*-?\s*\b(?:check|cheque)\s+no\s*:\s*(.+)$/is', $text, $match)) {
            $note = trim($match[1], " -\t\n");

            return [$note !== '' ? $note : null, trim($match[2])];
        }
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
            ->selectRaw("purchase_items.purchase_id as doc_id, {$itemName} as item, SUM(receive_materials.approved_qty) as qty")
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
