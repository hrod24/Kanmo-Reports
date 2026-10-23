<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AbsentEmployeeReportExport implements FromCollection, WithHeadings, WithMapping, WithStrictNullComparison
{
    public function __construct(private Collection $rows, private array $columns) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function map($row): array
    {
        return array_map(function ($column) use ($row) {
            $value = $row->{$column} ?? null;
            if ($value === null) {
                return null;
            }
            $text = (string) $value;

            return preg_match('/^[\s]*[=+@\-]/u', $text) ? "'".$text : $text;
        }, array_keys($this->columns));
    }
}
