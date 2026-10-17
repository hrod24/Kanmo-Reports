<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', ['reports' => config('attendance_reports')]);
    }

    public function show(Request $request, string $type): View
    {
        $settings = config("attendance_reports.{$type}");
        abort_unless(is_array($settings), 404);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'max:100'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $columns = $settings['columns'];
        $dateColumn = $settings['date_column'];
        $statusColumn = $settings['status_column'];
        $sort = array_key_exists($validated['sort'] ?? '', $columns) ? $validated['sort'] : 'id';
        $direction = $validated['direction'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 25);
        $search = trim($validated['search'] ?? '');

        $query = DB::table($settings['table']);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('employee_name', 'like', "%{$search}%");
            });
        }
        if (!empty($validated['from'])) {
            $query->whereDate($dateColumn, '>=', $validated['from']);
        }
        if (!empty($validated['to'])) {
            $query->whereDate($dateColumn, '<=', $validated['to']);
        }
        if (!empty($validated['status'])) {
            $query->where($statusColumn, $validated['status']);
        }

        $rows = $query->orderBy($sort, $direction)->orderBy('id')->paginate($perPage)->withQueryString();
        $statuses = DB::table($settings['table'])->whereNotNull($statusColumn)
            ->where($statusColumn, '<>', '')->distinct()->orderBy($statusColumn)
            ->pluck($statusColumn);

        return view('reports.show', compact('type', 'settings', 'rows', 'statuses', 'columns', 'sort', 'direction'));
    }
}
