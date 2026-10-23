<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\AbsentEmployeeReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function index(Request $request, AbsentEmployeeReportService $service): View
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:150'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'string', 'max:100'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);
        $columns = [
            'employee_id' => 'Employee ID', 'employee_name' => 'Employee Name',
            'business_unit' => 'Business Unit', 'department' => 'Department',
            'primary_email' => 'Email', 'date' => 'Absent Date',
        ];
        $sort = array_key_exists($filters['sort'] ?? '', $columns) ? $filters['sort'] : 'employee_name';
        $direction = $filters['direction'] ?? 'asc';
        $today = now('Asia/Jakarta')->startOfDay();
        $date = $filters['date'] ?? DB::table('daily_attendances')
            ->where('date', '<', $today->toDateString())->max('date')
            ?? $today->copy()->subDay()->toDateString();
        $search = trim($filters['search'] ?? '');
        $records = $service->rows(['from' => $date, 'to' => $date, 'search' => $search]);
        $employees = collect();
        foreach ($records->pluck('employee_id')->unique()->chunk(500) as $ids) {
            $employees = $employees->merge(Employee::whereIn('employee_id', $ids)
                ->get(['employee_id', 'primary_email']));
        }
        $employees = $employees->keyBy('employee_id');
        foreach ($records as $row) {
            $row->primary_email = trim((string) $employees->get($row->employee_id)?->primary_email);
        }
        $records = $records->sortBy([[$sort, $direction], ['employee_id', 'asc']])->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $records->forPage($page, 1000)->values();

        foreach ($items as $row) {
            $row->teams_url = null;
            if (filter_var($row->primary_email, FILTER_VALIDATE_EMAIL)) {
                $name = htmlspecialchars($row->employee_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $employeeId = htmlspecialchars($row->employee_id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $message = "Hi {$name} ({$employeeId}),<br><br>"
                    ."Our attendance records show you as absent on {$row->date}. "
                    .'Please check your attendance and submit the appropriate attendance regularization, leave request, or on duty request if needed.'
                    .'<br><br>For guidelines:<br><a href="https://kanmohris.com/ACHO.pdf">https://kanmohris.com/ACHO.pdf</a><br><br>Thank you.';
                $row->teams_url = 'https://teams.microsoft.com/l/chat/0/0?'.http_build_query([
                    'users' => $row->primary_email, 'message' => $message,
                ], '', '&', PHP_QUERY_RFC3986);
            }
        }

        $rows = (new LengthAwarePaginator($items, $records->count(), 1000, $page, [
            'path' => $request->url(),
        ]))->withQueryString();

        return view('reminders.index', compact('rows', 'date', 'search', 'columns', 'sort', 'direction'));
    }
}
