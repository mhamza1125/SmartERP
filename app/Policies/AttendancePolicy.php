<?php

namespace App\Policies;

use App\Models\User;

class AttendancePolicy
{
    public function access(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendance_access');
    }

    public function show(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendance_show');
    }

    public function create(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendance_create');
    }

    public function edit(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendance_edit');
    }

    public function delete(User $user): bool
    {
        return $user->role_id === 1 || $user->hasPermission('attendance_delete');
    }
}
