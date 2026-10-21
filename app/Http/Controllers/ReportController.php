<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceReportExport;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', ['reports' => config('attendance_reports')]);
    }

    public function show(Request $request, string $type): View|JsonResponse
    {
        [$settings, $filters, $sort, $direction, $perPage] = $this->options($request, $type);

        $query = $this->filteredQuery($settings, $filters);
        $rows = $query->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $columns = $settings['columns'];
        $statuses = DB::table($settings['table'])
            ->whereNotNull($settings['status_column'])
            ->where($settings['status_column'], '<>', '')
            ->distinct()->orderBy($settings['status_column'])
            ->pluck($settings['status_column']);

        $data = compact('type', 'settings', 'rows', 'statuses', 'columns', 'sort', 'direction');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('reports.partials.table', $data)->render(),
                'total' => $rows->total(),
            ]);
        }

        return view('reports.show', $data);
    }

    public function export(Request $request, string $type): BinaryFileResponse
    {
        [$settings, $filters, $sort, $direction] = $this->options($request, $type);

        $query = $this->filteredQuery($settings, $filters)
            ->orderBy($sort, $direction)->orderBy('id', 'asc');

        $filename = $type . '-' . now('Asia/Jakarta')->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new AttendanceReportExport($query, $settings['columns']),
            $filename
        );
    }

    private function options(Request $request, string $type): array
    {
        // Only four known server-side configurations can select tables / columns.
        $reports = config('attendance_reports', []);
        abort_unless(array_key_exists($type, $reports), 404);
        $settings = $reports[$type];

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'max:100'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $sort = array_key_exists($filters['sort'] ?? '', $settings['columns'])
            ? $filters['sort'] : 'id';
        $direction = $filters['direction'] ?? 'desc';
        $perPage = (int) ($filters['per_page'] ?? 25);

        return [$settings, $filters, $sort, $direction, $perPage];
    }

    private function filteredQuery(array $settings, array $filters): Builder
    {
        $query = DB::table($settings['table']);
        $search = trim($filters['search'] ?? '');

        if ($search !== '') {
            // Escape LIKE wildcards so user input is treated as literal text.
            $escaped = addcslashes($search, '%_\\');
            $query->where(function (Builder $q) use ($escaped): void {
                $q->where('employee_id', 'like', '%' . $escaped . '%')
                    ->orWhere('employee_name', 'like', '%' . $escaped . '%');
            });
        }
        if (!empty($filters['from'])) {
            $query->where($settings['date_column'], '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where($settings['date_column'], '<=', $filters['to']);
        }
        if (!empty($filters['status'])) {
            $query->where($settings['status_column'], $filters['status']);
        }
        return $query;
    }
}
