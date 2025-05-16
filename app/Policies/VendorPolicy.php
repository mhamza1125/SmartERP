<?php

namespace App\Policies;

use App\Models\User;

class VendorPolicy
{
    // =================================
    // These are being used for Vendors
    // =================================

    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('vendors_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('vendors_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('vendors_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('vendors_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('vendors_delete');
    }

    // ===================================
    // This is being used for Contractors
    // ===================================

    public function contractors_access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_access');
    }

    public function contractors_show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_show');
    }

    public function contractors_create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_create');
    }

    public function contractors_edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_edit');
    }

    public function contractors_delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_delete');
    }
}
