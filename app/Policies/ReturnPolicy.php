<?php

namespace App\Policies;

use App\Models\User;

class ReturnPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('returns_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('returns_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('returns_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('returns_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('returns_delete');
    }
}
