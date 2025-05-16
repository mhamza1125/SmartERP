<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('customers_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('customers_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('customers_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('customers_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('customers_delete');
    }
}
