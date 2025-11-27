<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = ['Admin', 'Manager', 'Accountant'];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role],
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            );
        }

        // 2. Create Permissions
        $tables = [
            'stocks', 'issuances', 'purchases', 'transactions', 'banks',
            'machines', 'orders', 'deliveries', 'customers', 'employees', 'vendors',
            'contractors', 'products', 'materials', 'attendance', 'payroll',
            'reports', 'settings', 'assets'
        ];        

        $allPermissions = [];

        foreach ($tables as $table) {
            foreach (['access', 'show' , 'create', 'edit', 'delete'] as $action) {
                $name = "{$table}_{$action}";
                DB::table('permissions')->updateOrInsert(
                    ['name' => $name],
                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
                );
                $allPermissions[] = $name;
            }
        }

        // 3. Assign Permissions (manual DB insert into permission_role)
        $roleIds = DB::table('roles')->pluck('id', 'name'); // ['Admin' => 1, ...]

        $allPermissionRecords = DB::table('permissions')->get();

        foreach ($allPermissionRecords as $permission) {
            $name = $permission->name;

            // Assign to Admin: all
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $roleIds['Admin'],
                'permission_id' => $permission->id,
            ]);

            // Assign to Manager: only show, access, create
            if (str_ends_with($name, '_show') || str_ends_with($name, '_access') || str_ends_with($name, '_create')) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleIds['Manager'],
                    'permission_id' => $permission->id,
                ]);
            }

            // Assign to Accountant: only show and access
            if (str_ends_with($name, '_show') || str_ends_with($name, '_access')) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleIds['Accountant'],
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
}
