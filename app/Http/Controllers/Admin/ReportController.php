<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedbackFormVersion;
use App\Models\FeedbackQuestion;
use App\Models\StaffSubject;
use App\Repositories\ReportRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportRepository $reports
    ) {}

    public function participation(Request $request): View
    {
        $filters = $request->only(['college_id', 'department_id', 'semester_id', 'form_version_id']);
        $filters = array_filter($filters, fn ($v) => $v !== null && $v !== '');
        $collegeId = isset($filters['college_id']) ? (int) $filters['college_id'] : null;
        $departmentId = isset($filters['department_id']) ? (int) $filters['department_id'] : null;
        $semesterId = isset($filters['semester_id']) ? (int) $filters['semester_id'] : null;
        $formVersionId = isset($filters['form_version_id']) ? (int) $filters['form_version_id'] : null;

        $participation = $this->reports->participationRatio($collegeId, $departmentId, $semesterId, $formVersionId);
        $lists = $this->reports->filterLists();
        $versions = FeedbackFormVersion::query()->orderByDesc('id')->limit(50)->get();

        return view('admin.reports.participation', compact('participation', 'lists', 'filters', 'versions'));
    }

    public function staff(Request $request): View
    {
        $versions = FeedbackFormVersion::query()->orderByDesc('id')->limit(50)->get();
        $staffList = StaffSubject::query()->with(['college', 'department'])->orderBy('instructor_name')->limit(500)->get();

        if (! $request->filled('staff_subject_id') || ! $request->filled('form_version_id')) {
            return view('admin.reports.staff', [
                'staffSubject' => null,
                'stats' => null,
                'overall' => null,
                'deptAvg' => null,
                'versionId' => null,
                'versions' => $versions,
                'staffList' => $staffList,
            ]);
        }

        $request->validate([
            'staff_subject_id' => ['required', 'exists:staff_subjects,id'],
            'form_version_id' => ['required', 'exists:feedback_form_versions,id'],
        ]);
        $staffSubject = StaffSubject::query()->findOrFail((int) $request->staff_subject_id);
        $versionId = (int) $request->form_version_id;
        $stats = $this->reports->staffSubjectQuestionStats($staffSubject->id, $versionId);
        $overall = $this->reports->overallLikertAverageForStaffSubject($staffSubject->id, $versionId);
        $deptAvg = $this->reports->departmentLikertAverage($staffSubject->department_id, $versionId);

        return view('admin.reports.staff', compact('staffSubject', 'stats', 'overall', 'deptAvg', 'versionId', 'versions', 'staffList'));
    }

    public function special(Request $request): View
    {
        $filters = $request->only(['college_id', 'department_id', 'semester_id', 'subject', 'form_version_id']);
        $filters = array_filter($filters, fn ($v) => $v !== null && $v !== '');
        $versionId = (int) ($filters['form_version_id'] ?? FeedbackFormVersion::query()
            ->where('accepts_submissions', true)
            ->orderByDesc('id')
            ->value('id') ?? 0);
        abort_if($versionId === 0, 404);
        unset($filters['form_version_id']);
        $rows = $this->reports->specialReportRows($versionId, $filters);
        $lists = $this->reports->filterLists();
        $versions = FeedbackFormVersion::query()->orderByDesc('id')->limit(50)->get();

        return view('admin.reports.special', compact('rows', 'lists', 'filters', 'versions', 'versionId'));
    }

    public function results(Request $request): View
    {
        $filters = $request->only(['college_id', 'department_id', 'semester_id', 'subject', 'form_version_id']);
        $filters = array_filter($filters, fn ($v) => $v !== null && $v !== '');
        if (isset($filters['form_version_id'])) {
            $request->validate([
                'form_version_id' => ['exists:feedback_form_versions,id'],
            ]);
        }

        $versions = FeedbackFormVersion::query()->orderByDesc('id')->limit(50)->get();
        $defaultVersionId = $versions->firstWhere('accepts_submissions', true)?->id
            ?? $versions->first()?->id;
        $versionId = isset($filters['form_version_id']) ? (int) $filters['form_version_id'] : $defaultVersionId;
        unset($filters['form_version_id']);

        $rows = $versionId ? $this->reports->evaluationResultRows($versionId, $filters) : [];
        $lists = $this->reports->filterLists();
        $questions = $versionId ? $this->resultQuestionColumns((int) $versionId, $rows) : [];

        return view('admin.reports.results', compact('rows', 'lists', 'filters', 'versions', 'versionId', 'questions'));
    }

    public function exportResultsExcel(Request $request): StreamedResponse
    {
        $versionId = (int) $request->get('form_version_id', 0);
        abort_if($versionId === 0, 404);
        $filters = $request->only(['college_id', 'department_id', 'semester_id', 'subject']);
        $filters = array_map(fn ($v) => is_numeric($v) ? (int) $v : $v, array_filter($filters, fn ($v) => $v !== null && $v !== ''));

        $rows = $this->reports->evaluationResultRows($versionId, $filters);
        $questionLabels = $this->resultQuestionColumns($versionId, $rows);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            __('reports.excel_staff'),
            __('reports.excel_subject'),
            __('reports.excel_college'),
            __('reports.excel_department'),
            __('reports.excel_semester'),
            __('reports.excel_submissions'),
            __('reports.excel_overall_avg'),
        ];
        foreach ($questionLabels as $pq) {
            $headers[] = $pq['label'];
        }

        $questionIdOrder = array_column($questionLabels, 'question_id');

        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).'1', $header);
            $colIndex++;
        }

        $r = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            $values = [
                $row['staff'],
                $row['subject'],
                $row['college'] ?? '',
                $row['department'] ?? '',
                $row['semester'] ?? '',
                $row['submission_count'],
                $row['overall_average'] ?? '',
            ];
            foreach ($values as $val) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).(string) $r, $val);
                $colIndex++;
            }
            $byQid = collect($row['per_question'])->keyBy('question_id');
            foreach ($questionIdOrder as $qid) {
                $cell = $byQid->get($qid);
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex).(string) $r,
                    is_array($cell) ? ($cell['average'] ?? '') : ''
                );
                $colIndex++;
            }
            $r++;
        }

        $writer = new Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tmp);

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, 'evaluation-results-v'.$versionId.'.xlsx');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $versionId = (int) $request->get('form_version_id', 0);
        abort_if($versionId === 0, 404);
        $filters = $request->only(['college_id', 'department_id', 'semester_id', 'subject']);
        $filters = array_map(fn ($v) => is_numeric($v) ? (int) $v : $v, array_filter($filters, fn ($v) => $v !== null && $v !== ''));
        $rows = $this->reports->specialReportRows($versionId, $filters);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Staff');
        $sheet->setCellValue('B1', 'Subject');
        $sheet->setCellValue('C1', 'College');
        $sheet->setCellValue('D1', 'Department');
        $sheet->setCellValue('E1', 'Semester');
        $sheet->setCellValue('F1', 'Overall (Likert avg)');
        $sheet->setCellValue('G1', 'Question aggregates (JSON)');
        $r = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$r, $row['staff']);
            $sheet->setCellValue('B'.$r, $row['subject']);
            $sheet->setCellValue('C'.$r, $row['college']);
            $sheet->setCellValue('D'.$r, $row['department']);
            $sheet->setCellValue('E'.$r, $row['semester']);
            $sheet->setCellValue('F'.$r, $row['overall'] ?? '');
            $sheet->setCellValue('G'.$r, json_encode($row['per_question']));
            $r++;
        }

        $writer = new Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tmp);

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, 'feedback-report-'.$versionId.'.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $versionId = (int) $request->get('form_version_id', 0);
        abort_if($versionId === 0, 404);
        $filters = $request->only(['college_id', 'department_id', 'semester_id', 'subject']);
        $filters = array_map(fn ($v) => is_numeric($v) ? (int) $v : $v, array_filter($filters, fn ($v) => $v !== null && $v !== ''));
        $rows = $this->reports->specialReportRows($versionId, $filters);

        $pdf = Pdf::loadView('admin.reports.special_pdf', ['rows' => $rows]);

        return $pdf->download('feedback-report-'.$versionId.'.pdf');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $versionId = (int) $request->get('form_version_id', 0);
        abort_if($versionId === 0, 404);
        $filters = $request->only(['college_id', 'department_id', 'semester_id', 'subject']);
        $filters = array_map(fn ($v) => is_numeric($v) ? (int) $v : $v, array_filter($filters, fn ($v) => $v !== null && $v !== ''));
        $rows = $this->reports->specialReportRows($versionId, $filters);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="feedback-'.$versionId.'.csv"',
        ];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['staff', 'subject', 'college', 'department', 'semester', 'overall_likert_avg']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['staff'],
                    $row['subject'],
                    $row['college'],
                    $row['department'],
                    $row['semester'],
                    $row['overall'] ?? '',
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    /**
     * @param  list<array{per_question?: list<array<string, mixed>>}>  $rows
     * @return list<array{question_id: int, label: string}>
     */
    protected function resultQuestionColumns(int $versionId, array $rows): array
    {
        if ($rows !== [] && ($rows[0]['per_question'] ?? []) !== []) {
            return array_map(fn ($pq) => [
                'question_id' => $pq['question_id'],
                'label' => $pq['label'],
            ], $rows[0]['per_question']);
        }

        return FeedbackQuestion::query()
            ->where('feedback_form_version_id', $versionId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (FeedbackQuestion $q) => [
                'question_id' => $q->id,
                'label' => $q->localizedLabel(),
            ])
            ->all();
    }
}
