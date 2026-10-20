<?php

namespace App\Http\Controllers;

use App\Services\AttendanceDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, AttendanceDashboardService $service)
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        // Default: previous calendar day in the office timezone, never latest imported date.
        $date = $request->query('date') ?: now('Asia/Jakarta')->subDay()->toDateString();

        return view('dashboard', [
            'summary' => $service->summarize($date),
        ]);
    }
}
