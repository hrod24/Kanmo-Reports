<?php

namespace App\Services;

use App\Imports\AttendanceRegularizationImport;
use App\Imports\DailyAttendanceImport;
use App\Imports\LeaveRequestImport;
use App\Imports\OnDutyImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use RuntimeException;

class AttendanceBatchProcessor
{
    public const BATCH_ROWS = 200;

    public const TYPES = [
        'daily_attendance' => ['label' => 'Daily Attendance', 'import' => DailyAttendanceImport::class, 'heading' => 6],
        'attendance_regularization' => ['label' => 'Attendance Regularization', 'import' => AttendanceRegularizationImport::class, 'heading' => 6],
        'on_duty' => ['label' => 'On Duty', 'import' => OnDutyImport::class, 'heading' => 6],
        'leave_requests' => ['label' => 'Leave Requests', 'import' => LeaveRequestImport::class, 'heading' => 1],
    ];

    public function totalRows(
        string $absolutePath,
        int $headerRow
    ): int {

        $extension = strtolower(
            pathinfo($absolutePath, PATHINFO_EXTENSION)
        );

        $reader = match ($extension) {
            'xlsx' => new Xlsx(),
            'xls' => new Xls(),
            default => throw new RuntimeException(
                'Unsupported Excel format.'
            ),
        };

        $sheets = $reader->listWorksheetInfo($absolutePath);

        if (empty($sheets)) {
            throw new RuntimeException(
                'Excel does not contain a worksheet.'
            );
        }

        return max(
            0,
            (int) $sheets[0]['totalRows'] - $headerRow
        );
    }

    public function process(string $absolutePath, string $type, string $originalFilename, int $nextRow, int $totalRows): int
    {
        if (!isset(self::TYPES[$type])) {
            throw new RuntimeException('Unknown report type.');
        }
        $headerRow = self::TYPES[$type]['heading'];
        $lastRow = $headerRow + $totalRows;
        $endRow = min($nextRow + self::BATCH_ROWS - 1, $lastRow);
        if ($nextRow > $endRow) return 0;

        $filter = new class($headerRow, $nextRow, $endRow) implements IReadFilter {
            public function __construct(private int $heading, private int $start, private int $end) {}
            public function readCell($columnAddress, $row, $worksheetName = ''): bool
            {
                return $row === $this->heading || ($row >= $this->start && $row <= $this->end);
            }
        };

        $reader = IOFactory::createReaderForFile($absolutePath);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter($filter);
        // Each HTTP request loads only the header row and this row range into memory.
        $spreadsheet = $reader->load($absolutePath);
        try {
            $sheet = $spreadsheet->getSheet(0);
            $lastColumn = $sheet->getHighestDataColumn();
            $headerCells = $sheet->rangeToArray("A{$headerRow}:{$lastColumn}{$headerRow}", null, false, false)[0] ?? [];
            $headings = [];
            foreach ($headerCells as $i => $header) {
                $normalized = strtolower(trim((string) $header));
                $normalized = trim((string) preg_replace('/[^a-z0-9]+/', '_', $normalized), '_');
                $headings[$i] = $normalized;
            }
            if (!in_array('employee_id', $headings, true) || !in_array('employee_name', $headings, true)) {
                throw new RuntimeException('Header Employee ID / Employee Name tidak ditemukan. Pastikan jenis file sesuai kotak upload.');
            }
            $rows = [];
            for ($rowNumber = $nextRow; $rowNumber <= $endRow; $rowNumber++) {
                $values = $sheet->rangeToArray("A{$rowNumber}:{$lastColumn}{$rowNumber}", null, false, false)[0] ?? [];
                $record = [];
                foreach ($headings as $index => $heading) {
                    if ($heading !== '') $record[$heading] = $values[$index] ?? null;
                }
                $rows[] = collect($record);
            }
            // Keep the existing four import mappings and upsert identity rules unchanged.
            $importClass = self::TYPES[$type]['import'];
            (new $importClass($originalFilename))->collection(collect($rows));
            return $endRow - $nextRow + 1;
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }
}
