<?php

namespace App\Repositories\Operator;

use DateTime;
use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements GlobalInterface {
    
    public function all(){
        $endDate = new DateTime(); // Today's date
        $startDate = (new DateTime())->modify('-7 days'); // Date 7 days ago
    
        // Format dates to match your database format
        $startDateFormatted = $startDate->format('Y-m-d');
        $endDateFormatted = $endDate->format('Y-m-d');
    
        // Fetch records for the last week
        $transactions = Transaction::join('salesman', 'salesman.salesman_id', '=', 'transactions.salesman_id')
            // ->join('orders', 'orders.order_id', '=', 'transactions.order_id')
            ->join('shops', 'shops.shop_id', '=', 'transactions.shop_id')
            // ->select('orders.order_no', 'salesman.name', 'shops.shop_no', 'transactions.credit', 'transactions.debit', 'salesman.salesman_id', 'transactions.transaction_date')
            ->select('transactions.*','salesman.name', 'shops.shop_no', 'salesman.salesman_id', 'shops.sname')
            ->whereBetween('transactions.transaction_date', [$startDateFormatted, $endDateFormatted])
            // ->orderBy('transactions.transaction_date', 'desc')
            ->orderBy('transactions.created_at', 'desc')
            ->get();

        // Calculate sums of debit and credit
        $tdebit = $transactions->sum('debit');
        $tcredit = $transactions->sum('credit');

        return [
            'transactions' => $transactions,
            'tdebit' => $tdebit,
            'tcredit' => $tcredit,
        ];
    }

    public function shopTransaction($id){
        $transactions = Transaction::leftJoin('salesman', 'salesman.salesman_id', '=', 'transactions.salesman_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'transactions.order_id')
            ->select('salesman.name', 'transactions.credit', 'transactions.debit', 'transactions.transaction_date', 'orders.order_no')
            ->orderBy('transactions.transaction_date', 'desc')
            ->where('transactions.shop_id', $id)
            ->get();

        $tdebit = $transactions->sum('debit');
        $tcredit = $transactions->sum('credit');

        return [
            'transactions' => $transactions,
            'tdebit' => $tdebit,
            'tcredit' => $tcredit,
        ];
    }

    public function market(){
        return Transaction::selectRaw('shop_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('shop_id')
            ->get()
            ->map(function ($transaction) {
                $total = $transaction->total_debit - $transaction->total_credit;
                return [
                    'shop_id' => $transaction->shop_id,
                    'total_debit' => $transaction->total_debit,
                    'total_credit' => $transaction->total_credit,
                    'total' => $total
                ];
            })
            ->keyBy('shop_id');
    }
    
    public function shopTransaction2($salesman, $shop){
        // $transactions = Transaction::join('salesman', 'salesman.salesman_id', '=', 'transactions.salesman_id')
        $transactions = Transaction::join('salesman', 'salesman.salesman_id', '=', 'transactions.salesman_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'transactions.order_id')
            ->select('salesman.name', 'transactions.credit', 'transactions.debit', 'transactions.transaction_date', 'orders.order_no')
            ->orderBy('transactions.transaction_date', 'desc')
            ->where('transactions.shop_id', $shop)
            ->where('salesman.salesman_id', $salesman)
            ->get();

        $tdebit = $transactions->sum('debit');
        $tcredit = $transactions->sum('credit');

        return [
            'transactions' => $transactions,
            'tdebit' => $tdebit,
            'tcredit' => $tcredit,
        ];
    }

    public function openingB(){
        return Transaction::where('salesman_id', 0)
            ->where('order_id', 0)
            ->get();
    }
        
    public function fDate($id){
        $transactions = Transaction::join('salesman', 'salesman.salesman_id', '=', 'transactions.salesman_id')
            // ->join('orders', 'orders.order_id', '=', 'transactions.order_id')
            ->join('shops', 'shops.shop_id', '=', 'transactions.shop_id')
            // ->select('orders.order_no', 'salesman.name', 'shops.shop_no', 'transactions.credit', 'transactions.debit', 'salesman.salesman_id', 'transactions.transaction_date')
            ->select('salesman.name', 'shops.shop_no', 'transactions.credit', 'transactions.debit', 'salesman.salesman_id', 'transactions.transaction_date')
            // ->where('transactions.credit', '!=', 0)
            ->where('transactions.transaction_date', '=', $id)
            ->orderBy('transactions.transaction_date', 'desc')
            ->get();
             // Calculate sums of debit and credit
        $tdebit = $transactions->sum('debit');
        $tcredit = $transactions->sum('credit');

        return [
            'transactions' => $transactions,
            'tdebit' => $tdebit,
            'tcredit' => $tcredit,
        ];
    }

    public function get($id){
        $data = DB::table('transactions')
            ->where('transactions.salesman_id', $id)
            ->get();
        return $data;
    }

    public function getDate($id, $id1, $id2){
        $data = DB::table('transactions')
            ->where('transactions.salesman_id', $id)
            ->whereBetween('transactions.transaction_date', [$id1, $id2])
            ->get();
        return $data;
    }

    public function cashIn(){
        $todayDate = Carbon::now()->toDateString();
        $transactions = Transaction::whereDate('transaction_date', $todayDate)
        ->select('credit')
        ->get();
        $tcredit = $transactions->sum('credit');
        return $tcredit;
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $transaction = Transaction::create($data);
        $transactionId = $transaction->transaction_id;
        return $transactionId;
    }

    public function update($id, array $data) {
        $transaction = Transaction::where('description', 'Opening Balance')
        ->where('shop_id', $id)
        ->firstOrFail();
        $transaction->update($data);
    }

    public function update2($id, array $data) {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($data);
    }

    public function delete($id){
        Transaction::destroy($id);
    }
    
    public function orderUpdate($id){
        Transaction::where('order_id', $id)->delete();
    }
}
