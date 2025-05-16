<?php

namespace App\Policies;

use App\Models\User;

class MaterialPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('materials_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('materials_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('materials_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('materials_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('materials_delete');
    }
}
