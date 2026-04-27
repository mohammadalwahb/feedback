<?php

namespace App\Services;

use App\Models\College;
use App\Models\Department;
use App\Models\Semester;
use App\Models\StaffSubject;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelImportService
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * @return array{created:int, updated:int, skipped:int, errors: array<int, array{row:int, messages: array}>}
     */
    public function importStudents(UploadedFile $file, User $admin): array
    {
        $rows = $this->readSheetRows($file);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            if ($this->rowEmpty($row)) {
                continue;
            }

            $data = [
                'email' => $this->cell($row, 0),
                'english_name' => $this->cell($row, 1),
                'kurdish_name' => $this->cell($row, 2),
                'arabic_name' => $this->cell($row, 3),
                'college' => $this->cell($row, 4),
                'department' => $this->cell($row, 5),
                'semester' => $this->cell($row, 6),
            ];

            $validator = Validator::make($data, [
                'email' => ['required', 'email', 'regex:/^[^@\s]+@stud\.uoz\.edu\.krd$/i'],
                'english_name' => ['required', 'string', 'max:255'],
                'kurdish_name' => ['nullable', 'string', 'max:255'],
                'arabic_name' => ['nullable', 'string', 'max:255'],
                'college' => ['required', 'string'],
                'department' => ['required', 'string'],
                'semester' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                $errors[] = ['row' => $rowNum, 'messages' => $validator->errors()->all()];
                $skipped++;

                continue;
            }

            $college = College::query()
                ->where('name_en', $data['college'])
                ->orWhere('name_ku', $data['college'])
                ->orWhere('name_ar', $data['college'])
                ->first();

            $department = null;
            if ($college) {
                $department = Department::query()
                    ->where('college_id', $college->id)
                    ->where(function ($q) use ($data) {
                        $q->where('name_en', $data['department'])
                            ->orWhere('name_ku', $data['department'])
                            ->orWhere('name_ar', $data['department']);
                    })
                    ->first();
            }

            $semester = Semester::query()
                ->where('name_en', $data['semester'])
                ->orWhere('name_ku', $data['semester'])
                ->orWhere('name_ar', $data['semester'])
                ->first();

            if (! $college || ! $department || ! $semester) {
                $errors[] = ['row' => $rowNum, 'messages' => [__('imports.invalid_academic_references')]];
                $skipped++;

                continue;
            }

            $emailNorm = strtolower($data['email']);
            $payload = [
                'email' => $emailNorm,
                'english_name' => $data['english_name'],
                'kurdish_name' => $data['kurdish_name'] ?: null,
                'arabic_name' => $data['arabic_name'] ?: null,
                'college_id' => $college->id,
                'department_id' => $department->id,
                'semester_id' => $semester->id,
            ];

            $student = Student::withTrashed()
                ->whereRaw('LOWER(email) = ?', [$emailNorm])
                ->first();

            if ($student) {
                if ($student->trashed()) {
                    $student->restore();
                }
                $student->fill($payload);
                $student->save();
                $updated++;
            } else {
                Student::query()->create($payload);
                $created++;
            }
        }

        $this->auditLogger->log($admin, 'import.students', null, [
            'file' => $file->getClientOriginalName(),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        return compact('created', 'updated', 'skipped', 'errors');
    }

    /**
     * @return array{created:int, updated:int, skipped:int, errors: array<int, array{row:int, messages: array}>}
     */
    public function importStaff(UploadedFile $file, User $admin): array
    {
        $rows = $this->readSheetRows($file);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            if ($this->rowEmpty($row)) {
                continue;
            }

            $nonEmptyCount = count(array_filter($row, fn ($c) => $c !== null && trim((string) $c) !== ''));

            if ($nonEmptyCount >= 6) {
                $data = [
                    'staff_employee_id' => $this->cell($row, 0),
                    'instructor_name' => $this->cell($row, 1),
                    'subject_name' => $this->cell($row, 2),
                    'college' => $this->cell($row, 3),
                    'department' => $this->cell($row, 4),
                    'semester' => $this->cell($row, 5),
                ];
            } else {
                $nameSubject = $this->cell($row, 0);
                $collegeName = $this->cell($row, 1);
                $departmentName = $this->cell($row, 2);
                $semesterName = $this->cell($row, 3);

                $parsed = $this->parseStaffSubjectCell($nameSubject);
                if ($parsed === null) {
                    $errors[] = ['row' => $rowNum, 'messages' => [__('imports.staff_subject_format')]];
                    $skipped++;

                    continue;
                }

                $data = [
                    'staff_employee_id' => $parsed['staff_id'],
                    'instructor_name' => $parsed['name'],
                    'subject_name' => $parsed['subject'],
                    'college' => $collegeName,
                    'department' => $departmentName,
                    'semester' => $semesterName,
                ];
            }

            $validator = Validator::make($data, [
                'staff_employee_id' => ['required', 'string', 'max:64'],
                'instructor_name' => ['required', 'string', 'max:255'],
                'subject_name' => ['required', 'string', 'max:255'],
                'college' => ['required', 'string'],
                'department' => ['required', 'string'],
                'semester' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                $errors[] = ['row' => $rowNum, 'messages' => $validator->errors()->all()];
                $skipped++;

                continue;
            }

            $college = College::query()
                ->where('name_en', $data['college'])
                ->orWhere('name_ku', $data['college'])
                ->orWhere('name_ar', $data['college'])
                ->first();

            $department = null;
            if ($college) {
                $department = Department::query()
                    ->where('college_id', $college->id)
                    ->where(function ($q) use ($data) {
                        $q->where('name_en', $data['department'])
                            ->orWhere('name_ku', $data['department'])
                            ->orWhere('name_ar', $data['department']);
                    })
                    ->first();
            }

            $semester = Semester::query()
                ->where('name_en', $data['semester'])
                ->orWhere('name_ku', $data['semester'])
                ->orWhere('name_ar', $data['semester'])
                ->first();

            if (! $college || ! $department || ! $semester) {
                $errors[] = ['row' => $rowNum, 'messages' => [__('imports.invalid_academic_references')]];
                $skipped++;

                continue;
            }

            $payload = [
                'staff_employee_id' => $data['staff_employee_id'],
                'instructor_name' => $data['instructor_name'],
                'subject_name' => $data['subject_name'],
                'college_id' => $college->id,
                'department_id' => $department->id,
                'semester_id' => $semester->id,
            ];

            $existing = StaffSubject::withTrashed()
                ->where('staff_employee_id', $data['staff_employee_id'])
                ->where('subject_name', $data['subject_name'])
                ->where('college_id', $college->id)
                ->where('department_id', $department->id)
                ->where('semester_id', $semester->id)
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->fill($payload);
                $existing->save();
                $updated++;
            } else {
                StaffSubject::query()->create($payload);
                $created++;
            }
        }

        $this->auditLogger->log($admin, 'import.staff', null, [
            'file' => $file->getClientOriginalName(),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        return compact('created', 'updated', 'skipped', 'errors');
    }

    /**
     * @return list<array<int, mixed>>
     */
    protected function readSheetRows(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        array_shift($rows);

        return array_values(array_filter($rows, fn ($r) => is_array($r)));
    }

    /**
     * @param  array<int, mixed>  $row
     */
    protected function rowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function cell(array $row, int $index): string
    {
        $v = $row[$index] ?? '';

        return trim((string) $v);
    }

    /**
     * Expect "STAFF_ID | Instructor Name | Subject" or "STAFF_ID - Name - Subject" (flexible).
     *
     * @return array{staff_id:string,name:string,subject:string}|null
     */
    protected function parseStaffSubjectCell(string $raw): ?array
    {
        if ($raw === '') {
            return null;
        }
        $parts = preg_split('/\s*[|]\s*|\s+-\s+/', $raw);
        if (! $parts || count($parts) < 3) {
            return null;
        }
        $staffId = trim($parts[0]);
        $name = trim($parts[1]);
        $subject = trim(implode(' - ', array_slice($parts, 2)));

        if ($staffId === '' || $name === '' || $subject === '') {
            return null;
        }

        return [
            'staff_id' => $staffId,
            'name' => $name,
            'subject' => $subject,
        ];
    }

    /**
     * Build .xlsx for student import (row 1 = headers matching importStudents column order).
     */
    public function makeStudentsTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        $headers = [
            'Email',
            'English name',
            'Kurdish name',
            'Arabic name',
            'College',
            'Department',
            'Semester',
        ];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray([[
            'student.example@stud.uoz.edu.krd',
            'Example Student',
            '',
            '',
            'Use exact College name as in system (English, Kurdish, or Arabic)',
            'Use exact Department name',
            'Use exact Semester name',
        ]], null, 'A2');

        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRgb('E0E7FF');
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $info = $spreadsheet->createSheet();
        $info->setTitle('Instructions');
        $info->setCellValue('A1', 'Student import');
        $info->getStyle('A1')->getFont()->setBold(true);
        $info->setCellValue('A2', '• Emails must end with @stud.uoz.edu.krd');
        $info->setCellValue('A3', '• College, Department, and Semester must match existing records exactly (any of EN/KU/AR names).');
        $info->setCellValue('A4', '• Remove the example row (row 2) on the Students sheet before importing, or replace with real data.');
        $info->setCellValue('A5', '• If an email already exists, that student row is updated (names, college, department, semester). Trashed rows are restored.');
        $info->getColumnDimension('A')->setWidth(90);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Six-column staff template (recommended). Matches import when all six columns are filled.
     */
    public function makeStaffTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Staff');

        $headers = [
            'Staff ID',
            'Instructor name',
            'Subject name',
            'College',
            'Department',
            'Semester',
        ];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray([[
            'T001',
            'Dr. Example Instructor',
            'Introduction to Example',
            'Exact college name as in system',
            'Exact department name',
            'Exact semester name',
        ]], null, 'A2');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRgb('E0E7FF');
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $info = $spreadsheet->createSheet();
        $info->setTitle('Instructions');
        $info->setCellValue('A1', 'Staff import');
        $info->getStyle('A1')->getFont()->setBold(true);
        $info->setCellValue('A2', '• Use this 6-column layout (recommended). All columns are required.');
        $info->setCellValue('A3', '• Alternative: 4 columns on one sheet — Col A = "StaffID | Name | Subject", then College, Department, Semester.');
        $info->setCellValue('A4', '• College, Department, Semester must match existing records exactly.');
        $info->setCellValue('A5', '• Remove or replace the example row before importing.');
        $info->setCellValue('A6', '• If the same Staff ID + subject + college + department + semester already exists, the row is updated (e.g. instructor name). Trashed rows are restored.');
        $info->getColumnDimension('A')->setWidth(90);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function writeSpreadsheetToStream(Spreadsheet $spreadsheet, $stream): void
    {
        $writer = new Xlsx($spreadsheet);
        $writer->save($stream);
    }

    /**
     * PhpSpreadsheet needs ext-zip for .xlsx; Apache often uses a different ini than CLI.
     */
    public function supportsExcelFiles(): bool
    {
        return extension_loaded('zip') && class_exists(\ZipArchive::class);
    }

    /**
     * @param  resource  $stream
     */
    public function writeStudentsTemplateCsv($stream): void
    {
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'Email',
            'English name',
            'Kurdish name',
            'Arabic name',
            'College',
            'Department',
            'Semester',
        ]);
        fputcsv($stream, [
            'student.example@stud.uoz.edu.krd',
            'Example Student',
            '',
            '',
            'Use exact College name as in system (English, Kurdish, or Arabic)',
            'Use exact Department name',
            'Use exact Semester name',
        ]);
    }

    /**
     * @param  resource  $stream
     */
    public function writeStaffTemplateCsv($stream): void
    {
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'Staff ID',
            'Instructor name',
            'Subject name',
            'College',
            'Department',
            'Semester',
        ]);
        fputcsv($stream, [
            'T001',
            'Dr. Example Instructor',
            'Introduction to Example',
            'Exact college name as in system',
            'Exact department name',
            'Exact semester name',
        ]);
    }
}
