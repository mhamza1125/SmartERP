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

        // 2. Create Permissions grouped by module
        // Format: module => [actions...]
        $modules = [
            // Sales & Delivery
            'orders'        => ['access', 'show', 'create', 'edit', 'delete'],
            'deliveries'    => ['access', 'show', 'create', 'edit', 'delete'],
            'packing_lists' => ['access', 'show', 'create', 'edit', 'delete'],

            // Procurement & Stock
            'purchases'     => ['access', 'show', 'create', 'edit', 'delete'],
            'receives'      => ['access', 'show', 'create', 'edit', 'delete'],
            'returns'       => ['access', 'show', 'create', 'edit', 'delete'],
            'stocks'        => ['access', 'show', 'create', 'edit', 'delete'],
            'issuances'     => ['access', 'show', 'create', 'edit', 'delete'],

            // Products & Materials
            'products'      => ['access', 'show', 'create', 'edit', 'delete'],
            'materials'     => ['access', 'show', 'create', 'edit', 'delete'],
            'machines'      => ['access', 'show', 'create', 'edit', 'delete'],

            // Parties
            'customers'     => ['access', 'show', 'create', 'edit', 'delete'],
            'vendors'       => ['access', 'show', 'create', 'edit', 'delete'],
            'contractors'   => ['access', 'show', 'create', 'edit', 'delete'],
            'employees'     => ['access', 'show', 'create', 'edit', 'delete'],

            // Finance
            'transactions'  => ['access', 'show', 'create', 'edit', 'delete'],
            'banks'         => ['access', 'show', 'create', 'edit', 'delete'],
            'assets'        => ['access', 'show', 'create', 'edit', 'delete'],

            // HR
            'attendance'    => ['access', 'show', 'create', 'edit', 'delete'],
            'payroll'       => ['access', 'show', 'create', 'edit', 'delete'],

            // Reports
            'reports'       => ['access', 'show', 'create', 'edit', 'delete'],

            // Settings & Config
            'settings'      => ['access', 'show', 'create', 'edit', 'delete'],
            'categories'    => ['access', 'show', 'create', 'edit', 'delete'],
            'heads'         => ['access', 'show', 'create', 'edit', 'delete'],

            // Admin
            'users'         => ['access', 'show', 'create', 'edit', 'delete'],
            'roles'         => ['access', 'show', 'create', 'edit', 'delete'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = "{$module}_{$action}";
                DB::table('permissions')->updateOrInsert(
                    ['name' => $name],
                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
                );
            }
        }

        // 3. Assign Permissions to Roles
        $roleIds = DB::table('roles')->pluck('id', 'name');
        $allPermissions = DB::table('permissions')->get();

        // Manager-accessible modules (operational access)
        $managerModules = [
            'orders', 'deliveries', 'packing_lists',
            'purchases', 'receives', 'returns',
            'stocks', 'issuances',
            'products', 'materials', 'machines',
            'customers', 'vendors', 'contractors', 'employees',
            'attendance',
        ];

        // Accountant-accessible modules (finance & view-only)
        $accountantModules = [
            'orders', 'deliveries',
            'purchases', 'receives',
            'customers', 'vendors', 'employees',
            'transactions', 'banks', 'assets',
            'payroll', 'reports',
        ];

        foreach ($allPermissions as $permission) {
            $name = $permission->name;

            // Admin gets ALL permissions
            DB::table('permission_role')->updateOrInsert([
                'role_id'       => $roleIds['Admin'],
                'permission_id' => $permission->id,
            ]);

            // Extract module and action from permission name (last segment after final _)
            $lastUnderscore = strrpos($name, '_');
            $module = substr($name, 0, $lastUnderscore);
            $action = substr($name, $lastUnderscore + 1);

            // Manager: access + show + create on their modules
            if (in_array($module, $managerModules) &&
                in_array($action, ['access', 'show', 'create'])) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id'       => $roleIds['Manager'],
                    'permission_id' => $permission->id,
                ]);
            }

            // Accountant: access + show only on their modules
            if (in_array($module, $accountantModules) &&
                in_array($action, ['access', 'show'])) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id'       => $roleIds['Accountant'],
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
}
