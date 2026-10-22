<?php

namespace App\Http\Controllers;

use App\Models\ImportHistory;
use App\Services\AttendanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request, AttendanceDashboardService $service)
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        $today = now('Asia/Jakarta')->startOfDay();

        // Keep showing the latest available completed day until newer attendance is imported.
        $date = $request->query('date')
            ?: DB::table('daily_attendances')->where('date', '<', $today->toDateString())->max('date')
            ?: $today->copy()->subDay()->toDateString();

        return view('dashboard', [
            'summary' => $service->summarize($date),
            'lastUpload' => ImportHistory::query()
                ->where('status', 'success')
                ->orderByRaw('COALESCE(completed_at, created_at) DESC')
                ->orderByDesc('id')
                ->first(),
        ]);
    }
}
