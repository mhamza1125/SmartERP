<?php

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    /**
     * Category sits under Settings in the menu, so a role given the Settings permission
     * for an action has it here too; the categories permission still works on its own.
     */
    private function allowed(User $user, string $action): bool
    {
        return $user->hasPermission('categories_'.$action)
            || $user->hasPermission('settings_'.$action);
    }

    public function access(User $user): bool
    {
        return $this->allowed($user, 'access');
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'create');
    }

    public function edit(User $user): bool
    {
        return $this->allowed($user, 'edit');
    }

    public function delete(User $user): bool
    {
        return $this->allowed($user, 'delete');
    }
}
