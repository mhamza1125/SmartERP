<?php

namespace App\Policies;

use App\Models\User;

class LiabilityPolicy
{
    public function access(User $user): bool
    {
        return $user->hasPermission('liabilities_access');
    }

    public function show(User $user): bool
    {
        return $user->hasPermission('liabilities_show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('liabilities_create');
    }

    public function edit(User $user): bool
    {
        return $user->hasPermission('liabilities_edit');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('liabilities_delete');
    }
}
