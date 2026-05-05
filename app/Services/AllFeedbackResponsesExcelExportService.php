<?php

namespace App\Services;

use App\Enums\FeedbackQuestionType;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackSubmission;
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
        $sheet->fromArray([$headers], null, 'A1', true);

        $questionIds = array_column($questionColumns, 'id');
        /** @var array<int, FeedbackQuestionType> $questionTypesById */
        $questionTypesById = $questions->keyBy('id')->map(fn (FeedbackQuestion $q) => $q->type)->all();

        $r = 2;

        $baseQuery = FeedbackSubmission::query()
            ->with([
                'version' => fn ($q) => $q->withTrashed()->with([
                    'form' => fn ($fq) => $fq->withTrashed(),
                ]),
                'answers',
                'staffSubject' => fn ($q) => $q->withTrashed()->with([
                    'college' => fn ($cq) => $cq->withTrashed(),
                    'department' => fn ($dq) => $dq->withTrashed(),
                    'semester' => fn ($sq) => $sq->withTrashed(),
                ]),
            ]);

        $table = (new FeedbackSubmission)->getTable();
        $qualifiedSubmittedAt = $table.'.submitted_at';
        $qualifiedKey = $table.'.id';
        $cursorSubmittedAt = '1970-01-01 00:00:00';
        $cursorId = 0;
        $chunkSize = 1000;

        while (true) {
            $batch = (clone $baseQuery)
                ->whereRaw(
                    "({$qualifiedSubmittedAt}, {$qualifiedKey}) > (?, ?)",
                    [$cursorSubmittedAt, $cursorId]
                )
                ->orderBy($qualifiedSubmittedAt)
                ->orderBy($qualifiedKey)
                ->limit($chunkSize)
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            $rows = [];

            foreach ($batch as $submission) {
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

                $row = [
                    $college,
                    $department,
                    $staff,
                    $semester,
                    $subject,
                    $formTitle,
                    $versionNo,
                    $submittedAt,
                ];

                foreach ($questionIds as $qid) {
                    $ans = $byQ->get($qid);
                    $raw = $ans?->value;
                    $text = '';
                    if (is_array($raw)) {
                        $type = $questionTypesById[$qid] ?? null;
                        $text = $type instanceof FeedbackQuestionType
                            ? $this->formatAnswerCell($type, $raw)
                            : (string) json_encode($raw, JSON_UNESCAPED_UNICODE);
                    }
                    $row[] = $text;
                }

                $rows[] = $row;
            }

            $sheet->fromArray($rows, null, 'A'.$r, true);
            $r += count($rows);

            $last = $batch->last();
            $cursorSubmittedAt = $last->submitted_at->format('Y-m-d H:i:s');
            $cursorId = $last->id;
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
        $writer->setPreCalculateFormulas(false);
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tmp);
        $filename = $basename.'-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, $filename);
    }
}
