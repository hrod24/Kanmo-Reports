<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AbsentEmployeeReportService
{
    public function __construct(private AttendanceDashboardService $dashboard) {}

    public function rows(array $filters): Collection
    {
        $dates = DB::table('daily_attendances')->select('date')->distinct()->orderBy('date');
        if (! empty($filters['from'])) {
            $dates->where('date', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $dates->where('date', '<=', $filters['to']);
        }

        $rows = collect();
        $search = mb_strtolower(trim($filters['search'] ?? ''));
        foreach ($dates->pluck('date') as $date) {
            foreach ($this->dashboard->summarize($date)['business_units'] as $unit) {
                foreach ($unit['absent_employees'] as $employee) {
                    if ($search !== '' && ! str_contains(mb_strtolower($employee['employee_id']), $search)
                        && ! str_contains(mb_strtolower($employee['display_name']), $search)) {
                        continue;
                    }
                    $rows->push((object) [
                        'employee_id' => $employee['employee_id'],
                        'employee_name' => $employee['display_name'],
                        'business_unit' => $unit['name'],
                        'department' => $employee['department'],
                        'date' => $employee['date'],
                    ]);
                }
            }
        }

        return $rows;
    }
}
