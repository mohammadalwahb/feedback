<?php

namespace App\Services;

use App\Enums\FeedbackQuestionType;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackSubmission;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AllFeedbackResponsesExcelExportService
{
    public function streamWorkbook(): StreamedResponse
    {
        $versionIds = FeedbackSubmission::query()
            ->distinct()
            ->pluck('feedback_form_version_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $questions = $versionIds === []
            ? collect()
            : FeedbackQuestion::query()
                ->withTrashed()
                ->whereIn('feedback_form_version_id', $versionIds)
                ->orderBy('feedback_form_version_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

        $questionColumns = $questions->map(fn (FeedbackQuestion $q) => [
            'id' => $q->id,
            'header' => $this->questionHeaderText($q),
        ])->all();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $staticHeaders = [
            __('reports.excel_college'),
            __('reports.excel_department'),
            __('reports.excel_staff'),
            __('reports.excel_semester'),
            __('reports.excel_subject'),
            __('reports.excel_form'),
            __('reports.excel_form_version'),
            __('reports.excel_submitted_at'),
        ];

        $headers = array_merge($staticHeaders, array_column($questionColumns, 'header'));

        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).'1', $header);
            $colIndex++;
        }

        $questionIds = array_column($questionColumns, 'id');
        $r = 2;

        $query = FeedbackSubmission::query()
            ->with([
                'version' => fn ($q) => $q->withTrashed()->with([
                    'form' => fn ($fq) => $fq->withTrashed(),
                ]),
                'answers' => fn ($q) => $q->with(['question' => fn ($qq) => $qq->withTrashed()]),
                'staffSubject' => fn ($q) => $q->withTrashed()->with([
                    'college' => fn ($cq) => $cq->withTrashed(),
                    'department' => fn ($dq) => $dq->withTrashed(),
                    'semester' => fn ($sq) => $sq->withTrashed(),
                ]),
            ])
            ->orderBy('submitted_at')
            ->orderBy('id');

        foreach ($query->cursor() as $submission) {
            $st = $submission->staffSubject;
            $college = $st?->college?->name_en ?? '';
            $department = $st?->department?->name_en ?? '';
            $staff = $st?->instructor_name ?? '';
            $semester = $st?->semester ? ($st->semester->name_en ?? $st->semester->localizedName()) : '';
            $subject = $st?->subject_name ?? '';
            $formTitle = $submission->version?->form?->title_en ?? '';
            $versionNo = $submission->version?->version_number ?? '';
            $submittedAt = $submission->submitted_at?->format('Y-m-d H:i:s') ?? '';

            $byQ = $submission->answers->keyBy('feedback_question_id');

            $colIndex = 1;
            foreach ([$college, $department, $staff, $semester, $subject, $formTitle, $versionNo, $submittedAt] as $cell) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).(string) $r, $cell);
                $colIndex++;
            }

            foreach ($questionIds as $qid) {
                $ans = $byQ->get($qid);
                $q = $ans?->question;
                $raw = $ans?->value;
                $text = '';
                if (is_array($raw)) {
                    $text = $q instanceof FeedbackQuestion
                        ? $this->formatAnswerCell($q->type, $raw)
                        : (string) json_encode($raw, JSON_UNESCAPED_UNICODE);
                }
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).(string) $r, $text);
                $colIndex++;
            }

            $r++;
        }

        return $this->streamXlsx($spreadsheet, 'all-feedback-responses');
    }

    protected function questionHeaderText(FeedbackQuestion $q): string
    {
        $parts = collect([$q->label_en, $q->label_ku, $q->label_ar])
            ->filter(fn ($x) => filled($x))
            ->unique()
            ->values()
            ->all();

        return $parts === [] ? ('Q#'.$q->id) : implode(' / ', $parts);
    }

    /**
     * @param  array<string, mixed>  $value
     */
    protected function formatAnswerCell(FeedbackQuestionType $type, array $value): string
    {
        return match ($type) {
            FeedbackQuestionType::Likert5 => isset($value['v']) ? (string) $value['v'] : '',
            FeedbackQuestionType::YesNo => array_key_exists('v', $value)
                ? ($value['v'] ? __('student.yes') : __('student.no'))
                : '',
            FeedbackQuestionType::MultipleChoice => isset($value['v']) ? (string) $value['v'] : '',
            FeedbackQuestionType::Text, FeedbackQuestionType::Note => isset($value['t']) ? (string) $value['t'] : '',
        };
    }

    protected function streamXlsx(Spreadsheet $spreadsheet, string $basename): StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tmp);
        $filename = $basename.'-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, $filename);
    }
}
