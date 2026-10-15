<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceRegularizationImport;
use App\Imports\DailyAttendanceImport;
use App\Imports\LeaveRequestImport;
use App\Imports\OnDutyImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload-data');
    }

    public function store(Request $request)
    {
        $request->validate([
            'daily_attendance' => [
                'nullable',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],

            'attendance_regularization' => [
                'nullable',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],

            'on_duty' => [
                'nullable',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],

            'leave_requests' => [
                'nullable',
                'file',
                'mimes:xlsx,xls',
                'max:20480',
            ],
        ]);

        if (
            !$request->hasFile('daily_attendance')
            && !$request->hasFile('attendance_regularization')
            && !$request->hasFile('on_duty')
            && !$request->hasFile('leave_requests')
        ) {
            return back()->withErrors([
                'upload' => 'Pilih minimal satu file untuk diimport.',
            ]);
        }

        $imports = [];

        if ($request->hasFile('daily_attendance')) {
            $file = $request->file('daily_attendance');

            $imports[] = [
                'name' => 'Daily Attendance',
                'file' => $file,
                'import' => new DailyAttendanceImport(
                    $file->getClientOriginalName()
                ),
            ];
        }

        if ($request->hasFile('attendance_regularization')) {
            $file = $request->file('attendance_regularization');

            $imports[] = [
                'name' => 'Attendance Regularization',
                'file' => $file,
                'import' => new AttendanceRegularizationImport(
                    $file->getClientOriginalName()
                ),
            ];
        }

        if ($request->hasFile('on_duty')) {
            $file = $request->file('on_duty');

            $imports[] = [
                'name' => 'On Duty',
                'file' => $file,
                'import' => new OnDutyImport(
                    $file->getClientOriginalName()
                ),
            ];
        }

        if ($request->hasFile('leave_requests')) {
            $file = $request->file('leave_requests');

            $imports[] = [
                'name' => 'Leave Requests',
                'file' => $file,
                'import' => new LeaveRequestImport(
                    $file->getClientOriginalName()
                ),
            ];
        }

        $success = [];
        $errors = [];

        foreach ($imports as $item) {
            try {
                DB::transaction(function () use ($item) {
                    Excel::import(
                        $item['import'],
                        $item['file']
                    );
                });

                $success[] = $item['name'];
            } catch (Throwable $e) {
                report($e);

                $errors[] = $item['name'] . ': ' . $e->getMessage();
            }
        }

        return back()
            ->with('import_success', $success)
            ->with('import_errors', $errors);
    }
}
