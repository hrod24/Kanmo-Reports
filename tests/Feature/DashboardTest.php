<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'));
    $this->withoutMiddleware();
    $this->withoutVite();
    $this->withViewErrors([]);
    Schema::table('employees', function (Blueprint $table) {
        $table->string('primary_email')->nullable();
    });
    Schema::create('departments', function (Blueprint $table) {
        $table->id();
        $table->string('department_code')->unique();
        $table->string('department_name');
    });
    DB::table('business_units')->insert(['code' => 'BU001', 'name' => 'FINANCE']);
    DB::table('employee_bands')->insert(['code' => 'HO001', 'name' => 'Head Office', 'source' => 'HEAD OFFICE']);
});

function insertDashboardAttendance(string $date, string $employeeId = 'EMP001'): void
{
    DB::table('employees')->insertOrIgnore([
        'employee_id' => $employeeId,
        'display_name' => 'Test Employee',
        'business_unit_code' => 'BU001',
        'employee_level_code' => 'HO001',
        'employment_type_code' => 'ET00001',
    ]);
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

test('absent employees include the related department name and attendance date', function () {
    DB::table('departments')->insert(['department_code' => 'DEP001', 'department_name' => 'Accounting']);
    insertDashboardAttendance('2026-10-20');
    insertDashboardAttendance('2026-10-20', 'EMP002');
    DB::table('employees')->where('employee_id', 'EMP001')->update(['department_code' => 'DEP001']);
    DB::table('daily_attendances')->update(['status' => 'ABSENT']);

    $this->get('/dashboard?date=2026-10-20')->assertOk()->assertViewHas('summary', function ($summary) {
        $employees = collect($summary['business_units'][0]['absent_employees'])->keyBy('employee_id');
        return $employees['EMP001']['department'] === 'Accounting'
            && $employees['EMP001']['date'] === '2026-10-20'
            && $employees['EMP002']['department'] === '-'
            && $summary['absent'] === 2;
    });
});

test('absent report shares dashboard rules and deduplicates employee dates', function () {
    foreach (['ABSENT', 'PUNCH', 'PRESENT', 'OFF', 'LEAVE', 'DUTY', 'REG', 'REJECTED', 'EXCLUDED'] as $id) {
        insertDashboardAttendance('2026-10-20', $id);
    }
    DB::table('daily_attendances')->update(['status' => 'ABSENT']);
    insertDashboardAttendance('2026-10-20', 'ABSENT');
    DB::table('daily_attendances')->where('employee_id', 'ABSENT')->update(['status' => 'ABSENT']);
    DB::table('daily_attendances')->where('employee_id', 'PUNCH')->update(['actual_punch_out' => '17:00:00']);
    DB::table('daily_attendances')->where('employee_id', 'PRESENT')->update(['status' => 'PRESENT']);
    DB::table('daily_attendances')->where('employee_id', 'OFF')->update(['shift_name' => 'Week Off']);
    DB::table('employees')->where('employee_id', 'EXCLUDED')->update(['employment_type_code' => 'OTHER']);
    DB::table('leave_requests')->insert([
        'employee_id' => 'LEAVE', 'employee_name' => 'Test Employee',
        'leave_start_date' => '2026-10-19', 'leave_end_date' => '2026-10-21', 'approval_status' => 'Approved',
    ]);
    DB::table('on_duties')->insert([
        'employee_id' => 'DUTY', 'employee_name' => 'Test Employee', 'raised_for' => '2026-10-20', 'approval_status' => 'Pending',
    ]);
    foreach (['REG' => 'Pending Approval', 'REJECTED' => 'Rejected'] as $id => $status) {
        DB::table('attendance_regularizations')->insert([
            'employee_id' => $id, 'employee_name' => 'Test Employee', 'raised_for' => '2026-10-20', 'approval_status' => $status,
        ]);
    }

    $this->get('/reports')->assertOk()->assertSee('Absent Employee Report');
    $this->get('/reports/absent-employees')->assertOk()->assertViewHas('rows', function ($rows) {
        return $rows->total() === 2 && collect($rows->items())->pluck('employee_id')->sort()->values()->all() === ['ABSENT', 'REJECTED'];
    });
});

test('absent report supports date search sorting pagination ajax and export', function () {
    for ($i = 1; $i <= 12; $i++) {
        insertDashboardAttendance('2026-10-20', sprintf('EMP%03d', $i));
    }
    insertDashboardAttendance('2026-10-21');
    DB::table('daily_attendances')->update(['status' => 'ABSENT']);
    $this->get('/reports/absent-employees?from=2026-10-20&to=2026-10-20&sort=employee_id&direction=asc&per_page=10&page=2')
        ->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 12 && $rows->first()->employee_id === 'EMP011');
    $this->getJson('/reports/absent-employees?search=EMP001&from=2026-10-21&to=2026-10-21')
        ->assertOk()->assertJsonPath('total', 1)->assertSee('2026-10-21');
    $this->get('/reports/absent-employees?search=missing')->assertOk()
        ->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
    $this->getJson('/reports/absent-employees?from=2026-10-21&to=2026-10-20')->assertUnprocessable();

    \Maatwebsite\Excel\Facades\Excel::fake();
    $this->get('/reports/absent-employees/export?search=EMP001&from=2026-10-21')->assertOk();
    \Maatwebsite\Excel\Facades\Excel::assertDownloaded('absent-employees-20261022-090000.xlsx', function ($export) {
        return $export->collection()->count() === 1
            && $export->collection()->first()->date === '2026-10-21'
            && $export->headings() === ['Employee ID', 'Employee Name', 'Business Unit', 'Department', 'Absent Date'];
    });
});

test('reminders use the selected absent date and employee primary email for Teams drafts', function () {
    insertDashboardAttendance('2026-10-20');
    insertDashboardAttendance('2026-10-20', 'EMP002');
    insertDashboardAttendance('2026-10-20', 'EMP003');
    insertDashboardAttendance('2026-10-21');
    DB::table('daily_attendances')->where('date', '2026-10-20')->update(['status' => 'ABSENT']);
    DB::table('employees')->where('employee_id', 'EMP001')->update(['primary_email' => 'test+employee@example.com']);
    DB::table('employees')->where('employee_id', 'EMP003')->update(['primary_email' => 'invalid-email']);

    $this->get('/reminders?date=2026-10-20')->assertOk()->assertViewHas('rows', function ($rows) {
        $employees = collect($rows->items())->keyBy('employee_id');
        parse_str(parse_url($employees['EMP001']->teams_url, PHP_URL_QUERY), $parameters);
        return $rows->total() === 3
            && $parameters['users'] === 'test+employee@example.com'
            && str_contains($parameters['message'], '2026-10-20')
            && str_contains($parameters['message'], 'EMP001')
            && $employees['EMP002']->teams_url === null
            && $employees['EMP003']->teams_url === null;
    })->assertSee('data-teams-reminder', false)->assertSee('Email not available');

    $this->get('/reminders')->assertOk()->assertViewHas('date', '2026-10-21')
        ->assertViewHas('rows', fn ($rows) => $rows->total() === 0);
    $this->get('/reminders?date=2026-10-20&search=EMP002')->assertOk()
        ->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->employee_id === 'EMP002');
    $this->getJson('/reminders?date=invalid')->assertUnprocessable();
});

test('reminder pagination preserves the selected date', function () {
    for ($i = 1; $i <= 26; $i++) {
        insertDashboardAttendance('2026-10-20', sprintf('EMP%03d', $i));
    }
    DB::table('daily_attendances')->update(['status' => 'ABSENT']);
    $this->get('/reminders?date=2026-10-20&page=2')->assertOk()
        ->assertViewHas('rows', fn ($rows) => $rows->total() === 26 && $rows->count() === 1 && $rows->firstItem() === 26)
        ->assertSee('date=2026-10-20', false);
});
