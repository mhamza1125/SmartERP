<?php

namespace App\Policies;

use App\Models\User;

class PurchasePolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('purchases_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('purchases_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('purchases_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('purchases_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('purchases_delete');
    }
}
