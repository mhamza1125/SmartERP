<?php

namespace App\Repositories\Operator;

use App\Models\BankBalance;
use Illuminate\Support\Facades\DB;

class PettyCashRepository implements GlobalInterface {
    
    public function all(){
        $transaction = \DB::table('transactions')
            ->leftJoin('salesman', 'salesman.salesman_id', '=', 'transactions.salesman_id')
            ->leftJoin('shops', 'shops.shop_id', '=', 'transactions.shop_id')
            ->select('transactions.*', 'transactions.created_at as combined_created_at', 'name', 'fname', 'shops.sname', 'shop_no')
            ->where('transactions.transaction_type', '>', 0) //Get 1 and 2
            ->get();
            
        $salaryPay = \DB::table('salary_pay')
            ->join('salesman', 'salesman.salesman_id', '=', 'salary_pay.salesman_id')
            ->select('salary_pay.*', 'salary_pay.created_at as combined_created_at', 'salary_pay.amount as debit', 'name', 'fname')
            ->get();
        
        $vendorPay = \DB::table('vendor_payments')
            ->join('vendors', 'vendors.vendor_id', '=', 'vendor_payments.vendor_id')
            ->select('vendor_payments.*', 'vendor_payments.created_at as combined_created_at', 'debit', 'name')
            ->where('vendor_payments.transaction_type', '=', 1)
            ->whereNull('vendor_payments.credit')
            ->get();
            
        $expense = \DB::table('expenses')
            ->join('heads', 'heads.head_id', '=', 'expenses.head_id')
            ->select('expenses.*', 'expenses.created_at as combined_created_at', 'expenses.amount as debit', 'name')
            ->get();

        // $pettyCash = $transaction->concat($salaryPay);
        $pettyCash = $transaction->concat($salaryPay)->concat($vendorPay)->concat($expense);
        $totalDebit = $pettyCash->sum('debit');
        $totalCredit = $pettyCash->sum('credit');
        $balance = $totalCredit - $totalDebit;
        $sorted = $pettyCash->sortByDesc('combined_created_at');
        return [
            'balance' => $balance,
            'pettyCash' => $sorted,
        ];
    }

    public function available(){
        $transaction = \DB::table('transactions')
            ->select('transactions.*', 'transactions.created_at as combined_created_at')
            ->where('transactions.transaction_type', '=', 1)
            ->get();

        $salary_pay = \DB::table('salary_pay')
            ->select('salary_pay.*', 'salary_pay.created_at as combined_created_at', 'salary_pay.amount as debit')
            ->get();

        $pettyCash = $transaction->concat($salary_pay);
        $totalDebit = $pettyCash->sum('debit');
        $totalCredit = $pettyCash->sum('credit');
        $balance = $totalCredit - $totalDebit;
        return $balance;
    }

    public function get($id){
        // return WeighBridge::find($id);
    }

    public function store(array $data){
        // $data['created_by'] = auth()->id();
        // BankBalance::create($data);
    }

    public function update($id, array $data){
        // Logic to update an existing WeighBridge entry with ID $id using the $data array
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
