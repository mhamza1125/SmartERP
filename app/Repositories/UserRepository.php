<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements GlobalInterface {
    
    public function all(){
        return User::all();
    }

    public function get($id){
        return User::findOrfail($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = User::create($data);
    }

    public function update($id, array $data) {
        $update = User::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){}
}