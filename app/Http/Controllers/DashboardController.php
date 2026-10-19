<?php

namespace App\Http\Controllers;

use App\Services\AttendanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request, AttendanceDashboardService $service)
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        // Default to latest imported attendance date rather than current date;
        // otherwise a newly installed dashboard may misleadingly show zero.
        $date = $request->query('date')
            ?: (DB::table('daily_attendances')->max('date') ?: now()->toDateString());

        return view('dashboard', [
            'summary' => $service->summarize($date),
        ]);
    }
}
