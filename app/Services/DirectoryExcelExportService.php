<?php

namespace App\Services;

use App\Models\College;
use App\Models\Department;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectoryExcelExportService
{
    public function streamCollegesWorkbook(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            __('fields.id'),
            __('fields.name_en'),
            __('fields.name_ku'),
            __('fields.name_ar'),
        ];
        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).'1', $header);
            $colIndex++;
        }

        $r = 2;
        foreach (College::query()->orderBy('name_en')->get() as $row) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(1).(string) $r, $row->id);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(2).(string) $r, $row->name_en);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(3).(string) $r, $row->name_ku ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(4).(string) $r, $row->name_ar ?? '');
            $r++;
        }

        return $this->streamXlsx($spreadsheet, 'colleges');
    }

    public function streamDepartmentsWorkbook(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            __('fields.id'),
            __('fields.college_name_en'),
            __('fields.college_name_ku'),
            __('fields.college_name_ar'),
            __('fields.name_en'),
            __('fields.name_ku'),
            __('fields.name_ar'),
        ];
        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex).'1', $header);
            $colIndex++;
        }

        $r = 2;
        foreach (Department::query()->with('college')->orderBy('name_en')->get() as $row) {
            $c = $row->college;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(1).(string) $r, $row->id);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(2).(string) $r, $c?->name_en ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(3).(string) $r, $c?->name_ku ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(4).(string) $r, $c?->name_ar ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(5).(string) $r, $row->name_en);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(6).(string) $r, $row->name_ku ?? '');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(7).(string) $r, $row->name_ar ?? '');
            $r++;
        }

        return $this->streamXlsx($spreadsheet, 'departments');
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
