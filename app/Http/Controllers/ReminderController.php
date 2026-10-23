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
        ]);
        $today = now('Asia/Jakarta')->startOfDay();
        $date = $filters['date'] ?? DB::table('daily_attendances')
            ->where('date', '<', $today->toDateString())->max('date')
            ?? $today->copy()->subDay()->toDateString();
        $search = trim($filters['search'] ?? '');
        $records = $service->rows(['from' => $date, 'to' => $date, 'search' => $search])
            ->sortBy('employee_name')->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $records->forPage($page, 25)->values();
        $employees = Employee::whereIn('employee_id', $items->pluck('employee_id'))
            ->get(['employee_id', 'primary_email'])->keyBy('employee_id');

        foreach ($items as $row) {
            $row->primary_email = trim((string) $employees->get($row->employee_id)?->primary_email);
            $row->teams_url = null;
            if (filter_var($row->primary_email, FILTER_VALIDATE_EMAIL)) {
                $message = "Hi {$row->employee_name} ({$row->employee_id}),\n\n"
                    ."Our attendance records show you as absent on {$row->date}. "
                    ."Please check your attendance and submit the appropriate attendance regularization, leave request, or on duty request if needed.\n\nThank you.";
                $row->teams_url = 'https://teams.microsoft.com/l/chat/0/0?'.http_build_query([
                    'users' => $row->primary_email, 'message' => $message,
                ], '', '&', PHP_QUERY_RFC3986);
            }
        }

        $rows = (new LengthAwarePaginator($items, $records->count(), 25, $page, [
            'path' => $request->url(),
        ]))->withQueryString();

        return view('reminders.index', compact('rows', 'date', 'search'));
    }
}
