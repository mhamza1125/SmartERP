<?php

namespace App\Policies;

use App\Models\User;

class EmployeePolicy
{
    public function access(User $user): bool
    {
        return $user->hasPermission('employees_access');
    }

    public function show(User $user): bool
    {
        return $user->hasPermission('employees_show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('employees_create');
    }

    public function edit(User $user): bool
    {
        return $user->hasPermission('employees_edit');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('employees_delete');
    }
}
