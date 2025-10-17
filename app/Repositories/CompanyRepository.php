<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository implements GlobalInterface
{
    public function all()
    {
        return Company::all();
    }

    public function get($id)
    {
        return Company::findOrFail($id);
    }

    public function first()
    {
        return Company::first();
    }

    public function store(array $data)
    {
        // $data['created_by'] = auth()->id();
        $store = Company::create($data);

        return $store->id;
    }

    public function update($id, array $data)
    {
        $company = Company::findOrFail($id);
        $company->update($data);

        return $company->id;
    }

    public function delete($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return true;
    }
}
