@extends('layouts.app')
@section('title', $settings['title'])
@section('content')
<div class="space-y-5" id="report-page">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('reports') }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">← Back to Reports</a>
            <h1 class="mt-2 text-xl font-bold text-slate-900">{{ $settings['title'] }}</h1>
            <p class="mt-1 text-xs text-slate-500">Read-only data from MySQL · <span id="filtered-count">{{ number_format($rows->total()) }}</span> filtered records</p>
        </div>
        <a id="download-report" href="{{ route('reports.export', ['type' => $type] + request()->except('page', 'per_page')) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg>
            Download Excel
        </a>
    </div>

    <form id="report-filters" method="GET" action="{{ route('reports.show', $type) }}"
          class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
            <label class="block xl:col-span-2">
                <span class="block mb-1 text-xs font-medium text-slate-600">Employee Name / ID</span>
                <input type="search" name="search" value="{{ request('search') }}" autocomplete="off" placeholder="Search employee..."
                       class="w-full px-3 py-2 text-sm border rounded-lg outline-none border-slate-200 focus:border-blue-500">
            </label>
            <label class="block">
                <span class="block mb-1 text-xs font-medium text-slate-600">From ({{ $columns[$settings['date_column']] }})</span>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-2 py-2 text-sm border rounded-lg outline-none border-slate-200 focus:border-blue-500">
            </label>
            <label class="block">
                <span class="block mb-1 text-xs font-medium text-slate-600">To</span>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full px-2 py-2 text-sm border rounded-lg outline-none border-slate-200 focus:border-blue-500">
            </label>
            <label class="block">
                <span class="block mb-1 text-xs font-medium text-slate-600">Status</span>
                <select name="status" class="w-full px-2 py-2 text-sm bg-white border rounded-lg outline-none border-slate-200 focus:border-blue-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="block mb-1 text-xs font-medium text-slate-600">Rows</span>
                <select name="per_page" class="w-full px-2 py-2 text-sm bg-white border rounded-lg outline-none border-slate-200 focus:border-blue-500">
                    @foreach ([10,25,50,100] as $limit)
                        <option value="{{ $limit }}" @selected((int) request('per_page', 25) === $limit)>{{ $limit }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 mt-3">
            <p id="report-loading" role="status" aria-live="polite" class="hidden text-xs text-blue-600">Updating report...</p>
            <p id="report-error" role="alert" class="hidden text-xs text-rose-600"></p>
            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('reports.show', $type) }}" id="reset-report" class="px-4 py-2 text-sm text-white bg-blue-600 border rounded-lg hover:bg-blue-500">Reset</a>
                
            </div>
        </div>
    </form>

    <section id="report-results" class="overflow-hidden bg-white border shadow-sm rounded-xl border-slate-200" aria-live="polite">
        @include('reports.partials.table')
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('report-filters');
    if (!form) return;
    const table = document.getElementById('report-results');
    const count = document.getElementById('filtered-count');
    const download = document.getElementById('download-report');
    const loading = document.getElementById('report-loading');
    const error = document.getElementById('report-error');
    const pageUrl = form.action;
    const exportUrl = @json(route('reports.export', ['type' => $type]));
    let currentSort = @json(request('sort', 'id'));
    let currentDirection = @json(request('direction', 'desc'));
    let activeRequest = null;
    let debounce = null;
    let sequence = 0;

    function parameters() {
        const params = new URLSearchParams(new FormData(form));
        // Carry over chosen sorting even if there is no visible sort input.
        params.set('sort', currentSort);
        params.set('direction', currentDirection);
        return params;
    }

    function downloadUrl(params) {
        const p = new URLSearchParams(params);
        p.delete('page');
        p.delete('per_page');
        return exportUrl + '?' + p.toString();
    }

    async function load(url, historyMode = 'push') {
        if (activeRequest) activeRequest.abort();
        const controller = new AbortController();
        activeRequest = controller;
        const token = ++sequence;
        loading.classList.remove('hidden');
        error.classList.add('hidden');
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller.signal,
            });
            const data = await response.json();
            if (!response.ok) {
                const details = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                throw new Error(details || `HTTP ${response.status}`);
            }
            if (token !== sequence) return;
            table.innerHTML = data.html;
            count.textContent = Number(data.total).toLocaleString('en-US');
            download.href = downloadUrl(new URL(url).searchParams);
            if (historyMode === 'push') history.pushState({}, '', url);
            if (historyMode === 'replace') history.replaceState({}, '', url);
        } catch (e) {
            if (e.name !== 'AbortError') {
                error.textContent = 'Tidak dapat memuat report: ' + e.message;
                error.classList.remove('hidden');
            }
        } finally {
            if (token === sequence) {
                loading.classList.add('hidden');
                activeRequest = null;
            }
        }
    }

    function filterNow() {
        const params = parameters();
        params.delete('page');
        load(pageUrl + '?' + params.toString(), 'replace');
    }

    function scheduleSearch() {
        clearTimeout(debounce);
        debounce = setTimeout(filterNow, 350);
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        clearTimeout(debounce);
        filterNow();
    });
    form.querySelector('[name="search"]').addEventListener('input', scheduleSearch);
    for (const name of ['from', 'to', 'status', 'per_page']) {
        form.querySelector(`[name="${name}"]`).addEventListener('change', () => {
            clearTimeout(debounce);
            filterNow();
        });
    }
    table.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || !table.contains(link)) return;
        event.preventDefault();
        const next = new URL(link.href);
        currentSort = next.searchParams.get('sort') || currentSort;
        currentDirection = next.searchParams.get('direction') || currentDirection;
        load(next.href);
    });
    document.getElementById('reset-report').addEventListener('click', event => {
        event.preventDefault();
        form.reset();
        form.querySelector('[name="search"]').value = '';
        form.querySelector('[name="from"]').value = '';
        form.querySelector('[name="to"]').value = '';
        form.querySelector('[name="status"]').value = '';
        form.querySelector('[name="per_page"]').value = '25';
        currentSort = 'id';
        currentDirection = 'desc';
        filterNow();
    });
    window.addEventListener('popstate', () => {
        // Full navigation on Back/Forward keeps inputs and results synchronized.
        location.reload();
    });
});
</script>
@endsection
