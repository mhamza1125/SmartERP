<?php

namespace App\Policies;

use App\Models\User;

/**
 * Batches are part of the stocks module: viewing needs stocks access/show,
 * changing a reservation or splitting a batch needs stocks edit.
 */
class StockBatchPolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_show');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('stocks_edit');
    }
}
