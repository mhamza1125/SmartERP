<?php

namespace App\Policies;

use App\Models\User;

class ContractorPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('contractors_delete');
    }
}
