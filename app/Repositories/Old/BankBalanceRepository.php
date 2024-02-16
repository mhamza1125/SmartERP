<?php

namespace App\Repositories\Operator;

use App\Models\BankBalance;

class BankBalanceRepository {
    
    public function all(){
        return BankBalance::join('banks', 'banks.bank_id', '=', 'bank_balances.bank_id')
        ->join('heads', 'heads.head_id', '=', 'banks.head_id')
        ->leftJoin('vendors', 'vendors.vendor_id', '=', 'banks.vendor_id')
        ->select('bank_balances.*', 'heads.name as hname', 'vendors.name as vname')
        ->orderBy('bank_balances.created_at', 'desc')
        ->get();
    }

    public function available(){
        return BankBalance::join('banks', 'banks.bank_id', '=', 'bank_balances.bank_id')
            ->join('heads', 'heads.head_id', '=', 'banks.head_id')
            ->where('banks.vendor_id', '0')
            ->selectRaw('bank_balances.bank_id, 
                SUM(bank_balances.debit) AS tdebit, 
                SUM(bank_balances.credit) AS tcredit, 
                heads.name as hname, 
                banks.account')
            ->groupBy('bank_balances.bank_id', 'heads.name', 'banks.account')
            ->get();
    }

    public function get($id){
        // return WeighBridge::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        BankBalance::create($data);
    }

    public function updatePay($id, array $data){
        $vendorPay = BankBalance::where('vp_id', $id);
        $vendorPay->update($data);
    }

    public function updateTransaction($id, array $data){
        $transaction = BankBalance::where('t_id', $id);
        $transaction->update($data);
    }
    
    public function updateExpense($id, array $data){
        $expense = BankBalance::where('e_id', $id);
        $expense->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
