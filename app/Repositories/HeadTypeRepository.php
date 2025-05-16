<?php

namespace App\Repositories;

use App\Models\HeadType;

class HeadTypeRepository implements GlobalInterface
{
    public function all()
    {
        return HeadType::all();
    }

    public function get($id)
    {
    }

    public function store(array $data)
    {
    }

    public function update($id, array $data)
    {
    }

    public function delete($id)
    {
    }
}
