<?php
namespace App\Imports;

class AttendanceRegularizationImport extends BaseAttendanceImport
{
    protected function tableName(): string { return 'attendance_regularizations'; }
    protected function dateColumns(): array { return ['raised_on', 'raised_for']; }
    protected function columns(): array
    {
        $fields = ['employee_name','employee_id','designation','raised_on','raised_for','assigned_shift_name','assigned_in_time','assigned_out_time','assigned_shift_duration','actual_shift_name','actual_in_time','actual_out_time','actual_duration','regularized_shift_name','regularized_in_time','regularized_out_time','regularized_duration','reason','approval_status','action_taken_by_id','action_taken_by_name'];
        return array_combine($fields, $fields);
    }
    protected function identity(array $data): array
    {
        return ['source_key' => $this->fingerprint($data, ['employee_id','raised_on','raised_for','regularized_shift_name','regularized_in_time','regularized_out_time','reason'])];
    }
}
