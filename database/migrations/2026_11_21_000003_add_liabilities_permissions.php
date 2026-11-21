<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Permissions of the Liabilities module (LiabilityPolicy). Only the Admin role gets them here;
 * other roles get them under Settings > Roles & Permissions.
 */
return new class extends Migration
{
    private array $actions = ['access', 'show', 'create', 'edit', 'delete'];

    private function names(): array
    {
        return array_map(fn ($action) => "liabilities_{$action}", $this->actions);
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

    public function down(): void
    {
        $ids = DB::table('permissions')->whereIn('name', $this->names())->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
