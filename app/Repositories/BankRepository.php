<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Facades\DB;
use App\Models\Bank;

class BankRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Bank::select('*', 'heads.name as hname', 'vendors.fname', 'customers.fname as cname', 'banks.address as baddress')
            ->join('heads', 'heads.head_id', '=', 'banks.head_id')
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'banks.banker_id')
                    ->whereIn('banks.bank_holder', ['vendor', 'contractor']);
                // ->where('banks.bank_holder', '=', 'vendor');
            })
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'banks.banker_id')
                    ->where('banks.bank_holder', '=', 'employee');
            })
            ->leftJoin('customers', function ($join) {
                $join->on('customers.customer_id', '=', 'banks.banker_id')
                    ->where('banks.bank_holder', '=', 'customer');
            })
            ->orderBy('bank_holder')->orderBy('banker_id')
            ->get();
    }

    public function get($id)
    {
        return Bank::join('heads', 'heads.head_id', '=', 'banks.head_id')
            ->select('*', 'heads.name as hname')
            ->where('banks.bank_id', $id)
            ->first();
    }

    /**
     * One of the company's own bank accounts with its bank name (from heads),
     * as an array for the proforma / commercial invoice prints. Null when not found.
     */
    public function printDetails($bankId): ?array
    {
        if (empty($bankId)) {
            return null;
        }

        $bank = Bank::leftJoin('heads', 'heads.head_id', '=', 'banks.head_id')
            ->where('banks.bank_id', $bankId)
            ->select(
                'banks.bank_id',
                'banks.account_title',
                'banks.account',
                'banks.iban',
                'banks.address',
                'banks.branch_code',
                'banks.swift_code',
                'heads.name as bank_name'
            )
            ->first();

        return $bank ? $bank->toArray() : null;
    }

    public function self()
    {
        return Bank::join('heads', 'heads.head_id', '=', 'banks.head_id')
            ->select('*', 'heads.name as hname')
            ->where('bank_holder', 'admin')
            ->get();
    }

    public function getBank($table, $tableId)
    {
        // Used by Transaction AjaxBank
        return Bank::select('*', 'heads.name as hname')
            ->join('heads', 'heads.head_id', 'banks.head_id')
            ->where('bank_holder', $table)
            ->where('banker_id', $tableId)->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Bank::create($data);

        return $store->bank_id;
    }

    public function update($id, array $data)
    {
        $update = Bank::findOrFail($id);
        $update->update($data);

        return $update->customer_id;
    }

    /**
     * A bank / cash account can be deleted only while no transaction or payroll uses it. The
     * company account's own opening balance entry is deleted with it.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $bank = Bank::findOrFail($id);
        $impact = DeletionImpact::for('Bank account '.trim($bank->account_title.' '.$bank->account));
        $isOpening = fn ($q) => $q->where('transaction_to', 'admin')->where('transaction_type', 'openingBalance');

        $impact->blockIf(
            DB::table('transactions')
                ->where(fn ($q) => $q->where('bank_id', $id)->orWhere('payee_bank_id', $id))
                ->whereNot(fn ($q) => $isOpening($q)->where('bank_id', $id))
                ->count(),
            'transaction'
        );
        $impact->blockIf($this->refCount('payrolls', 'bank_id', $id), 'monthly payroll', null, $this->refExamples('payrolls', 'bank_id', $id, 'month'));

        $impact->cascadeIf($this->refCount('transactions', 'bank_id', $id, $isOpening), 'opening balance entry', 'opening balance entries');

        return $impact;
    }

    public function delete($id)
    {
        DB::table('transactions')->where('bank_id', $id)->where('transaction_to', 'admin')->where('transaction_type', 'openingBalance')->delete();

        return Bank::findOrFail($id)->delete();
    }
}
