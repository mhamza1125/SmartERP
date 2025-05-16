<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository implements GlobalInterface
{
    public function all()
    {
        return Category::all();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Category::create($data);
    }

    public function update($id, array $data)
    {
        $update = Category::findOrFail($id);
        $update->update($data);
    }

    public function get($id)
    {
    }

    public function delete($id)
    {
    }
}
