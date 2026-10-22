@extends('layouts.app')
@section('title', 'Upload Attendance')
@section('content')
    @php
        $types = [
            ['field' => 'daily_attendance', 'title' => 'Daily Attendance', 'description' => 'Daily Attendance Report'],
            [
                'field' => 'attendance_regularization',
                'title' => 'Attendance Regularization',
                'description' => 'Attendance Regularization Requests Report',
            ],
            ['field' => 'on_duty', 'title' => 'On Duty', 'description' => 'On Duty Report'],
            ['field' => 'leave_requests', 'title' => 'Leave Requests', 'description' => 'Leave Request Report'],
        ];
    @endphp
    <div class="max-w-6xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Upload Attendance Data</h1>
            <p class="mt-1 text-sm text-slate-500">Upload Excel dengan memilih file atau drag & drop. Data lama tetap
                disimpan.</p>
        </div>

        @if (session('import_success') && count(session('import_success')))
            <div role="status" class="p-4 text-sm border rounded-xl border-emerald-200 bg-emerald-50 text-emerald-700">
                Import completed: {{ implode(', ', session('import_success')) }}
            </div>
        @endif
        @if (session('import_errors') && count(session('import_errors')))
            <div role="alert" class="p-4 text-sm border rounded-xl border-rose-200 bg-rose-50 text-rose-700">
                @foreach (session('import_errors') as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" class="p-4 text-sm border rounded-xl border-rose-200 bg-rose-50 text-rose-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form id="upload-form"
            data-process-url="{{ route('upload.process', ['history' => '_ID_']) }}"
            data-status-url="{{ route('upload.progress', ['history' => '_ID_']) }}"
            action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach ($types as $type)
                    <section class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
                        <h2 class="text-sm font-semibold text-slate-900">{{ $type['title'] }}</h2>
                        <p class="mt-1 mb-4 text-xs text-slate-500">{{ $type['description'] }}</p>
                        <div class="relative flex flex-col items-center justify-center px-3 py-5 text-center transition border-2 border-dashed drop-zone min-h-40 rounded-xl border-slate-200 bg-slate-50 hover:border-blue-400 hover:bg-blue-50 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100"
                            data-field="{{ $type['field'] }}">
                            <svg class="w-8 h-8 mb-2 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 16V4m0 0-4 4m4-4 4 4M4 16v4h16v-4" />
                            </svg>
                            <label for="file-{{ $type['field'] }}"
                                class="text-sm font-semibold text-blue-600 cursor-pointer hover:underline">Choose
                                file</label>
                            <p class="mt-1 text-xs text-slate-500">or drag & drop an Excel file here</p>
                            <p class="mt-1 text-[11px] text-slate-400">.xlsx / .xls · Max 20 MB</p>
                            <input id="file-{{ $type['field'] }}" name="{{ $type['field'] }}" type="file"
                                accept=".xlsx,.xls" class="sr-only upload-input" data-field="{{ $type['field'] }}">
                        </div>
                        <div class="items-center justify-between hidden gap-2 px-3 py-2 mt-3 text-xs text-blue-700 rounded-lg file-info bg-blue-50"
                            data-info="{{ $type['field'] }}">
                            <span class="min-w-0 break-all" data-filename="{{ $type['field'] }}"></span>
                            <button type="button"
                                class="px-2 py-1 font-semibold rounded remove-file shrink-0 hover:bg-blue-100"
                                data-remove="{{ $type['field'] }}" aria-label="Remove selected file">Remove</button>
                        </div>
                        @include('partials.last-upload', ['lastUpload' => $lastUploads->get($type['title'])])
                    </section>
                @endforeach
            </div>
            <div class="flex justify-end mt-5">
                <button id="import-button" type="submit"
                    class="px-6 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">Import
                    Data</button>
            </div>
        </form>

        <section class="p-5 mt-6 bg-white border shadow-sm rounded-2xl border-slate-200">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-slate-900">
                        Import Progress
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Progress pemrosesan file Excel
                    </p>
                </div>

                <button id="resume-button" type="button"
                    class="hidden px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Resume
                </button>
            </div>

            <div id="progress-warning" role="alert"
                class="hidden p-3 mt-4 text-sm rounded-lg bg-amber-50 text-amber-700">
            </div>

            <div id="progress-items" class="mt-4 space-y-3">
                <p class="text-sm text-slate-400">
                    Belum ada import aktif.
                </p>
            </div>

        </section>

        <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Upload History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left divide-y divide-slate-100">
                    <thead class="text-xs bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Report</th>
                            <th class="px-5 py-3">Filename</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($histories as $history)
                            <tr class="text-slate-700">
                                <td class="px-5 py-3 whitespace-nowrap">{{ $history->report_type }}</td>
                                <td class="max-w-xs px-5 py-3 break-all">{{ $history->original_filename }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $history->status === 'success' ? 'bg-emerald-50 text-emerald-700' : ($history->status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-blue-50 text-blue-700') }}">{{ ucfirst($history->status) }}</span>
                                    @if ($history->status === 'failed')
                                        <p class="max-w-sm mt-1 text-xs break-words text-rose-600"
                                            title="{{ $history->error_message }}">
                                            {{ \Illuminate\Support\Str::limit($history->error_message, 100) }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-sm text-center text-slate-400">Belum ada riwayat
                                    upload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($histories->hasPages())
                <div class="px-5 py-3 border-t border-slate-100">{{ $histories->links() }}</div>
            @endif
        </section>
    </div>

@vite('resources/js/pages/upload-data.js')
@endsection
