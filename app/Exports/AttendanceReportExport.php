<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AttendanceReportExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    public function __construct(
        private Builder $filteredQuery,
        private array $columns,
    ) {}

    public function query(): Builder
    {
        return $this->filteredQuery->select(array_keys($this->columns));
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($row): array
    {
        return array_map(function ($column) use ($row) {
            $value = $row->{$column} ?? null;
            if ($value === null) return null;
            if (is_bool($value)) return $value ? 'Yes' : 'No';
            $text = (string) $value;
            // Prevent downloaded user-sourced cell content from becoming Excel formulas.
            if (preg_match('/^[\s]*[=+@\-]/u', $text)) return "'" . $text;
            return $text;
        }, array_keys($this->columns));
    }
}
