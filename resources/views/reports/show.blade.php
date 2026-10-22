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

    <form id="report-filters"
          data-export-url="{{ route('reports.export', ['type' => $type]) }}"
          data-sort="{{ $sort }}" data-direction="{{ $direction }}"
          method="GET" action="{{ route('reports.show', $type) }}"
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

@vite('resources/js/pages/report.js')
@endsection
