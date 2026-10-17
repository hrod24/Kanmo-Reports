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
    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Upload Attendance Data</h1>
            <p class="mt-1 text-sm text-slate-500">Upload Excel dengan memilih file atau drag & drop. Data lama tetap
                disimpan.</p>
        </div>

        @if (session('import_success') && count(session('import_success')))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                Import completed: {{ implode(', ', session('import_success')) }}
            </div>
        @endif
        @if (session('import_errors') && count(session('import_errors')))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                @foreach (session('import_errors') as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form id="upload-form" action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach ($types as $type)
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold text-slate-900">{{ $type['title'] }}</h2>
                        <p class="mb-4 mt-1 text-xs text-slate-500">{{ $type['description'] }}</p>
                        <div class="drop-zone relative flex min-h-40 flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-3 py-5 text-center transition hover:border-blue-400 hover:bg-blue-50 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100"
                            data-field="{{ $type['field'] }}">
                            <svg class="mb-2 h-8 w-8 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 16V4m0 0-4 4m4-4 4 4M4 16v4h16v-4" />
                            </svg>
                            <label for="file-{{ $type['field'] }}"
                                class="cursor-pointer text-sm font-semibold text-blue-600 hover:underline">Choose
                                file</label>
                            <p class="mt-1 text-xs text-slate-500">or drag & drop an Excel file here</p>
                            <p class="mt-1 text-[11px] text-slate-400">.xlsx / .xls · Max 20 MB</p>
                            <input id="file-{{ $type['field'] }}" name="{{ $type['field'] }}" type="file"
                                accept=".xlsx,.xls" class="upload-input sr-only" data-field="{{ $type['field'] }}">
                        </div>
                        <div class="file-info mt-3 hidden items-center justify-between gap-2 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-700"
                            data-info="{{ $type['field'] }}">
                            <span class="min-w-0 break-all" data-filename="{{ $type['field'] }}"></span>
                            <button type="button"
                                class="remove-file shrink-0 rounded px-2 py-1 font-semibold hover:bg-blue-100"
                                data-remove="{{ $type['field'] }}" aria-label="Remove selected file">Remove</button>
                        </div>
                    </section>
                @endforeach
            </div>
            <div class="mt-5 flex justify-end">
                <button id="import-button" type="submit"
                    class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">Import
                    Data</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Upload History</h2>
                <p class="mt-1 text-xs text-slate-500">Riwayat file yang diproses. Berhasil/gagal dicatat per file, tanpa
                    statistik jumlah baris.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Date / Time</th>
                            <th class="px-5 py-3">Report</th>
                            <th class="px-5 py-3">Filename</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($histories as $history)
                            <tr class="text-slate-700">
                                <td class="whitespace-nowrap px-5 py-3">{{ $history->created_at?->format('d M Y, H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">{{ $history->report_type }}</td>
                                <td class="max-w-xs break-all px-5 py-3">{{ $history->original_filename }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $history->status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ ucfirst($history->status) }}</span>
                                    @if ($history->status === 'failed')
                                        <p class="mt-1 max-w-sm break-words text-xs text-rose-600"
                                            title="{{ $history->error_message }}">
                                            {{ \Illuminate\Support\Str::limit($history->error_message, 100) }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada riwayat
                                    upload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($histories->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $histories->links() }}</div>
            @endif
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('upload-form');
            const button = document.getElementById('import-button');
            const zones = document.querySelectorAll('.drop-zone');
            const inputs = document.querySelectorAll('.upload-input');
            const maxBytes = 20 * 1024 * 1024;
            let submitted = false;

            function updateDisplay(input) {
                const field = input.dataset.field;
                const info = document.querySelector(`[data-info="${field}"]`);
                const filename = document.querySelector(`[data-filename="${field}"]`);
                const file = input.files[0];
                if (!file) {
                    info.classList.add('hidden');
                    info.classList.remove('flex');
                    filename.textContent = '';
                    return;
                }
                filename.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                info.classList.remove('hidden');
                info.classList.add('flex');
            }

            function validateFile(file) {
                if (!/\.(xlsx|xls)$/i.test(file.name)) {
                    alert('File harus berformat .xlsx atau .xls');
                    return false;
                }
                if (file.size > maxBytes) {
                    alert('Ukuran file maksimal 20 MB.');
                    return false;
                }
                return true;
            }

            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    if (input.files.length && !validateFile(input.files[0])) input.value = '';
                    updateDisplay(input);
                });
            });

            zones.forEach(zone => {
                const input = zone.querySelector('input[type=file]');
                ['dragenter', 'dragover'].forEach(name => zone.addEventListener(name, event => {
                    event.preventDefault();
                    zone.classList.add('border-blue-500', 'bg-blue-50');
                }));
                ['dragleave', 'drop'].forEach(name => zone.addEventListener(name, event => {
                    event.preventDefault();
                    if (name === 'dragleave' && zone.contains(event.relatedTarget)) return;
                    zone.classList.remove('border-blue-500', 'bg-blue-50');
                }));
                zone.addEventListener('drop', event => {
                    const files = event.dataTransfer.files;
                    if (files.length !== 1) {
                        alert('Pilih satu file untuk setiap kotak upload.');
                        return;
                    }
                    if (!validateFile(files[0])) return;
                    const transfer = new DataTransfer();
                    transfer.items.add(files[0]);
                    input.files = transfer.files;
                    updateDisplay(input);
                });
            });

            document.querySelectorAll('.remove-file').forEach(remove => {
                remove.addEventListener('click', () => {
                    const input = document.querySelector(
                        `.upload-input[data-field="${remove.dataset.remove}"]`);
                    input.value = '';
                    updateDisplay(input);
                });
            });

            // Prevent dropping outside upload zones from navigating away from the page.
            document.addEventListener('dragover', event => {
                if (event.dataTransfer?.types.includes('Files')) event.preventDefault();
            });
            document.addEventListener('drop', event => {
                if (event.dataTransfer?.files.length) event.preventDefault();
            });

            form.addEventListener('submit', event => {
                if (submitted) {
                    event.preventDefault();
                    return;
                }
                const selected = Array.from(inputs).some(input => input.files.length > 0);
                if (!selected) {
                    event.preventDefault();
                    alert('Pilih minimal satu file Excel.');
                    return;
                }
                submitted = true;
                button.disabled = true;
                button.textContent = 'Importing...';
            });
        });
    </script>
@endsection
