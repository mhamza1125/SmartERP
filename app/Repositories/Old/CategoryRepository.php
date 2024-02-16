<?php

namespace App\Repositories\Operator;

use App\Models\Category;

class CategoryRepository implements GlobalInterface {
    
    public function all(){
        return Category::all();
    }

    public function get($id){
        return Category::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Category::create($data);
    }

    public function update($id, array $data) {
        $update = Category::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
