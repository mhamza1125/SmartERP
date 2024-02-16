<?php

namespace App\Repositories\Operator;

use App\Models\Cheque;

class ChequeRepository implements GlobalInterface {
    
    public function all(){
        return Cheque::orderBy('created_at', 'desc')
        ->get();
    }

    public function get($id){
        return Cheque::where('cheque_id', $id)
        ->get();
    }

    public function fDate($id1, $id2){
        return Cheque::whereBetween('cheques.cheque_date', [$id1, $id2])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Cheque::create($data);
    }

    public function update($id, array $data) {
        // Null
        $update = Cheque::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){
        Cheque::destroy($id);
    }
}
