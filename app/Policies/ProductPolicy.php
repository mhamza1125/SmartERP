<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('products_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('products_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('products_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('products_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('products_delete');
    }
}
