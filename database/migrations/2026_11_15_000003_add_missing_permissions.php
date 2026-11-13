<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permissions checked by the policies (ReceivePolicy, ReturnPolicy, HeadPolicy,
     * CategoryPolicy, AdminPolicy users_* / roles_*) but missing from the permissions table, so
     * they could not be granted to any role and those pages worked for the Admin only.
     * Only the names are added (plus the Admin role, which has every permission); other roles
     * get them under Settings > Roles & Permissions.
     */
    private array $modules = ['receives', 'returns', 'users', 'roles', 'heads', 'categories'];

    private array $actions = ['access', 'show', 'create', 'edit', 'delete'];

    private function names(): array
    {
        $names = [];
        foreach ($this->modules as $module) {
            foreach ($this->actions as $action) {
                $names[] = "{$module}_{$action}";
            }
        }

        return $names;
    }

    public function up(): void
    {
        $adminRoleId = DB::table('roles')->where('id', 1)->value('id');
        foreach ($this->names() as $name) {
            if (! DB::table('permissions')->where('name', $name)->exists()) {
                DB::table('permissions')->insert(['name' => $name, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            }
            $permissionId = DB::table('permissions')->where('name', $name)->value('id');
            if ($adminRoleId && ! DB::table('permission_role')->where('role_id', $adminRoleId)->where('permission_id', $permissionId)->exists()) {
                DB::table('permission_role')->insert(['role_id' => $adminRoleId, 'permission_id' => $permissionId]);
            }
        }
    }

    /**
     * Removes the permissions and every role grant they have since received.
     */
    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', $this->names())->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
