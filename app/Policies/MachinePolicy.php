<?php

namespace App\Policies;

use App\Models\User;

class MachinePolicy
{
    public function access(User $user): bool
    {
        return $user->hasPermission('machines_access');
    }

    public function show(User $user): bool
    {
        return $user->hasPermission('machines_show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('machines_create');
    }

    public function edit(User $user): bool
    {
        return $user->hasPermission('machines_edit');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('machines_delete');
    }
}
