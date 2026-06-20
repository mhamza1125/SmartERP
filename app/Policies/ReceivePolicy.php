<?php

namespace App\Policies;

use App\Models\User;

class ReceivePolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('receives_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('receives_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('receives_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('receives_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('receives_delete');
    }
}
