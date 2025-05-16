<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;

class AdminRepository implements GlobalInterface
{
    public function role(){
        return Role::all();
    }

    public function permission(){
        return Permission::all();
    }

    public function all(){}

    public function get($id){}

    public function store(array $data)
    {
        $store = Role::create($data);
    
        foreach ($data['permission_id'] as $item) {
            PermissionRole::create([
                'role_id' => $store->id,
                'permission_id' => $item,
            ]);
        }

        return $store->id;
    }

    public function update($id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->update($data);

        PermissionRole::where('role_id', $id)->delete();

        foreach ($data['permission_id'] as $item) {
            PermissionRole::create([
                'role_id' => $id,
                'permission_id' => $item,
            ]);
        }

        return $role->id;
    }


    public function delete($id){}
}
