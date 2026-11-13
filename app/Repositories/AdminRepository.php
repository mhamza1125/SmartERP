<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\DB;

class AdminRepository implements GlobalInterface
{
    use ChecksReferences;

    public function role(){
        return Role::all();
    }

    public function permission(){
        return Permission::all();
    }

    /**
     * Permissions grouped by section and module, labelled for the Roles & Permissions screen.
     */
    public function groupedPermissions(): array
    {
        return PermissionCatalog::grouped(Permission::orderBy('name')->get(['id', 'name']));
    }

    /**
     * Per role: user count and "Module: Access, View, ..." lines for the roles list.
     *
     * @return array<int, array{users: int, modules: array<int, string>}>
     */
    public function roleSummaries(): array
    {
        $names = DB::table('permission_role')->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->select('permission_role.role_id', 'permissions.name')->get()->groupBy('role_id');
        $users = DB::table('users')->select('role_id', DB::raw('COUNT(*) as n'))->groupBy('role_id')->pluck('n', 'role_id');
        $labels = collect(PermissionCatalog::SECTIONS)->collapse()->map(fn ($m) => $m[0]);

        $summary = [];
        foreach (Role::pluck('id') as $roleId) {
            $modules = [];
            foreach ($names->get($roleId, collect())->groupBy(fn ($r) => PermissionCatalog::moduleOf($r->name)) as $module => $rows) {
                $actions = $rows->map(fn ($r) => PermissionCatalog::actionOf($r->name));
                $ordered = collect(array_keys(PermissionCatalog::ACTIONS))->filter(fn ($a) => $actions->contains($a))
                    ->map(fn ($a) => PermissionCatalog::ACTIONS[$a][0]);
                $label = $labels[$module] ?? $module;
                $modules[$label] = $label.': '.$ordered->implode(', ');
            }
            ksort($modules);
            $summary[$roleId] = ['users' => (int) ($users[$roleId] ?? 0), 'modules' => array_values($modules)];
        }

        return $summary;
    }

    /**
     * The system Admin role (role id 1) always holds every permission.
     */
    public function isSystemAdminRole($roleId): bool
    {
        return (int) $roleId === User::ROLE_ADMIN;
    }

    public function all(){}

    public function get($id){}

    public function store(array $data)
    {
        $store = Role::create(['name' => $data['name']]);
        $this->syncPermissions($store->id, $data['permission_id'] ?? []);

        return $store->id;
    }

    public function update($id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $data['name']]);
        $this->syncPermissions($role->id, $this->isSystemAdminRole($role->id)
            ? Permission::pluck('id')->all()
            : ($data['permission_id'] ?? []));

        return $role->id;
    }

    /**
     * Replaces a role's permissions, adding the ones the selection depends on
     * (PermissionCatalog::normalize: any permission of a module => its Access; Edit => View).
     *
     * @return array<int, string> the permission names the role now has
     */
    public function syncPermissions($roleId, array $permissionIds): array
    {
        $selected = Permission::whereIn('id', array_map('intval', $permissionIds))->pluck('name');
        $names = PermissionCatalog::normalize($selected);
        $ids = Permission::whereIn('name', $names)->pluck('id');

        DB::transaction(function () use ($roleId, $ids) {
            PermissionRole::where('role_id', $roleId)->delete();
            foreach ($ids as $permissionId) {
                PermissionRole::create(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
        });

        return $names;
    }

    /**
     * A role can be deleted only while no user has it; the Admin role (role id 1) never.
     * Its permission grants are deleted with it.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $role = Role::findOrFail($id);
        $impact = DeletionImpact::for('Role '.$role->name);

        if ((int) $role->id === User::ROLE_ADMIN) {
            return $impact->block('the Admin role is required by the software');
        }
        $impact->blockIf($this->refCount('users', 'role_id', $id), 'user', null, $this->refExamples('users', 'role_id', $id, 'name'));
        $impact->cascadeIf(PermissionRole::where('role_id', $id)->count(), 'permission grant');

        return $impact;
    }

    public function delete($id)
    {
        PermissionRole::where('role_id', $id)->delete();

        return Role::findOrFail($id)->delete();
    }
}
