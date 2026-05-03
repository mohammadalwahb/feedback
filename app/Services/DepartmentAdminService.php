<?php

namespace App\Services;

use App\Models\Department;
use App\Models\FeedbackResponseDraft;
use App\Models\FeedbackSubmission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DepartmentAdminService
{
    public function __construct(
        protected AuditLogger $auditLogger,
    ) {}

    /**
     * Hard-delete all feedback drafts and submissions (answers cascade) for the given student IDs.
     */
    protected function permanentlyDeleteFeedbackDataForStudentIds(array $studentIds): int
    {
        if ($studentIds === []) {
            return 0;
        }

        FeedbackResponseDraft::query()
            ->whereIn('student_id', $studentIds)
            ->delete();

        return (int) FeedbackSubmission::query()
            ->whereIn('student_id', $studentIds)
            ->delete();
    }

    /**
     * Permanently remove feedback submissions and drafts for students in this department.
     */
    public function deleteFeedbackForDepartment(Department $department, User $admin): int
    {
        return (int) DB::transaction(function () use ($department, $admin) {
            $studentIds = Student::query()
                ->withTrashed()
                ->where('department_id', $department->id)
                ->pluck('id')
                ->all();

            $deleted = $this->permanentlyDeleteFeedbackDataForStudentIds($studentIds);

            $this->auditLogger->log($admin, 'department.feedback_deleted', $department, [
                'deleted_submissions' => $deleted,
            ]);

            return $deleted;
        });
    }

    /**
     * Permanently delete all students in this department (including soft-deleted roster rows).
     * Feedback data is removed first because submissions use restrictOnDelete on student_id.
     */
    public function permanentlyDeleteStudentsForDepartment(Department $department, User $admin): int
    {
        return (int) DB::transaction(function () use ($department, $admin) {
            $ids = Student::withTrashed()
                ->where('department_id', $department->id)
                ->pluck('id');

            $count = $ids->count();
            if ($count === 0) {
                return 0;
            }

            $this->permanentlyDeleteFeedbackDataForStudentIds($ids->all());

            foreach ($ids->chunk(100) as $chunk) {
                Student::withTrashed()
                    ->whereIn('id', $chunk)
                    ->get()
                    ->each(fn (Student $student) => $student->forceDelete());
            }

            $this->auditLogger->log($admin, 'department.students_permanently_deleted', $department, [
                'count' => $count,
            ]);

            return $count;
        });
    }
}
