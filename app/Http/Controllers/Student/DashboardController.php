<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentFeedbackService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected StudentFeedbackService $feedback
    ) {}

    public function __invoke(): View
    {
        $student = auth()->user()->student;
        $version = $this->feedback->currentVersionForStudent($student);
        $assigned = $this->feedback->assignedStaff($student);
        $progress = $version
            ? $this->feedback->progress($student, $version)
            : ['completed' => 0, 'total' => $assigned->count()];

        return view('student.dashboard', compact('student', 'version', 'assigned', 'progress'));
    }
}
