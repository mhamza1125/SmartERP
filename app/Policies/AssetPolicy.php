<?php

namespace App\Policies;

use App\Models\User;

class AssetPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('assets_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('assets_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('assets_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('assets_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('assets_delete');
    }
}

