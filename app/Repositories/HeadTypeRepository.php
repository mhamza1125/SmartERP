<?php

namespace App\Repositories;

use App\Models\HeadType;

class HeadTypeRepository implements GlobalInterface
{
    public function all()
    {
        // Exclude Product Costing
        return HeadType::where('head_type_id', '!=', '14')->get();
    }

    public function get($id) {}

    public function store(array $data) {}

    public function update($id, array $data) {}

    public function delete($id) {}
}
