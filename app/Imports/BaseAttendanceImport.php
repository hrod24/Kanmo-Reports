<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

abstract class BaseAttendanceImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(protected string $sourceFile) {}

    abstract protected function tableName(): string;
    abstract protected function columns(): array;
    abstract protected function identity(array $data): array;

    protected function dateColumns(): array { return []; }
    protected function dateTimeColumns(): array { return []; }
    public function headingRow(): int { return 6; }
    public function chunkSize(): int { return 300; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $raw = [];
            foreach ($row->toArray() as $key => $value) {
                // Handles header variants such as tab-prefixed 'Approval Status'.
                $key = strtolower(trim((string) $key));
                $key = preg_replace('/[^a-z0-9]+/', '_', $key);
                $raw[trim($key, '_')] = $value;
            }

            $data = [];
            foreach ($this->columns() as $column => $heading) {
                $value = $raw[$heading] ?? null;
                if (in_array($column, $this->dateColumns(), true)) {
                    $data[$column] = $this->asDate($value);
                } elseif (in_array($column, $this->dateTimeColumns(), true)) {
                    $data[$column] = $this->asDate($value, true);
                } else {
                    $data[$column] = $this->clean($value);
                }
            }

            if (empty($data['employee_id']) || empty($data['employee_name'])) {
                continue;
            }

            $key = $this->identity($data);
            if (!$key || in_array(null, array_values($key), true)) {
                continue;
            }

            $now = now();
            $data['source_file'] = $this->sourceFile;
            $data['updated_at'] = $now;
            $query = DB::table($this->tableName())->where($key);
            if ($query->exists()) {
                $query->update($data);
            } else {
                DB::table($this->tableName())->insert(array_merge($data, $key, [
                    'created_at' => $now,
                ]));
            }
        }
    }

    protected function clean(mixed $value): ?string
    {
        if ($value === null) return null;
        $text = trim((string) $value);
        return $text === '' || $text === '-' ? null : $text;
    }

    protected function asDate(mixed $value, bool $withTime = false): ?string
    {
        $value = $this->clean($value);
        if ($value === null) return null;
        try {
            $date = is_numeric($value)
                ? Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))
                : Carbon::parse($value);
            return $date->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
        } catch (Throwable $e) {
            throw new \RuntimeException("Invalid date '{$value}' in {$this->sourceFile}", 0, $e);
        }
    }

    protected function fingerprint(array $data, array $fields): string
    {
        return hash('sha256', json_encode(array_map(fn ($field) => $data[$field] ?? null, $fields), JSON_UNESCAPED_UNICODE));
    }
}
