<?php
namespace App\Imports;

class OnDutyImport extends BaseAttendanceImport
{
    protected function tableName(): string { return 'on_duties'; }
    protected function dateColumns(): array { return ['raised_on', 'raised_for']; }
    protected function columns(): array
    {
        $fields = ['employee_name','employee_id','designation','raised_on','raised_for','request_id','shift_name','on_duty_type','on_duty_start_time','on_duty_end_time','on_duty_period','reason','approval_status','action_taken_by_id','action_taken_by_name'];
        return array_combine($fields, $fields);
    }
    protected function identity(array $data): array { return ['request_id' => $data['request_id'], 'raised_for' => $data['raised_for']]; }
}
