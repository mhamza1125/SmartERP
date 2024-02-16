<?php

namespace App\Repositories\Operator;

use Carbon\Carbon;
use App\Models\Expense;

class ExpenseRepository implements GlobalInterface {
    
    public function all(){
        return Expense::join('heads', 'heads.head_id', '=', 'expenses.head_id')
            ->join('head_types', 'head_types.ht_id', '=', 'heads.ht_id')
            ->select('expenses.*', 'heads.name', 'head_types.name as htname')
            ->orderBy('expenses.created_at', 'desc')
            ->get();
    }

    public function fDate($id1, $id2) {
        return Expense::join('heads', 'heads.head_id', '=', 'expenses.head_id')
            ->join('head_types', 'head_types.ht_id', '=', 'heads.ht_id')
            ->select('expenses.*', 'heads.name', 'head_types.name as htname')
            ->orderBy('expenses.expense_date', 'desc')
            ->whereBetween('expenses.expense_date', [$id1, $id2])
            ->get();
    }

    public function get($id){
        // return Expense::find($id);
    }

    public function cashOut(){
        $todayDate = Carbon::now()->toDateString();
        $transactions = Expense::whereDate('expense_date', $todayDate)
        ->select('amount')
        ->get();
        $tdebit = $transactions->sum('amount');
        return $tdebit;
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $expense = Expense::create($data);
        return $expense->expense_id;
    }

    public function update($id, array $data) {
        $expense = Expense::findOrFail($id);
        $expense->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
