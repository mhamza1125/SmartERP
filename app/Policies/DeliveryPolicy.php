<?php

namespace App\Policies;

use App\Models\User;

class DeliveryPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('deliveries_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('deliveries_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('deliveries_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('deliveries_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('deliveries_delete');
    }
}
