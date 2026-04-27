<?php

namespace App\Services;

use App\Models\StaffSubject;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AdminRosterService
{
    /**
     * Soft-delete all non–trashed students (database rules: soft deletes only).
     */
    public function softDeleteAllStudents(): int
    {
        return (int) DB::transaction(function () {
            $query = Student::query();
            $count = (clone $query)->count();
            $query->delete();

            return $count;
        });
    }

    /**
     * Soft-delete all non–trashed staff_subject rows (database rules: soft deletes only).
     */
    public function softDeleteAllStaffSubjects(): int
    {
        return (int) DB::transaction(function () {
            $query = StaffSubject::query();
            $count = (clone $query)->count();
            $query->delete();

            return $count;
        });
    }
}
