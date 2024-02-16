<?php

namespace App\Repositories\Operator;

use App\Models\User;

class UserRepository implements GlobalInterface {
    
    public function all(){
    }

    public function get($id){
        return User::where('pass', 'operator')
        ->get();
    }

    public function store(array $data){
    }

    public function update($id, array $data) {
        $update = User::where('pass', 'operator')
            ->firstOrFail();
        $update->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
