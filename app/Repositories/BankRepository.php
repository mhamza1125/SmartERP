<?php

namespace App\Repositories;

use App\Models\Bank;

class BankRepository implements GlobalInterface
{
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

    public function delete($id)
    {
    }
}
