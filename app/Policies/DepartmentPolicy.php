<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function deleteResponses(User $user, Department $department): bool
    {
        return $user->isAdmin();
    }

    public function deleteStudents(User $user, Department $department): bool
    {
        return $user->isAdmin();
    }
}
