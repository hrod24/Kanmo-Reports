@extends('layouts.app')
@section('title', 'Reports')
@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reports Overview</h1>
                <p class="mt-1 text-sm text-slate-500">Browse attendance reports imported into the database.</p>
            </div>
            <input id="report-search" type="search" placeholder="Search reports..." aria-label="Search reports"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-blue-500 sm:w-72">
        </div>
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Report Modules</h2>
            <span id="report-count"
                class="rounded-lg bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ count($reports) }}
                Reports</span>
        </div>
        <div id="report-grid" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($reports as $slug => $report)
                <article
                    class="report-card flex min-h-40 flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md"
                    data-name="{{ strtolower($report['title']) }}">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <rect x="3" y="4" width="18" height="16" rx="2" />
                                <path d="M3 10h18M9 10v10" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $report['title'] }}</h3>
                            <p class="mt-1 text-xs text-slate-500">View, search and filter attendance records</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.show', $slug) }}"
                        class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                        View Details <span aria-hidden="true">→</span>
                    </a>
                </article>
            @endforeach
        </div>
        <p id="no-results" class="hidden py-8 text-center text-sm text-slate-500">No matching report modules.</p>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('report-search');
            const cards = [...document.querySelectorAll('.report-card')];
            input.addEventListener('input', () => {
                let count = 0;
                cards.forEach(card => {
                    const matches = card.dataset.name.includes(input.value.toLowerCase().trim());
                    card.classList.toggle('hidden', !matches);
                    if (matches) count++;
                });
                document.getElementById('report-count').textContent = `${count} Reports`;
                document.getElementById('no-results').classList.toggle('hidden', count !== 0);
            });
        });
    </script>
@endsection
