<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'));
    $this->withoutMiddleware();
    $this->withoutVite();
});

function insertDashboardAttendance(string $date, string $employeeId = 'EMP001'): void
{
    DB::table('daily_attendances')->insert([
        'employee_name' => 'Test Employee',
        'employee_id' => $employeeId,
        'date' => $date,
        'status' => 'PRESENT',
        'business_unit' => 'TEST',
        'created_at' => '2026-10-20 09:00:00',
        'updated_at' => '2026-10-20 09:00:00',
    ]);
}

test('dashboard keeps showing older attendance until yesterday becomes available', function () {
    insertDashboardAttendance('2026-10-20');

    $this->get('/dashboard')->assertOk()->assertViewHas('summary', fn ($summary) =>
        $summary['date'] === '2026-10-20' && $summary['present'] === 1 && $summary['has_data']
    );

    insertDashboardAttendance('2026-10-21');
    insertDashboardAttendance('2026-10-21', 'EMP002');
    insertDashboardAttendance('2026-10-22');
    insertDashboardAttendance('2026-10-23');

    $this->get('/dashboard')->assertOk()->assertViewHas('summary', fn ($summary) =>
        $summary['date'] === '2026-10-21' && $summary['present'] === 2
    );

    $this->travelTo(\Carbon\Carbon::parse('2026-10-27 09:00:00', 'Asia/Jakarta'));

    $this->get('/dashboard')->assertOk()->assertViewHas('summary', fn ($summary) =>
        $summary['date'] === '2026-10-23' && $summary['has_data']
    );
});

test('dashboard respects an explicitly selected date', function () {
    insertDashboardAttendance('2026-10-20');
    insertDashboardAttendance('2026-10-21');

    $this->get('/dashboard?date=2026-10-20')->assertOk()->assertViewHas('summary', fn ($summary) =>
        $summary['date'] === '2026-10-20' && $summary['has_data']
    );
});

test('dashboard handles missing attendance data', function () {
    $this->get('/dashboard')->assertOk()->assertViewHas('summary', fn ($summary) =>
        $summary['date'] === '2026-10-21' && ! $summary['has_data'] && $summary['scheduled'] === 0
    );
});
