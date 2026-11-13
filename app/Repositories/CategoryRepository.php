<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Models\Category;

class CategoryRepository implements GlobalInterface
{
    use ChecksReferences;

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

    /**
     * A category can be deleted only while no product is in it.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $category = Category::findOrFail($id);

        return DeletionImpact::for('Category '.$category->name)
            ->blockIf($this->refCount('products', 'category_id', $id), 'product', null, $this->refExamples('products', 'category_id', $id, 'article_no'));
    }

    public function delete($id)
    {
        return Category::findOrFail($id)->delete();
    }
}
