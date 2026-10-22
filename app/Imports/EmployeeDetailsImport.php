<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeeDetailsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(protected string $sourceFile) {}

    public function headingRow(): int { return 1; }
    public function chunkSize(): int { return 200; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $raw = [];
            foreach ($row->toArray() as $key => $value) {
                $key = strtolower(trim((string) $key));
                $key = trim((string) preg_replace('/[^a-z0-9]+/', '_', $key), '_');
                $raw[$key] = $value;
            }

            $employeeId = $this->clean($raw['employee_id'] ?? null);
            if ($employeeId === null) continue;

            $values = [
                'display_name' => $this->clean($raw['display_name'] ?? null),
                'business_unit_code' => $this->clean($raw['business_unit_org_element_1'] ?? null),
                'department_code' => $this->clean($raw['department_org_element_2'] ?? null),
                'employee_level_code' => $this->clean($raw['employee_level_code'] ?? null),
                'employment_type_code' => $this->clean($raw['employment_type_code'] ?? null),
            ];
            // Do not overwrite valid existing master data with blank fields.
            $values = array_filter($values, fn ($v) => $v !== null);
            $now = now();
            $existing = DB::table('employees')->where('employee_id', $employeeId);
            if (!$existing->exists()) {
                DB::table('employees')->insert(array_merge(
                    ['employee_id' => $employeeId, 'created_at' => $now, 'updated_at' => $now],
                    $values
                ));
            } elseif ($values !== []) {
                $existing->update(array_merge($values, ['updated_at' => $now]));
            }
        }
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        if ($value === '' || $value === '-' || str_starts_with($value, '=')) return null;
        return $value;
    }
}
