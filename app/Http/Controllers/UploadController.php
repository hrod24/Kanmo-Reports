<?php

namespace App\Http\Controllers;

use App\Models\ImportHistory;
use App\Services\AttendanceBatchProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class UploadController extends Controller
{
    public function index()
    {
        $histories = ImportHistory::query()->latest('id')->paginate(10);
        $lastUploads = ImportHistory::query()
            ->whereIn('id', ImportHistory::query()->selectRaw('MAX(id)')->where('status', 'success')->groupBy('report_type'))
            ->get()->keyBy('report_type');

        return view('upload-data', compact('histories', 'lastUploads'));
    }

    public function store(Request $request, AttendanceBatchProcessor $processor): JsonResponse
    {
        $rules = [];
        foreach (array_keys(AttendanceBatchProcessor::TYPES) as $field) {
            $rules[$field] = ['nullable', 'file', 'mimes:xlsx,xls', 'max:20480'];
        }
        $request->validate($rules);
        if (! collect(array_keys($rules))->contains(fn($field) => $request->hasFile($field))) {
            return response()->json(['message' => 'Pilih minimal satu file Excel.'], 422);
        }

        $jobs = [];
        foreach (AttendanceBatchProcessor::TYPES as $field => $type) {
            if (!$request->hasFile($field)) continue;
            $file = $request->file($field);
            $path = $file->storeAs('attendance-imports', (string) Str::uuid() . '.' . strtolower($file->getClientOriginalExtension()), 'local');
            try {
                $count = $processor->totalRows(Storage::disk('local')->path($path), $type['heading']);
                if ($count < 1) throw new \RuntimeException('File Excel tidak memiliki baris data.');
                $history = ImportHistory::create([
                    'report_type' => $type['label'],
                    'report_key' => $field,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => (int) $file->getSize(),
                    'status' => 'queued',
                    'stored_path' => $path,
                    'total_rows' => $count,
                    'processed_rows' => 0,
                    'next_row' => $type['heading'] + 1,
                ]);
                $jobs[] = $this->statusData($history);
            } catch (Throwable $e) {
                report($e);
                Storage::disk('local')->delete($path);
                $history = ImportHistory::create([
                    'report_type' => $type['label'],
                    'report_key' => $field,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => (int) $file->getSize(),
                    'status' => 'failed',
                    'error_message' => Str::limit($e->getMessage(), 1500),
                    'completed_at' => now(),
                ]);
                $jobs[] = $this->statusData($history);
            }
        }
        return response()->json(['jobs' => $jobs]);
    }

    public function progress(ImportHistory $history): JsonResponse
    {
        return response()->json($this->statusData($history));
    }

    public function process(ImportHistory $history, AttendanceBatchProcessor $processor): JsonResponse
    {
        // The row cursor is locked until both row changes and progress are committed.
        try {
            DB::transaction(function () use ($history, $processor) {
                $item = ImportHistory::query()->whereKey($history->getKey())->lockForUpdate()->firstOrFail();
                if (in_array($item->status, ['success', 'failed'], true)) return;
                if (!isset(AttendanceBatchProcessor::TYPES[$item->report_key])) {
                    throw new \RuntimeException('Jenis report tidak valid.');
                }
                if (!$item->stored_path || !Storage::disk('local')->exists($item->stored_path)) {
                    throw new \RuntimeException('File sementara tidak ditemukan.');
                }
                $item->status = 'processing';
                $processed = $processor->process(
                    Storage::disk('local')->path($item->stored_path),
                    $item->report_key,
                    $item->original_filename,
                    (int) $item->next_row,
                    (int) $item->total_rows
                );
                $item->next_row += $processed;
                $item->processed_rows += $processed;
                if ($item->processed_rows >= $item->total_rows) {
                    $item->status = 'success';
                    $item->completed_at = now();
                }
                $item->save();
            }, 2);
        } catch (Throwable $e) {
            report($e);
            // Keep a failed record, but do not invalidate already committed batches.
            DB::transaction(function () use ($history, $e) {
                $item = ImportHistory::query()->whereKey($history->getKey())->lockForUpdate()->firstOrFail();
                if ($item->status === 'success') return;
                $item->status = 'failed';
                $item->error_message = Str::limit($e->getMessage(), 1500);
                $item->completed_at = now();
                $item->save();
            });
        }
        $history->refresh();
        if ($history->status === 'success' && $history->stored_path) {
            Storage::disk('local')->delete($history->stored_path);
            // Keep metadata but remove the path so it can't be processed again.
            $history->update(['stored_path' => null]);
        }
        return response()->json($this->statusData($history));
    }

    private function statusData(ImportHistory $history): array
    {
        return [
            'id' => $history->id,
            'report_type' => $history->report_type,
            'original_filename' => $history->original_filename,
            'status' => $history->status,
            'processed_rows' => (int) $history->processed_rows,
            'total_rows' => (int) $history->total_rows,
            'percent' => $history->status === 'success' ? 100 : ($history->total_rows > 0 ? (int) floor(100 * $history->processed_rows / $history->total_rows) : 0),
            'error_message' => $history->status === 'failed' ? $history->error_message : null,
        ];
    }
}
