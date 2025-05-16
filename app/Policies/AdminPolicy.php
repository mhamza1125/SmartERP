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

    // ===================================
    // This is being used for Attendances
    // ===================================

    public function attendances_access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendances_access');
    }

    public function attendances_show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendances_show');
    }

    public function attendances_create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendances_create');
    }

    public function attendances_edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendances_edit');
    }

    public function attendances_delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendances_delete');
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
}
