<?php

namespace App\Policies;

use App\Models\User;

class HeadPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('heads_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('heads_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('heads_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('heads_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('heads_delete');
    }
}
