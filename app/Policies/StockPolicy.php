<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Stock;

class StockPolicy
{
    // ===================================
    // These are being used for Issuances   
    // ===================================

    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('issuances_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('issuances_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('issuances_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('issuances_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('issuances_delete');
    }

    // ==============================
    // This is being used for Stocks
    // ==============================

    public function stocks_access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_access');
    }

    public function stocks_show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_show');
    }

    public function stocks_create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_create');
    }

    public function stocks_edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_edit');
    }

    public function stocks_delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_delete');
    }
}
