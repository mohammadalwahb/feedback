<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExcelImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function __construct(
        protected ExcelImportService $excelImportService
    ) {}

    public function studentsForm(): View
    {
        return view('admin.import.students', [
            'excelSupported' => $this->excelImportService->supportsExcelFiles(),
        ]);
    }

    public function downloadStudentsTemplate(): StreamedResponse
    {
        if ($this->excelImportService->supportsExcelFiles()) {
            return response()->streamDownload(function () {
                $spreadsheet = $this->excelImportService->makeStudentsTemplateSpreadsheet();
                $this->excelImportService->writeSpreadsheetToStream($spreadsheet, 'php://output');
            }, 'student-import-template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            $this->excelImportService->writeStudentsTemplateCsv($out);
            fclose($out);
        }, 'student-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function students(Request $request): RedirectResponse
    {
        $request->validate($this->importFileValidationRules());
        $path = $request->file('file');
        $result = $this->excelImportService->importStudents($path, $request->user());

        return redirect()
            ->route('admin.import.students')
            ->with('import_result', $result);
    }

    public function staffForm(): View
    {
        return view('admin.import.staff', [
            'excelSupported' => $this->excelImportService->supportsExcelFiles(),
        ]);
    }

    public function downloadStaffTemplate(): StreamedResponse
    {
        if ($this->excelImportService->supportsExcelFiles()) {
            return response()->streamDownload(function () {
                $spreadsheet = $this->excelImportService->makeStaffTemplateSpreadsheet();
                $this->excelImportService->writeSpreadsheetToStream($spreadsheet, 'php://output');
            }, 'staff-import-template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            $this->excelImportService->writeStaffTemplateCsv($out);
            fclose($out);
        }, 'staff-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function staff(Request $request): RedirectResponse
    {
        $request->validate($this->importFileValidationRules());
        $path = $request->file('file');
        $result = $this->excelImportService->importStaff($path, $request->user());

        return redirect()
            ->route('admin.import.staff')
            ->with('import_result', $result);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function importFileValidationRules(): array
    {
        $mimes = $this->excelImportService->supportsExcelFiles()
            ? 'mimes:xlsx,xls,csv,txt'
            : 'mimes:csv,txt';

        return [
            'file' => ['required', 'file', $mimes, 'max:10240'],
        ];
    }
}
