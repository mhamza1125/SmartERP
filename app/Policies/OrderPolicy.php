<?php

namespace App\Policies;

use App\Models\User;

class OrderPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('orders_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('orders_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('orders_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('orders_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('orders_delete');
    }
}
