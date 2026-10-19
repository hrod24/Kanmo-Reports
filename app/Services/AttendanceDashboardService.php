<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceDashboardService
{
    /**
     * Daily attendance defines who is scheduled for the selected date.
     * One employee is counted once even if source files contain duplicate rows.
     * Requests are supporting evidence, not additional scheduled employees.
     */
    public function summarize(string $date): array
    {
        $date = Carbon::parse($date)->toDateString();

        $daily = DB::table('daily_attendances')
            ->whereDate('date', $date)
            ->select('employee_id', 'employee_name', 'status', 'actual_punch_in', 'actual_punch_out')
            ->get()
            ->filter(fn ($r) => $this->id($r->employee_id) !== '')
            ->groupBy(fn ($r) => $this->id($r->employee_id));

        $ids = $daily->keys()->all();
        $scheduled = count($ids);

        if ($scheduled === 0) {
            return $this->result($date, 0, 0, 0, 0, 0);
        }

        // Employee ID is the join key; avoid matching by name.
        $leaves = DB::table('leave_requests')
            ->whereIn('employee_id', $ids)
            ->whereDate('leave_start_date', '<=', $date)
            ->whereDate('leave_end_date', '>=', $date)
            ->select('employee_id', 'approval_status')
            ->get()
            ->filter(fn ($r) => $this->qualifies($r->approval_status))
            ->groupBy(fn ($r) => $this->id($r->employee_id));

        $duties = DB::table('on_duties')
            ->whereIn('employee_id', $ids)
            ->whereDate('raised_for', $date)
            ->select('employee_id', 'approval_status', 'on_duty_type')
            ->get()
            ->filter(fn ($r) => $this->qualifies($r->approval_status))
            ->groupBy(fn ($r) => $this->id($r->employee_id));

        $regularizations = DB::table('attendance_regularizations')
            ->whereIn('employee_id', $ids)
            ->whereDate('raised_for', $date)
            ->select('employee_id', 'approval_status')
            ->get()
            ->filter(fn ($r) => $this->qualifies($r->approval_status))
            ->groupBy(fn ($r) => $this->id($r->employee_id));

        $present = 0;
        $absent = 0;
        $onLeave = 0;
        $wfh = 0;

        foreach ($daily as $employeeId => $attendanceRows) {
            $hasPunchOrPresentStatus = $attendanceRows->contains(function ($row) {
                return $this->hasValue($row->actual_punch_in)
                    || $this->hasValue($row->actual_punch_out)
                    || $this->isPresentStatus($row->status);
            });

            $hasLeave = $leaves->has($employeeId);
            $hasDuty = $duties->has($employeeId);
            $hasRegularization = $regularizations->has($employeeId);

            $isPresent = $hasPunchOrPresentStatus || $hasLeave || $hasDuty || $hasRegularization;

            if ($isPresent) {
                $present++;
            } else {
                $absent++;
            }

            // On Leave and WFH are overlapping descriptive subsets of Present.
            if ($hasLeave) {
                $onLeave++;
            }

            if ($hasDuty && $duties->get($employeeId)->contains(
                fn ($r) => Str::upper(trim((string) $r->on_duty_type)) === 'WFH'
            )) {
                $wfh++;
            }
        }

        return $this->result($date, $scheduled, $present, $absent, $onLeave, $wfh);
    }

    private function qualifies(?string $status): bool
    {
        $status = Str::lower(trim((string) $status));

        // Deliberately whitelist; rejected/cancelled/unknown statuses are excluded.
        return in_array($status, ['pending', 'pending approval', 'approved'], true);
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

    private function result(string $date, int $scheduled, int $present, int $absent, int $onLeave, int $wfh): array
    {
        return [
            'date' => $date,
            'scheduled' => $scheduled,
            'present' => $present,
            'absent' => $absent,
            'on_leave' => $onLeave,
            'wfh' => $wfh,
            'overall' => $scheduled > 0 ? round($present * 100 / $scheduled, 1) : 0.0,
        ];
    }
}
