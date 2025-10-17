<?php

namespace App\Policies;

use App\Models\User;

class SettingsPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('settings_delete');
    }
}
