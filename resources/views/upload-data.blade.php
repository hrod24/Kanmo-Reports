@extends('layouts.app')

@section('title', 'Upload Attendance')

@section('content')

    <div class="max-w-6xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">
                Upload Attendance Data
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Import attendance, regularization, on duty,
                and leave request reports.
            </p>
        </div>

        @if (session()->has('import_success') && count(session('import_success')))
            <div role="status"
                class="px-4 py-3 mb-4 text-sm border rounded-xl border-emerald-200 bg-emerald-50 text-emerald-700">

                <div class="flex items-center gap-2 font-semibold">
                    <span>✓</span>
                    Import Completed Successfully
                </div>

                <p class="mt-1">
                    Imported:
                    {{ implode(', ', session('import_success')) }}
                </p>
            </div>
        @endif

        @if (session('import_errors') && count(session('import_errors')))
            <div class="px-4 py-3 mb-4 text-sm text-red-700 border border-red-200 rounded-xl bg-red-50">

                <div class="mb-2 font-medium">
                    Some files could not be imported:
                </div>

                <ul class="pl-5 space-y-1 list-disc">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif

        @if ($errors->any())
            <div class="px-4 py-3 mb-4 text-sm text-red-700 border border-red-200 rounded-xl bg-red-50">

                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach

            </div>
        @endif


        <form id="upload-form" action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                {{-- DAILY ATTENDANCE --}}
                <div class="p-6 bg-white border rounded-2xl border-slate-200">

                    <div class="mb-5">
                        <div class="text-sm font-semibold text-slate-900">
                            Daily Attendance
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            Daily Attendance Report
                        </div>
                    </div>

                    <label
                        class="flex flex-col items-center justify-center transition border-2 border-dashed cursor-pointer min-h-36 rounded-xl border-slate-200 bg-slate-50 hover:border-blue-400 hover:bg-blue-50">
                        <svg class="w-8 h-8 mb-2 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 16V4m0 0-4 4m4-4 4 4M4 16v4h16v-4" />
                        </svg>

                        <span class="text-sm font-medium text-slate-700">
                            Choose Daily Attendance file
                        </span>

                        <span class="mt-1 text-xs text-slate-400">
                            XLSX or XLS
                        </span>

                        <input type="file" name="daily_attendance" accept=".xlsx,.xls" class="sr-only upload-input"
                            data-label="daily-file-name" aria-label="Choose Daily Attendance file">
                    </label>

                    <div id="daily-file-name"
                        class="hidden px-3 py-2 mt-3 text-xs rounded-lg bg-emerald-50 text-emerald-700">
                    </div>

                </div>


                {{-- REGULARIZATION --}}
                <div class="p-6 bg-white border rounded-2xl border-slate-200">

                    <div class="mb-5">
                        <div class="text-sm font-semibold text-slate-900">
                            Attendance Regularization
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            Attendance Regularization Requests Report
                        </div>
                    </div>

                    <label
                        class="flex flex-col items-center justify-center transition border-2 border-dashed cursor-pointer min-h-36 rounded-xl border-slate-200 bg-slate-50 hover:border-blue-400 hover:bg-blue-50">
                        <svg class="w-8 h-8 mb-2 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 16V4m0 0-4 4m4-4 4 4M4 16v4h16v-4" />
                        </svg>

                        <span class="text-sm font-medium text-slate-700">
                            Choose Daily Attendance file
                        </span>

                        <span class="mt-1 text-xs text-slate-400">
                            XLSX or XLS
                        </span>

                        <input type="file" name="daily_attendance" accept=".xlsx,.xls" class="sr-only upload-input"
                            data-label="regularization-file-name" aria-label="Choose Daily Attendance file">
                    </label>

                    <div id="regularization-file-name"
                        class="hidden px-3 py-2 mt-3 text-xs rounded-lg bg-emerald-50 text-emerald-700">
                    </div>

                </div>


                {{-- ON DUTY --}}
                <div class="p-6 bg-white border rounded-2xl border-slate-200">

                    <div class="mb-5">
                        <div class="text-sm font-semibold text-slate-900">
                            On Duty
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            On Duty Report
                        </div>
                    </div>

                    <label
                        class="flex flex-col items-center justify-center transition border-2 border-dashed cursor-pointer min-h-36 rounded-xl border-slate-200 bg-slate-50 hover:border-blue-400 hover:bg-blue-50">
                        <svg class="w-8 h-8 mb-2 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 16V4m0 0-4 4m4-4 4 4M4 16v4h16v-4" />
                        </svg>

                        <span class="text-sm font-medium text-slate-700">
                            Choose Daily Attendance file
                        </span>

                        <span class="mt-1 text-xs text-slate-400">
                            XLSX or XLS
                        </span>

                        <input type="file" name="daily_attendance" accept=".xlsx,.xls" class="sr-only upload-input"
                            data-label="onduty-file-name" aria-label="Choose Daily Attendance file">
                    </label>

                    <div id="onduty-file-name"
                        class="hidden px-3 py-2 mt-3 text-xs rounded-lg bg-emerald-50 text-emerald-700">
                    </div>

                </div>


                {{-- LEAVE --}}
                <div class="p-6 bg-white border rounded-2xl border-slate-200">

                    <div class="mb-5">
                        <div class="text-sm font-semibold text-slate-900">
                            Leave Requests
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            Leave Request Report
                        </div>
                    </div>

                    <label
                        class="flex flex-col items-center justify-center transition border-2 border-dashed cursor-pointer min-h-36 rounded-xl border-slate-200 bg-slate-50 hover:border-blue-400 hover:bg-blue-50">
                        <svg class="w-8 h-8 mb-2 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 16V4m0 0-4 4m4-4 4 4M4 16v4h16v-4" />
                        </svg>

                        <span class="text-sm font-medium text-slate-700">
                            Choose Daily Attendance file
                        </span>

                        <span class="mt-1 text-xs text-slate-400">
                            XLSX or XLS
                        </span>

                        <input type="file" name="daily_attendance" accept=".xlsx,.xls" class="sr-only upload-input"
                            data-label="leave-file-name" aria-label="Choose Daily Attendance file">
                    </label>

                    <div id="leave-file-name"
                        class="hidden px-3 py-2 mt-3 text-xs rounded-lg bg-emerald-50 text-emerald-700">
                    </div>

                </div>

            </div>


            <div class="flex justify-end mt-6">

                <button id="import-button" type="submit"
                    class="px-6 py-3 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="import-button-text">Import Data</span>
                </button>

            </div>

        </form>

    </div>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const inputs = document.querySelectorAll('.upload-input');
        const form = document.getElementById('upload-form');
        const button = document.getElementById('import-button');
        const buttonText = document.getElementById('import-button-text');

        form?.addEventListener('submit', function(event) {

            const hasFile = Array.from(inputs).some(input => input.files.length > 0);

            if (!hasFile) {
                event.preventDefault();
                alert('Pilih minimal satu file Excel terlebih dahulu.');
                return;
            }

            button.disabled = true;
            buttonText.textContent = 'Importing...';

        });

        inputs.forEach(input => {

            input.addEventListener('change', function() {

                const labelId = this.dataset.label;
                const label = document.getElementById(labelId);
                const file = this.files[0];

                if (!label) return;

                if (file) {
                    const size = (file.size / 1024 / 1024).toFixed(2);

                    label.textContent =
                        '✓ Selected: ' + file.name + ' (' + size + ' MB)';

                    label.classList.remove('hidden');
                } else {
                    label.textContent = '';
                    label.classList.add('hidden');
                }

            });

        });

    });
</script>
