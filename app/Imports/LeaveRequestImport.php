<?php
namespace App\Imports;

class LeaveRequestImport extends BaseAttendanceImport
{
    public function headingRow(): int { return 1; }
    protected function tableName(): string { return 'leave_requests'; }
    protected function dateColumns(): array { return ['leave_start_date', 'leave_end_date']; }
    protected function dateTimeColumns(): array { return ['raised_on']; }
    protected function columns(): array
    {
        $fields = ['employee_id','employee_name','leave_start_date','leave_end_date','leave_type','leave_type_status','duration','leave_credits','approval_status','raised_on','leave_reason','final_approval'];
        return array_combine($fields, $fields);
    }
    protected function identity(array $data): array
    {
        return ['source_key' => $this->fingerprint($data, ['employee_id','leave_start_date','leave_end_date','leave_type','raised_on', 'leave_credits'])];
    }
}
