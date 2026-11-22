<?php

namespace App\Policies;

use App\Models\User;

/** Stock Adjustments (stock_adjustments_*). No edit: a wrong adjustment is deleted and entered again. */
class StockAdjustmentPolicy
{
    public function access(User $user): bool
    {
        return $user->hasPermission('stock_adjustments_access');
    }

    public function show(User $user): bool
    {
        return $user->hasPermission('stock_adjustments_show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('stock_adjustments_create');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('stock_adjustments_delete');
    }
}
