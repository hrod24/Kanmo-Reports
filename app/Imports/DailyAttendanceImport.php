<?php

namespace App\Imports;

class DailyAttendanceImport extends BaseAttendanceImport
{
    protected function tableName(): string
    {
        return 'daily_attendances';
    }
    protected function dateColumns(): array
    {
        return ['date'];
    }
    protected function columns(): array
    {
        $fields = [
            'employee_name',
            'employee_id',
            'designation',
            'date',
            'shift_name',
            'scheduled_in_time',
            'scheduled_out_time',
            'actual_punch_in',
            'actual_punch_out',
            'duration',
            'duration_calculation_method',
            'late_in_by',
            'early_out_by',
            'overtime_worked',
            'overtime_eligible',
            'overtime_type',
            'status',
            'payment_status',
        ];

        $mapping = array_combine($fields, $fields);

        // Nama kolom database => Header Excel
        $mapping['business_unit'] = 'business_unit';

        return $mapping;
    }
    protected function identity(array $data): array
    {
        return ['employee_id' => $data['employee_id'], 'date' => $data['date']];
    }
}
