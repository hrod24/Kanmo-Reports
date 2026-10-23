<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceDashboardService
{
    public function summarize(string $date): array
    {
        $date = Carbon::parse($date)->toDateString();

        // Only employees present in Daily Attendance on the selected date are scheduled.

        $allowedBusinessUnits = [
            'BUSINESS DEVELOPMENT',
            'CREATIVE & EXPERIENCE EXECUTION',
            'DIGITAL, OMNICHANNEL & INTELLIGENT TECHNOLOGY',
            'FASHION & ACCESSORIES',
            'FASHION & ACCESSORIES 2',
            'FINANCE',
            'FOOTWEAR & ACTIVE',
            'HUMAN RESOURCES',
            'LIFESTYLE',
            'PROJECT & MAINTENANCE',
            'SHIPPING',
            'THE COACH RESTAURANT',
            'WAREHOUSE',
        ];

        $allowedEmploymentTypes = [
            'ET00001',
            'ET00002',
            'ET00003',
            'ET00004',
            'ET00005',
        ];

        $daily = DB::table('daily_attendances as da')
            // Employee wajib terdaftar di master.
            ->join(
                'employees as e',
                'da.employee_id',
                '=',
                'e.employee_id'
            )

            // Employee Level harus termasuk Head Office.
            ->join(
                'employee_bands as eb',
                'e.employee_level_code',
                '=',
                'eb.code'
            )

            // Business Unit harus memiliki mapping.
            ->join(
                'business_units as bu',
                'e.business_unit_code',
                '=',
                'bu.code'
            )
            ->where('e.employee_id', '!=', 'ADMINAKV')

            // Pilih tanggal attendance.
            ->where('da.date', $date)

            // Hanya Head Office.
            ->where('eb.source', 'HEAD OFFICE')

            // Hanya 13 Business Unit terpilih.
            ->whereIn('bu.name', $allowedBusinessUnits)

            // Hanya 5 Employment Type.
            ->whereIn(
                'e.employment_type_code',
                $allowedEmploymentTypes
            )

            ->select(
                'da.employee_id',
                'e.display_name',
                'da.date',
                'da.status',
                'da.shift_name',
                'da.actual_punch_in',
                'da.actual_punch_out',
                'bu.name as business_unit'
            )
            ->get()
            ->filter(
                fn($row) => $this->id($row->employee_id) !== ''
            )
            ->groupBy(
                fn($row) => $this->id($row->employee_id)
            );


        $ids = $daily->keys()->all();
        $scheduled = count($ids);
        if ($scheduled === 0) {
            return $this->result($date, 0, 0, 0, 0, 0, []);
        }

        // Limit IN clause size. Requests are evidence only, never additional scheduled employees.
        $leaveIds = [];
        $dutyIds = [];
        $wfhIds = [];
        $regularizationIds = [];

        foreach (array_chunk($ids, 500) as $chunk) {
            $leaveRows = DB::table('leave_requests')
                ->whereIn('employee_id', $chunk)
                ->where('leave_start_date', '<=', $date)
                ->where('leave_end_date', '>=', $date)
                ->select('employee_id', 'approval_status')->get();
            foreach ($leaveRows as $row) {
                if ($this->qualifies($row->approval_status)) {
                    $leaveIds[$this->id($row->employee_id)] = true;
                }
            }

            $dutyRows = DB::table('on_duties')
                ->whereIn('employee_id', $chunk)
                ->where('raised_for', $date)
                ->select('employee_id', 'approval_status', 'on_duty_type')->get();
            foreach ($dutyRows as $row) {
                if ($this->qualifies($row->approval_status)) {
                    $id = $this->id($row->employee_id);
                    $dutyIds[$id] = true;
                    if (Str::upper(trim((string) $row->on_duty_type)) === 'WFH') {
                        $wfhIds[$id] = true;
                    }
                }
            }

            $regularizationRows = DB::table('attendance_regularizations')
                ->whereIn('employee_id', $chunk)
                ->where('raised_for', $date)
                ->select('employee_id', 'approval_status')->get();
            foreach ($regularizationRows as $row) {
                if ($this->qualifies($row->approval_status)) {
                    $regularizationIds[$this->id($row->employee_id)] = true;
                }
            }
        }

        $present = 0;
        $absent = 0;
        $onLeave = 0;
        $wfh = 0;
        $businessUnits = [];

        foreach ($daily as $employeeId => $attendanceRows) {
            $hasPunchOrPresentStatus = $attendanceRows->contains(
                fn($row) =>
                $this->hasValue($row->actual_punch_in)
                    || $this->hasValue($row->actual_punch_out)
                    || $this->isPresentStatus($row->status)
                    || Str::lower(trim((string) $row->shift_name)) === 'week off'
            );

            $hasLeave = isset($leaveIds[$employeeId]);
            $hasDuty = isset($dutyIds[$employeeId]);
            $hasRegularization = isset($regularizationIds[$employeeId]);
            $isPresent = $hasPunchOrPresentStatus || $hasLeave || $hasDuty || $hasRegularization;

            // One employee = one BU on this date. Conflicting BU data should be fixed at source.
            $unit = $attendanceRows
                ->pluck('business_unit')
                ->map(fn($value) => trim((string) $value))
                ->first(fn($value) => $value !== '' && $value !== '-' && !str_starts_with($value, '=') && !str_starts_with($value, '#'));
            $unit = $unit ? Str::upper($unit) : 'UNMAPPED';

            if (!isset($businessUnits[$unit])) {
                $businessUnits[$unit] = [
                    'name' => $unit,
                    'scheduled' => 0,
                    'present' => 0,
                    'absent' => 0,
                    'on_leave' => 0,
                    'wfh' => 0,
                    'absent_employees' => [],
                ];
            }
            $businessUnits[$unit]['scheduled']++;
            if ($isPresent) {
                ++$present;
                ++$businessUnits[$unit]['present'];
            } else {
                ++$absent;
                ++$businessUnits[$unit]['absent'];

                $employee = $attendanceRows->first();

                $businessUnits[$unit]['absent_employees'][] = [
                    'employee_id' => $employeeId,
                    'display_name' => $employee->display_name ?: '-',
                    'date' => $employee->date,
                ];
            }

            if ($hasLeave) {
                $onLeave++;
                $businessUnits[$unit]['on_leave']++;
            }
            if (isset($wfhIds[$employeeId])) {
                $wfh++;
                $businessUnits[$unit]['wfh']++;
            }
        }

        $absentIds = collect($businessUnits)->pluck('absent_employees')->flatten(1)->pluck('employee_id');
        $departments = collect();
        foreach ($absentIds->chunk(500) as $chunk) {
            $departments = $departments->merge(
                Employee::with('department')->whereIn('employee_id', $chunk)->get()
            );
        }
        $departments = $departments->keyBy('employee_id');

        $units = array_values(array_map(function ($unit) use ($departments) {
            $unit['absent_employees'] = array_map(function ($employee) use ($departments) {
                $employee['department'] = $departments->get($employee['employee_id'])?->department?->department_name ?: '-';
                return $employee;
            }, $unit['absent_employees']);
            $unit['percentage'] = round(100 * $unit['present'] / $unit['scheduled'], 1);
            return $unit;
        }, $businessUnits));

        // Highest attendance first, then alphabetical. UNMAPPED always at bottom.
        usort(
            $units,
            fn($a, $b) => ($a['name'] === 'UNMAPPED' ? 1 : 0) <=> ($b['name'] === 'UNMAPPED' ? 1 : 0)
                ?: ($b['percentage'] <=> $a['percentage'])
                ?: strcmp($a['name'], $b['name'])
        );

        return $this->result($date, $scheduled, $present, $absent, $onLeave, $wfh, $units);
    }

    private function qualifies(?string $status): bool
    {
        return in_array(Str::lower(trim((string) $status)), [
            'pending',
            'pending approval',
            'approved',
        ], true);
    }

    private function isPresentStatus(?string $status): bool
    {
        return Str::upper(trim((string) $status)) === 'PRESENT';
    }

    private function hasValue(mixed $value): bool
    {
        $value = trim((string) $value);
        return $value !== '' && $value !== '-' && $value !== '00:00:00';
    }

    private function id(mixed $value): string
    {
        return trim((string) $value);
    }

    private function result(string $date, int $scheduled, int $present, int $absent, int $onLeave, int $wfh, array $units): array
    {
        return [
            'date' => $date,
            'scheduled' => $scheduled,
            'present' => $present,
            'absent' => $absent,
            'on_leave' => $onLeave,
            'wfh' => $wfh,
            'overall' => $scheduled > 0 ? round($present * 100 / $scheduled, 1) : 0.0,
            'business_units' => $units,
            'has_data' => $scheduled > 0,
        ];
    }
}
