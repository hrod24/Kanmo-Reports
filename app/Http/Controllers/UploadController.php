<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceRegularizationImport;
use App\Imports\DailyAttendanceImport;
use App\Imports\LeaveRequestImport;
use App\Imports\OnDutyImport;
use App\Models\ImportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class UploadController extends Controller
{
    private const FILES = [
        'daily_attendance' => ['label' => 'Daily Attendance', 'import' => DailyAttendanceImport::class],
        'attendance_regularization' => ['label' => 'Attendance Regularization', 'import' => AttendanceRegularizationImport::class],
        'on_duty' => ['label' => 'On Duty', 'import' => OnDutyImport::class],
        'leave_requests' => ['label' => 'Leave Requests', 'import' => LeaveRequestImport::class],
    ];

    public function index()
    {
        $histories = ImportHistory::query()->latest('id')->paginate(10);
        return view('upload-data', compact('histories'));
    }

    public function store(Request $request)
    {
        $rules = [];
        foreach (array_keys(self::FILES) as $field) {
            $rules[$field] = ['nullable', 'file', 'mimes:xlsx,xls', 'max:20480'];
        }
        $request->validate($rules);

        if (! collect(array_keys(self::FILES))->contains(fn($field) => $request->hasFile($field))) {
            return back()->withErrors(['upload' => 'Pilih minimal satu file Excel.']);
        }

        $success = [];
        $errors = [];

        foreach (self::FILES as $field => $info) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $originalName = $file->getClientOriginalName();
            $status = 'success';
            $errorMessage = null;

            try {
                // Each file is independent. On error, any changes by that file are rolled back.
                DB::transaction(function () use ($file, $info, $originalName) {
                    Excel::import(new $info['import']($originalName), $file);
                });
                $success[] = $info['label'];
            } catch (Throwable $e) {
                report($e);
                $status = 'failed';
                $errorMessage = Str::limit($e->getMessage(), 1500);
                $errors[] = $info['label'] . ': gagal diproses. Periksa history atau log Laravel.';
            }

            // Saved outside transaction, so failed attempts are preserved as history.
            ImportHistory::create([
                'report_type' => $info['label'],
                'original_filename' => $originalName,
                'file_size' => $file->getSize() ?: 0,
                'status' => $status,
                'error_message' => $errorMessage,
                'completed_at' => now(),
            ]);
        }

        return redirect()->route('upload.index')
            ->with('import_success', $success)
            ->with('import_errors', $errors);
    }
}
