<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    // ================================
    // This is being used for Settings
    // ================================
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_delete');
    }

    // ===============================
    // This is being used for Reports
    // ===============================

    public function reports_access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('reports_access');
    }

    public function reports_show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('reports_show');
    }

    public function reports_create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('reports_create');
    }

    public function reports_edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('reports_edit');
    }

    public function reports_delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('reports_delete');
    }

    // ================================
    // This is being used for Payrolls
    // ================================

    public function payroll_access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('payroll_access');
    }

    public function payroll_show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('payroll_show');
    }

    public function payroll_create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('payroll_create');
    }

    public function payroll_edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('payroll_edit');
    }

    public function payroll_delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('payroll_delete');
    }

    // ================================
    // This is being used for Users
    // ================================

    public function users_access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('users_access');
    }

    public function users_show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('users_show');
    }

    public function users_create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('users_create');
    }

    public function users_edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('users_edit');
    }

    public function users_delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('users_delete');
    }

    // ================================
    // This is being used for Roles
    // ================================

    public function roles_access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('roles_access');
    }

    public function roles_show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('roles_show');
    }

    public function roles_create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('roles_create');
    }

    public function roles_edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('roles_edit');
    }

    public function roles_delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('roles_delete');
    }
}
