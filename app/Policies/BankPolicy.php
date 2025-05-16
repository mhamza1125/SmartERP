<?php

namespace App\Policies;

use App\Models\User;

class BankPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('banks_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('banks_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('banks_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('banks_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('banks_delete');
    }
}
