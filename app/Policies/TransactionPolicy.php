<?php

namespace App\Policies;

use App\Models\User;

class TransactionPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('transactions_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('transactions_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('transactions_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('transactions_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('transactions_delete');
    }
}
