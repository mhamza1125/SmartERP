<?php

namespace App\Repositories;

use App\Models\Salary;

class SalaryRepository implements GlobalInterface
{
    public function all()
    {
        return Salary::all();
    }

    public function get($id)
    {
        return Salary::findOrFail($id);
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Salary::create($data);
    }

    public function update($id, array $data)
    {
        $check = Salary::where('employee_id', $id)
            ->where('amount', $data['amount'])
            ->where('status', '1')->first();
        if (! $check) {
            $update = Salary::where('employee_id', $id)->first();
            if ($update) {
                $update->update(['status' => '0']);
            }
            $data['created_by'] = auth()->id();
            $store = Salary::create($data);
        }
    }

    public function delete($id)
    {
    }
}
