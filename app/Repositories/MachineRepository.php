<?php

namespace App\Repositories;

use App\Models\Machine;

class MachineRepository implements GlobalInterface {
    
    public function all(){
        return Machine::join('heads', 'heads.head_id', '=', 'machines.machine_type_id')
        ->leftJoin('employees', 'employees.employee_id', '=', 'machines.employee_id')
        ->select('*', 'employees.name', 'heads.name as hname')
        ->orderBy('machines.created_at', 'desc')
        ->get();
    }

    public function get($id){
        return Machine::where('machines.machine_id', $id)
        ->join('heads', 'heads.head_id', '=', 'machines.machine_type_id')
        ->leftJoin('employees', 'employees.employee_id', '=', 'machines.employee_id')
        ->select('*', 'employees.name', 'heads.name as hname')
        ->first();
    }

    public function refNo() {
        $lastMachine = Machine::all()->sortByDesc(function($machine) {
            return intval(substr($machine->machine_no, 1));
        })->first();
    
        $lastNumber = $lastMachine ? intval(substr($lastMachine->machine_no, 1)) : 0;
        return 'M' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Machine::create($data);
    }

    public function update($id, array $data) {
        $update = Machine::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){}
}