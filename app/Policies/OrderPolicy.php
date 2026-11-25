<?php

namespace App\Policies;

use App\Models\User;

class OrderPolicy
{
    public function access(User $user): bool
    {
        return $user->hasPermission('orders_access');
    }

    public function show(User $user): bool
    {
        return $user->hasPermission('orders_show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('orders_create');
    }

    public function edit(User $user): bool
    {
        return $user->hasPermission('orders_edit');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('orders_delete');
    }

    /**
     * Moving an order back to an earlier status (re-open, incl. from Cancelled / Delivered).
     * No permission grants it: only the Admin role passes (Gate::before in AuthServiceProvider).
     */
    public function reopen(User $user): bool
    {
        return false;
    }
}
