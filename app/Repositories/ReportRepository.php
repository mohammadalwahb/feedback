<?php

namespace App\Repositories;

use App\Enums\FeedbackQuestionType;
use App\Models\College;
use App\Models\Department;
use App\Models\FeedbackAnswer;
use App\Models\FeedbackFormVersion;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackSubmission;
use App\Models\Semester;
use App\Models\StaffSubject;
use App\Models\Student;
use Illuminate\Support\Collection;

class ReportRepository
{
    /**
     * Participation: submitted distinct students / eligible students (those with ≥1 staff row in same college, dept, semester), scoped.
     *
     * @return array{eligible:int, submitted:int, ratio:float}
     */
    public function participationRatio(
        ?int $collegeId = null,
        ?int $departmentId = null,
        ?int $semesterId = null,
        ?int $formVersionId = null
    ): array {
        $versionId = $formVersionId ?? FeedbackFormVersion::query()
            ->where('accepts_submissions', true)
            ->orderByDesc('id')
            ->value('id');

        if (! $versionId) {
            return ['eligible' => 0, 'submitted' => 0, 'ratio' => 0.0];
        }

        $eligibleQ = \App\Models\Student::query()->whereExists(function ($q) {
            $q->selectRaw('1')
                ->from('staff_subjects')
                ->whereColumn('staff_subjects.college_id', 'students.college_id')
                ->whereColumn('staff_subjects.department_id', 'students.department_id')
                ->whereColumn('staff_subjects.semester_id', 'students.semester_id')
                ->whereNull('staff_subjects.deleted_at');
        });
        if ($collegeId) {
            $eligibleQ->where('college_id', $collegeId);
        }
        if ($departmentId) {
            $eligibleQ->where('department_id', $departmentId);
        }
        if ($semesterId) {
            $eligibleQ->where('semester_id', $semesterId);
        }

        $eligible = (int) $eligibleQ->count();

        $subQ = FeedbackSubmission::query()
            ->where('feedback_form_version_id', $versionId);

        if ($collegeId || $departmentId || $semesterId) {
            $subQ->whereHas('student', function ($q) use ($collegeId, $departmentId, $semesterId) {
                if ($collegeId) {
                    $q->where('college_id', $collegeId);
                }
                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }
                if ($semesterId) {
                    $q->where('semester_id', $semesterId);
                }
            });
        }

        $submitted = (int) $subQ->pluck('student_id')->unique()->count();

        $ratio = $eligible > 0 ? round($submitted / $eligible, 4) : 0.0;

        return compact('eligible', 'submitted', 'ratio');
    }

    /**
     * @return array{total:int, evaluated:int}
     */
    public function staffEvaluationProgress(?int $formVersionId = null): array
    {
        $versionId = $formVersionId ?? FeedbackFormVersion::query()
            ->where('accepts_submissions', true)
            ->orderByDesc('id')
            ->value('id');

        $total = (int) StaffSubject::query()->count();
        if (! $versionId) {
            return ['total' => $total, 'evaluated' => 0];
        }

        $evaluated = (int) FeedbackSubmission::query()
            ->where('feedback_form_version_id', $versionId)
            ->distinct()
            ->count('staff_subject_id');

        return ['total' => $total, 'evaluated' => $evaluated];
    }

    /**
     * Aggregates per question for a staff_subject in a form version (anonymous — no student ids returned).
     *
     * @return list<array{question_id:int, type:string, label:string, likert_avg:?float, yes_pct:?float, choice_counts:?array, text_samples:?array}>
     */
    public function staffSubjectQuestionStats(int $staffSubjectId, int $formVersionId, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $questions = FeedbackQuestion::query()
            ->where('feedback_form_version_id', $formVersionId)
            ->orderBy('sort_order')
            ->get();

        $subs = FeedbackSubmission::query()
            ->where('staff_subject_id', $staffSubjectId)
            ->where('feedback_form_version_id', $formVersionId)
            ->pluck('id');

        $out = [];
        foreach ($questions as $q) {
            $label = $q->localizedLabel($locale);
            $row = [
                'question_id' => $q->id,
                'type' => $q->type->value,
                'label' => $label,
                'likert_avg' => null,
                'yes_pct' => null,
                'choice_counts' => null,
                'text_samples' => null,
            ];

            $answers = FeedbackAnswer::query()
                ->where('feedback_question_id', $q->id)
                ->whereIn('feedback_submission_id', $subs)
                ->pluck('value');

            if ($answers->isEmpty()) {
                $out[] = $row;

                continue;
            }

            if ($q->type === FeedbackQuestionType::Likert5) {
                $nums = $answers->map(fn ($v) => (int) ($v['v'] ?? 0))->filter(fn ($n) => $n >= 1 && $n <= 5);
                $row['likert_avg'] = $nums->isEmpty() ? null : round($nums->avg(), 2);
            } elseif ($q->type === FeedbackQuestionType::YesNo) {
                $yes = $answers->filter(fn ($v) => ! empty($v['v']))->count();
                $row['yes_pct'] = round(100 * $yes / max(1, $answers->count()), 1);
            } elseif ($q->type === FeedbackQuestionType::MultipleChoice) {
                $counts = [];
                foreach ($answers as $v) {
                    $k = (string) ($v['v'] ?? '');
                    $counts[$k] = ($counts[$k] ?? 0) + 1;
                }
                $row['choice_counts'] = $counts;
            } elseif ($q->type === FeedbackQuestionType::Text) {
                $row['text_samples'] = $answers->map(fn ($v) => (string) ($v['t'] ?? ''))
                    ->filter()
                    ->take(50)
                    ->values()
                    ->all();
            }

            $out[] = $row;
        }

        return $out;
    }

    public function overallLikertAverageForStaffSubject(int $staffSubjectId, int $formVersionId): ?float
    {
        $likertQuestionIds = FeedbackQuestion::query()
            ->where('feedback_form_version_id', $formVersionId)
            ->where('type', FeedbackQuestionType::Likert5)
            ->pluck('id');

        if ($likertQuestionIds->isEmpty()) {
            return null;
        }

        $subs = FeedbackSubmission::query()
            ->where('staff_subject_id', $staffSubjectId)
            ->where('feedback_form_version_id', $formVersionId)
            ->pluck('id');

        $vals = FeedbackAnswer::query()
            ->whereIn('feedback_question_id', $likertQuestionIds)
            ->whereIn('feedback_submission_id', $subs)
            ->pluck('value')
            ->map(fn ($v) => (int) ($v['v'] ?? 0))
            ->filter(fn ($n) => $n >= 1 && $n <= 5);

        return $vals->isEmpty() ? null : round($vals->avg(), 2);
    }

    /**
     * Department average of likert answers for comparison (all staff in dept for version).
     */
    public function departmentLikertAverage(?int $departmentId, int $formVersionId): ?float
    {
        if (! $departmentId) {
            return null;
        }

        $staffIds = StaffSubject::query()
            ->where('department_id', $departmentId)
            ->pluck('id');

        if ($staffIds->isEmpty()) {
            return null;
        }

        $likertQuestionIds = FeedbackQuestion::query()
            ->where('feedback_form_version_id', $formVersionId)
            ->where('type', FeedbackQuestionType::Likert5)
            ->pluck('id');

        $subs = FeedbackSubmission::query()
            ->whereIn('staff_subject_id', $staffIds)
            ->where('feedback_form_version_id', $formVersionId)
            ->pluck('id');

        $vals = FeedbackAnswer::query()
            ->whereIn('feedback_question_id', $likertQuestionIds)
            ->whereIn('feedback_submission_id', $subs)
            ->pluck('value')
            ->map(fn ($v) => (int) ($v['v'] ?? 0))
            ->filter(fn ($n) => $n >= 1 && $n <= 5);

        return $vals->isEmpty() ? null : round($vals->avg(), 2);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function specialReportRows(int $formVersionId, array $filters = []): array
    {
        $q = StaffSubject::query()
            ->with(['college', 'department', 'semester']);

        if (! empty($filters['college_id'])) {
            $q->where('college_id', $filters['college_id']);
        }
        if (! empty($filters['department_id'])) {
            $q->where('department_id', $filters['department_id']);
        }
        if (! empty($filters['semester_id'])) {
            $q->where('semester_id', $filters['semester_id']);
        }
        if (! empty($filters['subject'])) {
            $q->where('subject_name', 'like', '%'.$filters['subject'].'%');
        }

        $rows = [];
        foreach ($q->orderBy('instructor_name')->cursor() as $staffSubject) {
            $perQ = $this->staffSubjectQuestionStats($staffSubject->id, $formVersionId);
            $rows[] = [
                'staff' => $staffSubject->instructor_name,
                'subject' => $staffSubject->subject_name,
                'college' => $staffSubject->college?->name_en,
                'department' => $staffSubject->department?->name_en,
                'semester' => $staffSubject->semester?->name_en,
                'per_question' => $perQ,
                'overall' => $this->overallLikertAverageForStaffSubject($staffSubject->id, $formVersionId),
            ];
        }

        return $rows;
    }

    public function filterLists(): array
    {
        return [
            'colleges' => College::query()->orderBy('name_en')->get(),
            'departments' => Department::query()->orderBy('name_en')->get(),
            'semesters' => Semester::query()->orderBy('name_en')->get(),
        ];
    }

    /**
     * @return list<array{
     *   staff:string,
     *   subject:string,
     *   college:?string,
     *   department:?string,
     *   semester:?string,
     *   evaluation_count:int,
     *   expected_students:int
     * }>
     */
    public function realtimeStatisticsRows(): array
    {
        $submissionCounts = FeedbackSubmission::query()
            ->selectRaw('staff_subject_id, COUNT(*) as aggregate_count')
            ->groupBy('staff_subject_id')
            ->pluck('aggregate_count', 'staff_subject_id');

        $studentCountsByContext = Student::query()
            ->selectRaw('college_id, department_id, semester_id, COUNT(*) as aggregate_count')
            ->groupBy('college_id', 'department_id', 'semester_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $key = $row->college_id.'|'.$row->department_id.'|'.$row->semester_id;

                return [$key => (int) $row->aggregate_count];
            });

        $rows = StaffSubject::query()
            ->with(['college', 'department', 'semester'])
            ->orderBy('instructor_name')
            ->get()
            ->map(function (StaffSubject $staffSubject) use ($submissionCounts, $studentCountsByContext) {
                $contextKey = $staffSubject->college_id.'|'.$staffSubject->department_id.'|'.$staffSubject->semester_id;

                return [
                    'staff' => $staffSubject->instructor_name,
                    'subject' => $staffSubject->subject_name,
                    'college' => $staffSubject->college?->name_en,
                    'department' => $staffSubject->department?->name_en,
                    'semester' => $staffSubject->semester?->name_en,
                    'evaluation_count' => (int) ($submissionCounts[$staffSubject->id] ?? 0),
                    'expected_students' => (int) ($studentCountsByContext[$contextKey] ?? 0),
                ];
            })
            ->sortByDesc('evaluation_count')
            ->values()
            ->all();

        return $rows;
    }

    /**
     * Per staff member: combines all taught subjects into one result row.
     * Numeric average per question (Likert 1–5, Yes/No as 5/1, MC spread on 1–5, text omitted),
     * then overall = mean of those question averages (only questions with a computed average).
     *
     * @return list<array{
     *   staff_employee_id:string,
     *   staff:string,
     *   subject:string,
     *   college:?string,
     *   department:?string,
     *   semester:?string,
     *   submission_count:int,
     *   per_question:list<array{question_id:int, label:string, type:string, average:?float, response_count:int}>,
     *   overall_average:?float
     * }>
     */
    public function evaluationResultRows(int $formVersionId, array $filters = [], ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        $questions = FeedbackQuestion::query()
            ->where('feedback_form_version_id', $formVersionId)
            ->orderBy('sort_order')
            ->get();

        $q = StaffSubject::query()
            ->with(['college', 'department', 'semester']);

        if (! empty($filters['college_id'])) {
            $q->where('college_id', $filters['college_id']);
        }
        if (! empty($filters['department_id'])) {
            $q->where('department_id', $filters['department_id']);
        }
        if (! empty($filters['semester_id'])) {
            $q->where('semester_id', $filters['semester_id']);
        }
        if (! empty($filters['subject'])) {
            $q->where('subject_name', 'like', '%'.$filters['subject'].'%');
        }

        $rows = [];
        $staffGroups = $q
            ->orderBy('instructor_name')
            ->get()
            ->groupBy(fn (StaffSubject $staffSubject) => $this->staffGroupingKey($staffSubject));

        foreach ($staffGroups as $groupKey => $staffSubjects) {
            /** @var StaffSubject $first */
            $first = $staffSubjects->first();
            if (! $first) {
                continue;
            }
            $staffSubjectIds = $staffSubjects->pluck('id');
            $subs = FeedbackSubmission::query()
                ->whereIn('staff_subject_id', $staffSubjectIds)
                ->where('feedback_form_version_id', $formVersionId)
                ->pluck('id');

            $submissionCount = $subs->count();

            $perQuestion = [];
            $scoresForOverall = [];

            foreach ($questions as $question) {
                $answers = FeedbackAnswer::query()
                    ->where('feedback_question_id', $question->id)
                    ->whereIn('feedback_submission_id', $subs)
                    ->pluck('value');

                $scores = $this->numericScoresForQuestion($question, $answers);
                $avg = $scores->isEmpty() ? null : round((float) $scores->avg(), 2);
                $perQuestion[] = [
                    'question_id' => $question->id,
                    'label' => $question->localizedLabel($locale),
                    'type' => $question->type->value,
                    'average' => $avg,
                    'response_count' => $answers->count(),
                ];
                if ($scores->isNotEmpty()) {
                    $scoresForOverall = array_merge($scoresForOverall, $scores->all());
                }
            }

            $overall = count($scoresForOverall) > 0
                ? round(array_sum($scoresForOverall) / count($scoresForOverall), 2)
                : null;

            $subjects = $staffSubjects->pluck('subject_name')
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $colleges = $staffSubjects->map(fn (StaffSubject $s) => $s->college?->name_en)
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $departments = $staffSubjects->map(fn (StaffSubject $s) => $s->department?->name_en)
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $semesters = $staffSubjects->map(fn (StaffSubject $s) => $s->semester?->name_en)
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');

            $rows[] = [
                'staff_employee_id' => $first->staff_employee_id,
                'staff' => $first->instructor_name,
                'subject' => $subjects !== '' ? $subjects : null,
                'college' => $colleges !== '' ? $colleges : null,
                'department' => $departments !== '' ? $departments : null,
                'semester' => $semesters !== '' ? $semesters : null,
                'submission_count' => $submissionCount,
                'per_question' => $perQuestion,
                'overall_average' => $overall,
            ];
        }

        return $rows;
    }

    /**
     * One scalar per question for “average of averages” (text excluded).
     */
    protected function numericAverageForQuestion(FeedbackQuestion $question, Collection $answers): ?float
    {
        $scores = $this->numericScoresForQuestion($question, $answers);

        return $scores->isEmpty() ? null : round((float) $scores->avg(), 2);
    }

    protected function numericScoresForQuestion(FeedbackQuestion $question, Collection $answers): Collection
    {
        if ($answers->isEmpty()) {
            return collect();
        }

        return match ($question->type) {
            FeedbackQuestionType::Likert5 => $answers
                ->map(fn ($v) => (int) ($v['v'] ?? 0))
                ->filter(fn ($n) => $n >= 1 && $n <= 5)
                ->map(fn ($n) => (float) $n)
                ->values(),
            FeedbackQuestionType::YesNo => $answers->map(function ($v) {
                $raw = $v['v'] ?? null;
                if ($raw === null || $raw === '') {
                    return null;
                }
                if (is_bool($raw)) {
                    return $raw ? 5.0 : 1.0;
                }
                $s = strtolower((string) $raw);
                if (in_array($s, ['yes', '1', 'true'], true)) {
                    return 5.0;
                }
                if (in_array($s, ['no', '0', 'false'], true)) {
                    return 1.0;
                }

                return null;
            })->filter(fn ($x) => $x !== null)->values(),
            FeedbackQuestionType::MultipleChoice => $this->multipleChoiceScores($question, $answers),
            FeedbackQuestionType::Text, FeedbackQuestionType::Note => collect(),
        };
    }

    protected function multipleChoiceScores(FeedbackQuestion $question, Collection $answers): Collection
    {
        $opts = $question->options['choices'] ?? [];
        $keys = collect($opts)->pluck('key')->all();
        if ($keys === []) {
            return collect();
        }

        $n = count($keys);
        $scores = $answers->map(function ($v) use ($keys, $n) {
            $k = (string) ($v['v'] ?? '');
            $idx = array_search($k, $keys, true);
            if ($idx === false) {
                return null;
            }
            if ($n <= 1) {
                return 3.0;
            }

            return 1.0 + ($idx / ($n - 1)) * 4.0;
        })->filter(fn ($x) => $x !== null);

        return $scores->values();
    }

    protected function staffGroupingKey(StaffSubject $staffSubject): string
    {
        $name = preg_replace('/\s+/', ' ', trim((string) $staffSubject->instructor_name)) ?? '';
        if ($name !== '') {
            return 'name:'.mb_strtolower($name);
        }

        $employeeId = trim((string) $staffSubject->staff_employee_id);
        if ($employeeId !== '') {
            return 'emp:'.mb_strtolower($employeeId);
        }

        return 'row:'.$staffSubject->id;
    }
}
