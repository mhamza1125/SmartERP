<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permissions no page or action checks (no such screen exists): ticking them did nothing.
     */
    private array $unused = [
        'stocks_create', 'stocks_delete',
        'reports_show', 'reports_create', 'reports_edit', 'reports_delete',
        'attendance_show', 'heads_show', 'categories_show', 'users_show', 'roles_show',
    ];

    /**
     * 1. Removes the unused permissions (and their role grants).
     * 2. Applies the dependency rule of App\Support\PermissionCatalog::normalize() to every existing
     *    role: any permission of a module adds the module's Access, Edit adds View. Only adds
     *    grants (e.g. the Accountant role had Products: View without Products: Access, so it could
     *    open a product but not the product list or menu); nothing a role has is taken away.
     * 3. The system Admin role (id 1) gets every permission.
     */
    public function up(): void
    {
        $unusedIds = DB::table('permissions')->whereIn('name', $this->unused)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $unusedIds)->delete();
        DB::table('permissions')->whereIn('id', $unusedIds)->delete();

        $permissions = DB::table('permissions')->pluck('id', 'name');
        foreach (DB::table('roles')->pluck('id') as $roleId) {
            $has = DB::table('permission_role')->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $roleId)->pluck('permissions.name')->all();
            $want = (int) $roleId === 1 ? $permissions->keys()->all() : $this->normalize($has);
            foreach (array_diff($want, $has) as $name) {
                if (isset($permissions[$name])) {
                    DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $permissions[$name]]);
                }
            }
        }
    }

    /**
     * Restores the removed permission names (without grants); added grants are kept.
     */
    public function down(): void
    {
        foreach ($this->unused as $name) {
            DB::table('permissions')->updateOrInsert(['name' => $name], ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }
    }

    /** Copy of PermissionCatalog::normalize() so this migration never changes with the app code. */
    private function normalize(array $names): array
    {
        $out = $names;
        foreach ($names as $name) {
            $module = substr($name, 0, strrpos($name, '_'));
            $action = substr($name, strrpos($name, '_') + 1);
            if ($action !== 'access') {
                $out[] = $module.'_access';
            }
            if ($action === 'edit') {
                $out[] = $module.'_show';
            }
        }

        return array_values(array_unique($out));
    }
};
