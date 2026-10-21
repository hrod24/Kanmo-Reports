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

        <form id="upload-form" action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
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
                <p class="mt-1 text-xs text-slate-500">Riwayat file yang diproses. Berhasil/gagal dicatat per file, tanpa
                    statistik jumlah baris.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left divide-y divide-slate-100">
                    <thead class="text-xs bg-slate-50 text-slate-500">
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
                                <td class="px-5 py-3 whitespace-nowrap">{{ $history->created_at?->format('d M Y, H:i') }}
                                </td>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('upload-form'),
                button = document.getElementById('import-button');
            const inputs = [...document.querySelectorAll('.upload-input')],
                list = document.getElementById('progress-items');
            const resume = document.getElementById('resume-button'),
                notice = document.getElementById('progress-warning');
            const csrf = form.querySelector('[name="_token"]').value,
                key = 'attendance_batch_active_ids';
            const startUrl = @json(route('upload.store'));
            const processTemplate = @json(route('upload.process', ['history' => '_ID_']));
            const statusTemplate = @json(route('upload.progress', ['history' => '_ID_']));
            let jobs = [],
                busy = false;
            const url = (tpl, id) => tpl.replace('_ID_', id);

            function showFile(input) {
                const field = input.dataset.field,
                    info = document.querySelector(`[data-info="${field}"]`);
                const label = document.querySelector(`[data-filename="${field}"]`),
                    file = input.files[0];
                label.textContent = file ? `${file.name} (${(file.size/1048576).toFixed(2)} MB)` : '';
                info.classList.toggle('hidden', !file);
                info.classList.toggle('flex', !!file);
            }

            function valid(file) {
                if (!/\.(xlsx|xls)$/i.test(file.name)) {
                    alert('Gunakan .xlsx atau .xls');
                    return false;
                }
                if (file.size > 20 * 1048576) {
                    alert('Maksimal 20 MB per file');
                    return false;
                }
                return true;
            }
            inputs.forEach(i => i.addEventListener('change', () => {
                if (i.files[0] && !valid(i.files[0])) i.value = '';
                showFile(i);
            }));
            document.querySelectorAll('.drop-zone').forEach(zone => {
                const input = zone.querySelector('input[type=file]');
                for (const n of ['dragenter', 'dragover']) zone.addEventListener(n, e => {
                    e.preventDefault();
                    zone.classList.add('border-blue-500', 'bg-blue-50');
                });
                for (const n of ['dragleave', 'drop']) zone.addEventListener(n, e => {
                    e.preventDefault();
                    if (n === 'dragleave' && zone.contains(e.relatedTarget)) return;
                    zone.classList.remove('border-blue-500', 'bg-blue-50');
                });
                zone.addEventListener('drop', e => {
                    if (e.dataTransfer.files.length !== 1) {
                        alert('Satu file per kotak');
                        return;
                    }
                    const f = e.dataTransfer.files[0];
                    if (!valid(f)) return;
                    const dt = new DataTransfer();
                    dt.items.add(f);
                    input.files = dt.files;
                    showFile(input);
                });
            });
            document.querySelectorAll('.remove-file').forEach(b => b.addEventListener('click', () => {
                const input = document.querySelector(`.upload-input[data-field="${b.dataset.remove}"]`);
                input.value = '';
                showFile(input);
            }));
            document.addEventListener('dragover', e => {
                if (e.dataTransfer?.types.includes('Files')) e.preventDefault();
            });
            document.addEventListener('drop', e => {
                if (e.dataTransfer?.files.length) e.preventDefault();
            });
            async function api(url, opts = {}) {
                const res = await fetch(url, {
                    credentials: 'same-origin',
                    ...opts,
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        ...(opts.headers || {})
                    }
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw Error(data.message || `HTTP ${res.status}`);
                return data;
            }

            function render() {
                list.replaceChildren();
                if (!jobs.length) {
                    const p = document.createElement('p');
                    p.className = 'text-sm text-slate-400';
                    p.textContent = 'Belum ada import aktif.';
                    list.append(p);
                }
                for (const j of jobs) {
                    const box = document.createElement('div');
                    box.className = 'rounded-lg border border-slate-200 p-3';
                    const head = document.createElement('div');
                    head.className = 'mb-2 flex justify-between gap-3 text-sm';
                    const name = document.createElement('span');
                    name.className = 'break-all font-medium';
                    name.textContent = `${j.report_type}: ${j.original_filename}`;
                    const status = document.createElement('span');
                    status.className = 'shrink-0 text-xs ' + (j.status === 'failed' ? 'text-rose-600' : j.status ===
                        'success' ? 'text-emerald-600' : 'text-blue-600');
                    status.textContent = `${j.status} · ${j.percent}%`;
                    head.append(name, status);
                    box.append(head);
                    const track = document.createElement('div');
                    track.className = 'h-2 rounded-full bg-slate-100 overflow-hidden';
                    const fill = document.createElement('div');
                    fill.className = 'h-full ' + (j.status === 'failed' ? 'bg-rose-500' : j.status === 'success' ?
                        'bg-emerald-500' : 'bg-blue-600');
                    fill.style.width = j.percent + '%';
                    track.append(fill);
                    box.append(track);
                    if (j.error_message) {
                        const p = document.createElement('p');
                        p.className = 'mt-2 text-xs text-rose-600';
                        p.textContent = j.error_message;
                        box.append(p);
                    }
                    list.append(box);
                }
                sessionStorage.setItem(key, JSON.stringify(jobs.filter(j => !['success', 'failed'].includes(j
                    .status)).map(j => j.id)));
                resume.classList.toggle('hidden', busy || !jobs.some(j => !['success', 'failed'].includes(j
                    .status)));
            }
            async function run() {
                if (busy) return;
                busy = true;
                button.disabled = true;
                notice.classList.add('hidden');
                render();
                try {
                    for (let i = 0; i < jobs.length; i++) {
                        while (!['success', 'failed'].includes(jobs[i].status)) {
                            jobs[i] = await api(url(processTemplate, jobs[i].id), {
                                method: 'POST'
                            });
                            render();
                        }
                    }
                } catch (e) {
                    notice.textContent = `Terhenti: ${e.message}. Klik Resume untuk melanjutkan.`;
                    notice.classList.remove('hidden');
                } finally {
                    busy = false;
                    button.disabled = false;
                    button.textContent = 'Import Data';
                    render();
                    if (jobs.length && jobs.every(j => ['success', 'failed'].includes(j.status))) setTimeout(
                        () => location.reload(), 1200);
                }
            }
            resume.addEventListener('click', run);
            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                if (busy) return;

                const selected = inputs.some(input => input.files.length > 0);

                if (!selected) {
                    alert('Pilih minimal satu file Excel.');
                    return;
                }

                busy = true;
                button.disabled = true;
                button.textContent = 'Uploading...';

                notice.classList.add('hidden');

                try {
                    const result = await api(startUrl, {
                        method: 'POST',
                        body: new FormData(form),
                    });

                    if (!Array.isArray(result.jobs) || !result.jobs.length) {
                        throw new Error('Server tidak mengembalikan data import.');
                    }

                    // Simpan pekerjaan baru ke daftar progress
                    jobs = result.jobs;

                    // Simpan ID agar dapat dilanjutkan setelah halaman di-refresh
                    sessionStorage.setItem(
                        key,
                        JSON.stringify(jobs.map(job => job.id))
                    );

                    render();

                    // Lepaskan status busy agar run() dapat dimulai
                    busy = false;

                    // Jalankan pemrosesan 200 baris per request
                    await run();

                } catch (error) {
                    console.error('Upload error:', error);

                    notice.textContent = 'Upload gagal: ' + error.message;
                    notice.classList.remove('hidden');

                } finally {
                    busy = false;
                    button.disabled = false;
                    button.textContent = 'Import Data';
                }
            });
            (async () => {
                try {
                    const ids = JSON.parse(sessionStorage.getItem(key) || '[]');
                    if (!Array.isArray(ids) || !ids.length) return;
                    jobs = await Promise.all(ids.map(id => api(url(statusTemplate, id))));
                    render();
                } catch (e) {
                    sessionStorage.removeItem(key);
                }
            })();
        });
    </script>
@endsection
