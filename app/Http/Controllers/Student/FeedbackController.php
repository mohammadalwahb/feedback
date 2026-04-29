<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FeedbackFormVersion;
use App\Models\FeedbackQuestion;
use App\Services\StudentFeedbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function __construct(
        protected StudentFeedbackService $feedback
    ) {}

    public function index(): View
    {
        $student = auth()->user()->student;
        $version = $this->feedback->currentVersionForStudent($student);
        $assigned = $this->feedback->assignedStaff($student);
        $evaluatedStaffIds = $version
            ? $this->feedback->completedStaffSubjectIds($student, $version)
            : [];
        $progress = $version
            ? $this->feedback->progress($student, $version)
            : ['completed' => 0, 'total' => $assigned->count()];

        return view('student.feedback.index', compact('student', 'version', 'assigned', 'progress', 'evaluatedStaffIds'));
    }

    public function start(Request $request): RedirectResponse
    {
        $student = auth()->user()->student;
        $version = $this->feedback->currentVersionForStudent($student);
        if (! $version) {
            throw ValidationException::withMessages(['form' => __('feedback.closed')]);
        }

        $data = $request->validate([
            'staff_subject_ids' => ['required', 'array', 'min:1'],
            'staff_subject_ids.*' => ['integer', 'exists:staff_subjects,id'],
        ]);

        $allowed = $student->evaluatableStaffSubjectIds();
        $evaluated = $this->feedback->completedStaffSubjectIds($student, $version);
        foreach ($data['staff_subject_ids'] as $sid) {
            if (! in_array((int) $sid, $allowed, true)) {
                throw ValidationException::withMessages(['staff_subject_ids' => __('feedback.staff_not_in_context')]);
            }
            if (in_array((int) $sid, $evaluated, true)) {
                throw ValidationException::withMessages(['staff_subject_ids' => __('feedback.already_submitted')]);
            }
        }

        session([
            'fb_version_id' => $version->id,
            'fb_staff' => array_values(array_map('intval', $data['staff_subject_ids'])),
            'fb_step' => 0,
            'fb_answers' => [],
        ]);

        return redirect()->route('student.feedback.wizard');
    }

    public function wizard(): View|RedirectResponse
    {
        $student = auth()->user()->student;
        $ctx = $this->sessionContext();
        if (! $ctx) {
            return redirect()->route('student.feedback.index');
        }

        $version = FeedbackFormVersion::query()->with(['questions' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($ctx['version_id']);
        $questions = $version->questions;
        if ($questions->isEmpty()) {
            return redirect()->route('student.feedback.index')->with('error', __('feedback.no_questions'));
        }

        if ($ctx['step'] >= $questions->count()) {
            return redirect()->route('student.feedback.review');
        }

        /** @var FeedbackQuestion $question */
        $question = $questions[$ctx['step']];
        $staffIds = $ctx['staff'];
        $staffModels = \App\Models\StaffSubject::query()->whereIn('id', $staffIds)->get()->keyBy('id');

        $existing = $ctx['answers'][$question->id] ?? [];

        return view('student.feedback.wizard', [
            'version' => $version,
            'question' => $question,
            'staffIds' => $staffIds,
            'staffModels' => $staffModels,
            'existing' => $existing,
            'step' => $ctx['step'],
            'totalSteps' => $questions->count(),
        ]);
    }

    public function saveStep(Request $request): RedirectResponse
    {
        $student = auth()->user()->student;
        $ctx = $this->sessionContext();
        if (! $ctx) {
            return redirect()->route('student.feedback.index');
        }

        $version = FeedbackFormVersion::query()->with(['questions' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($ctx['version_id']);
        $questions = $version->questions;
        $question = $questions[$ctx['step']] ?? null;
        if (! $question) {
            return redirect()->route('student.feedback.review');
        }

        $perStaff = $request->input('per_staff', []);
        $merged = $ctx['answers'];
        $row = [];
        foreach ($ctx['staff'] as $sid) {
            $payload = $perStaff[(string) $sid] ?? $perStaff[$sid] ?? null;
            $normalized = $this->feedback->normalizeAnswer($question, $payload);
            if ($normalized === null && $question->is_required) {
                throw ValidationException::withMessages(['per_staff' => __('validation.required')]);
            }
            $row[$sid] = $normalized;
        }
        $merged[$question->id] = $row;
        session([
            'fb_answers' => $merged,
            'fb_step' => $ctx['step'] + 1,
        ]);

        $this->feedback->saveDraft($student, $version, $ctx['staff'], $ctx['step'] + 1, $merged);

        return redirect()->route('student.feedback.wizard');
    }

    public function review(): View|RedirectResponse
    {
        $ctx = $this->sessionContext();
        if (! $ctx) {
            return redirect()->route('student.feedback.index');
        }
        $version = FeedbackFormVersion::query()->with(['questions' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($ctx['version_id']);
        $staffModels = \App\Models\StaffSubject::query()->whereIn('id', $ctx['staff'])->get()->keyBy('id');

        return view('student.feedback.review', [
            'version' => $version,
            'staffIds' => $ctx['staff'],
            'staffModels' => $staffModels,
            'answers' => $ctx['answers'],
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $student = auth()->user()->student;
        $ctx = $this->sessionContext();
        if (! $ctx) {
            return redirect()->route('student.feedback.index');
        }

        $version = FeedbackFormVersion::query()->with(['questions' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($ctx['version_id']);
        $byStaff = [];
        foreach ($ctx['staff'] as $sid) {
            $byStaff[$sid] = [];
            foreach ($version->questions as $q) {
                $cell = $ctx['answers'][$q->id][$sid] ?? null;
                $byStaff[$sid][$q->id] = $cell;
            }
        }

        $this->feedback->submit($student, $version, $ctx['staff'], $byStaff);

        session()->forget(['fb_version_id', 'fb_staff', 'fb_step', 'fb_answers']);

        return redirect()->route('student.dashboard')->with('ok', __('feedback.thanks'));
    }

    public function saveDraftAjax(Request $request): \Illuminate\Http\JsonResponse
    {
        $student = auth()->user()->student;
        $ctx = $this->sessionContext();
        if (! $ctx) {
            return response()->json(['ok' => false], 400);
        }
        $version = FeedbackFormVersion::query()->findOrFail($ctx['version_id']);
        $this->feedback->saveDraft($student, $version, $ctx['staff'], $ctx['step'], $ctx['answers']);

        return response()->json(['ok' => true]);
    }

    /**
     * @return array{version_id:int, staff: list<int>, step:int, answers: array}|null
     */
    protected function sessionContext(): ?array
    {
        if (! session()->has('fb_version_id')) {
            return null;
        }

        return [
            'version_id' => (int) session('fb_version_id'),
            'staff' => session('fb_staff', []),
            'step' => (int) session('fb_step', 0),
            'answers' => session('fb_answers', []),
        ];
    }
}
