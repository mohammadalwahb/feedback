<?php

namespace App\Policies;

use App\Models\StaffSubject;
use App\Models\User;

class StaffSubjectPolicy
{
    public function deleteAll(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, StaffSubject $staffSubject): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, StaffSubject $staffSubject): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
