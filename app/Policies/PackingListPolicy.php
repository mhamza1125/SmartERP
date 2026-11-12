<?php

namespace App\Policies;

use App\Models\User;

class PackingListPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('delivery_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('delivery_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('delivery_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('delivery_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('delivery_delete');
    }
}
