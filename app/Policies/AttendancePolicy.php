<?php

namespace App\Policies;

use App\Models\User;

class AttendancePolicy
{
    public function access(User $user): bool
    {
        return $user->hasPermission('attendance_access');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance_create');
    }

    public function edit(User $user): bool
    {
        return $user->hasPermission('attendance_edit');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('attendance_delete');
    }
}
