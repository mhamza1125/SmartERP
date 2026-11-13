<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Repositories\ImageRepository;
use App\Models\Machine;

class MachineRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Machine::join('heads', 'heads.head_id', '=', 'machines.machine_type_id')
            ->leftJoin('employees', 'employees.employee_id', '=', 'machines.employee_id')
            ->select('*', 'employees.name', 'heads.name as hname')
            ->orderBy('machines.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        return Machine::where('machines.machine_id', $id)
            ->join('heads', 'heads.head_id', '=', 'machines.machine_type_id')
            ->leftJoin('employees', 'employees.employee_id', '=', 'machines.employee_id')
            ->select('machines.*', 'employees.name as employee_name', 'heads.name as machine_type')
            ->first();
    }

    public function refNo()
    {
        $lastMachine = Machine::all()->sortByDesc(function ($machine) {
            return intval(substr($machine->machine_no, 1));
        })->first();

        $lastNumber = $lastMachine ? intval(substr($lastMachine->machine_no, 1)) : 0;

        return 'M'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Machine::create($data);
    }

    public function update($id, array $data)
    {
        $update = Machine::findOrFail($id);
        $update->update($data);
    }

    /**
     * A machine can be deleted only while no material issuance was made for it.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $machine = Machine::findOrFail($id);
        $impact = DeletionImpact::for('Machine '.$machine->machine_no);

        $impact->blockIf($this->refCount('stocks', 'machine_id', $id), 'machine material issuance', null, $this->refExamples('stocks', 'machine_id', $id, 'stock_no'));
        $impact->cascadeIf(app(ImageRepository::class)->countFor('machines', $id), 'image');

        return $impact;
    }

    public function delete($id)
    {
        app(ImageRepository::class)->deleteFor('machines', $id);

        return Machine::findOrFail($id)->delete();
    }
}
