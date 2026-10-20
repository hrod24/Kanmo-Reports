@extends('layouts.dashboard-layouts')

@section('title', 'Dashboard')

@section('content')
@php
    $dateLabel = \Carbon\Carbon::parse($summary['date'])->locale('en')->translatedFormat('l, d F Y');
    $scheduled = max(1, $summary['scheduled']);
    $cards = [
        ['label' => 'Overall', 'value' => number_format($summary['overall'], 1).'%', 'note' => number_format($summary['scheduled']).' scheduled', 'icon' => 'pulse', 'bg' => 'bg-blue-50', 'fg' => 'text-blue-600'],
        ['label' => 'Present', 'value' => number_format($summary['present']), 'note' => 'Incl. valid requests', 'icon' => 'users', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-600'],
        ['label' => 'On Leave', 'value' => number_format($summary['on_leave']), 'note' => $summary['scheduled'] ? number_format($summary['on_leave'] / $scheduled * 100, 1).'% of scheduled' : '0% of scheduled', 'icon' => 'calendar', 'bg' => 'bg-violet-50', 'fg' => 'text-violet-600'],
        ['label' => 'WFH', 'value' => number_format($summary['wfh']), 'note' => $summary['scheduled'] ? number_format($summary['wfh'] / $scheduled * 100, 1).'% of scheduled' : '0% of scheduled', 'icon' => 'home', 'bg' => 'bg-sky-50', 'fg' => 'text-sky-600'],
        ['label' => 'Absent', 'value' => number_format($summary['absent']), 'note' => $summary['scheduled'] ? number_format($summary['absent'] / $scheduled * 100, 1).'% of scheduled' : '0% of scheduled', 'icon' => 'alert', 'bg' => 'bg-rose-50', 'fg' => 'text-rose-600'],
    ];
@endphp

<div class="space-y-4 pb-4">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
                Attendance Overview - {{ $dateLabel }}
            </h1>
            <p class="mt-1 text-xs text-slate-400">Kanmo Group · Daily Attendance Report</p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
            <span class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm sm:inline-flex">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Dashboard Overview
            </span>
        </form>
    </div>

    @unless($summary['has_data'])
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">
            No Daily Attendance data was found for {{ $dateLabel }}. Upload that day's file or select another report date. Zero values below do not mean all employees were absent.
        </div>
    @endunless

    {{-- Five summary cards --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @foreach($cards as $card)
            <article class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="text-lg font-medium text-slate-500">{{ $card['label'] }}</h2>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['bg'] }} {{ $card['fg'] }}">
                        @switch($card['icon'])
                            @case('pulse')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l3-7 4 14 3-7h4"/></svg>
                                @break
                            @case('users')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M19 20v-2a6 6 0 0 0-3-5.2"/></svg>
                                @break
                            @case('calendar')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18m-12 5 2 2 4-4"/></svg>
                                @break
                            @case('home')
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7v11H3V10Z"/><path d="M9 21v-8h6v8"/></svg>
                                @break
                            @default
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 4h.01"/></svg>
                        @endswitch
                    </div>
                </div>
                <p class="mt-1 text-3xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $card['note'] }}</p>
            </article>
        @endforeach
    </div>

    {{-- Business-unit section --}}
    <section>
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Attendance by Business Unit</h2>
                <p class="mt-1 text-xs text-slate-400">Performance overview across business units</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Excellent ≥95%</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Warning ≥90%</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-rose-500"></span>Below Target &lt;90%</span>
            </div>
        </div>

        @if(count($summary['business_units']))
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach($summary['business_units'] as $unit)
                    @php
                        $pct = $unit['percentage'];
                        $barColor = $pct >= 95 ? 'bg-emerald-500' : ($pct >= 90 ? 'bg-amber-500' : 'bg-rose-500');
                        $textColor = $pct >= 95 ? 'text-emerald-600' : ($pct >= 90 ? 'text-amber-600' : 'text-rose-600');
                        $rating = $pct >= 95 ? 'On target' : 'Under target';
                    @endphp
                    <article class="flex min-h-32 min-w-0 flex-col justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 class="min-h-10 break-words text-base font-medium leading-tight text-slate-500" title="{{ $unit['name'] }}">{{ $unit['name'] }}</h3>
                        <div>
                            <p class="mb-3 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($pct, 1) }}%</p>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}" aria-label="Attendance rate for {{ $unit['name'] }}">
                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center justify-between gap-1 text-[10px]">
                                <span class="text-slate-400">{{ number_format($unit['scheduled']) }} scheduled · {{ number_format($unit['present']) }} present</span>
                                <span class="font-medium {{ $textColor }}">{{ $rating }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                No Business Unit data available for the selected date.
            </div>
        @endif
    </section>
</div>
@endsection
