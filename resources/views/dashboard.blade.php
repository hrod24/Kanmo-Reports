
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $summary = [
        [
            'title' => 'Overall',
            'value' => '87.7%',
            'description' => '487 scheduled',
            'icon' => 'activity',
            'bg' => 'bg-blue-50',
            'iconColor' => 'text-blue-600',
        ],
        [
            'title' => 'Present',
            'value' => '427',
            'description' => 'Incl. 0 on duty',
            'icon' => 'users',
            'bg' => 'bg-emerald-50',
            'iconColor' => 'text-emerald-600',
        ],
        [
            'title' => 'On Leave',
            'value' => '25',
            'description' => '5.1% of scheduled',
            'icon' => 'calendar',
            'bg' => 'bg-violet-50',
            'iconColor' => 'text-violet-600',
        ],
        [
            'title' => 'WFH',
            'value' => '0',
            'description' => '0% of scheduled',
            'icon' => 'home',
            'bg' => 'bg-sky-50',
            'iconColor' => 'text-sky-600',
        ],
        [
            'title' => 'Absent',
            'value' => '35',
            'description' => '7.2% of scheduled',
            'icon' => 'alert',
            'bg' => 'bg-rose-50',
            'iconColor' => 'text-rose-500',
        ],
    ];

    $divisions = [
        ['name' => 'Business Development', 'percentage' => 100, 'scheduled' => 5],
        ['name' => 'Fashion & Accessories 2', 'percentage' => 100, 'scheduled' => 5],
        ['name' => 'Management', 'percentage' => 100, 'scheduled' => 8],
        ['name' => 'Warehouse', 'percentage' => 100, 'scheduled' => 14],
        ['name' => 'Lifestyle', 'percentage' => 96.6, 'scheduled' => 29],
        ['name' => 'Human Resources', 'percentage' => 94.7, 'scheduled' => 38],
        ['name' => 'Footwear & Active', 'percentage' => 93.7, 'scheduled' => 63],
        ['name' => 'Finance', 'percentage' => 90.2, 'scheduled' => 61],
        ['name' => 'Project & Maintenance', 'percentage' => 87.5, 'scheduled' => 16],
        ['name' => 'Creative & Experience Execution', 'percentage' => 85.7, 'scheduled' => 14],
        ['name' => 'Shipping', 'percentage' => 84.6, 'scheduled' => 13],
        ['name' => 'DOIT', 'percentage' => 83, 'scheduled' => 141],
        ['name' => 'Fashion & Accessories', 'percentage' => 81.5, 'scheduled' => 65],
        ['name' => 'The Coach Restaurant', 'percentage' => 80, 'scheduled' => 5],
        ['name' => 'Legal & Compliance', 'percentage' => 60, 'scheduled' => 10],
    ];
@endphp

<div class="flex h-full min-h-0 flex-col gap-3.5">

    {{-- HEADER --}}
    <div class="flex items-center justify-between gap-3 shrink-0">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-[#20263A]">
                Attendance Overview
            </h1>

            <p class="mt-0.5 text-xs text-[#8B95AA]">
                Kanmo Group · Daily Attendance Report
            </p>
        </div>

        <div class="items-center hidden gap-2 px-3 py-2 text-xs font-medium bg-white rounded-lg shadow-sm text-slate-500 sm:flex">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            Dashboard Overview
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 gap-3 shrink-0 lg:grid-cols-5">

        @foreach ($summary as $item)

            <div class="group relative overflow-hidden rounded-xl
                        border border-[#E9EDF5] bg-white
                        px-4 py-3.5 shadow-[0_2px_12px_rgba(30,41,59,0.025)]
                        transition-all duration-200
                        hover:-translate-y-0.5
                        hover:shadow-[0_8px_22px_rgba(30,41,59,0.07)]">

                <div class="flex items-start justify-between gap-2">

                    <div class="min-w-0">

                        <p class="text-2xl font-medium text-slate-500">
                            {{ $item['title'] }}
                        </p>

                        <h2 class="mt-2 text-3xl
                                   font-bold leading-none tracking-tight
                                   text-[#20263A] ">
                            {{ $item['value'] }}
                        </h2>

                        <p class="mt-2 text-[14px] text-[#9CA6B9]">
                            {{ $item['description'] }}
                        </p>
                    </div>

                    {{-- ICON --}}
                    <div class="flex h-9 w-9 shrink-0 items-center
                                justify-center rounded-lg
                                {{ $item['bg'] }} {{ $item['iconColor'] }}">

                        @if ($item['icon'] === 'activity')
                            <svg class="h-[18px] w-[18px]" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M3 12h4l3-8 4 16 3-8h4"/>
                            </svg>

                        @elseif ($item['icon'] === 'users')
                            <svg class="h-[18px] w-[18px]" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="9" cy="8" r="3"/>
                                <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                                <path d="M17 5a3 3 0 0 1 0 6"/>
                                <path d="M19 20v-2a6 6 0 0 0-3-5.2"/>
                            </svg>

                        @elseif ($item['icon'] === 'calendar')
                            <svg class="h-[18px] w-[18px]" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M7 3v4M17 3v4M3 10h18"/>
                                <path d="m9 16 2 2 4-4"/>
                            </svg>

                        @elseif ($item['icon'] === 'home')
                            <svg class="h-[18px] w-[18px]" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="m3 10 9-7 9 7v10H3V10z"/>
                                <path d="M9 20v-7h6v7"/>
                            </svg>

                        @else
                            <svg class="h-[18px] w-[18px]" fill="none"
                                 stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v6M12 17h.01"/>
                            </svg>
                        @endif

                    </div>
                </div>
            </div>

        @endforeach
    </div>

    {{-- SECTION TITLE --}}
    <div class="flex flex-wrap items-center justify-between gap-2 shrink-0">

        <div>
            <h2 class="text-base font-bold tracking-tight
                       text-[#20263A]">
                Attendance by Division
            </h2>

            <p class="mt-0.5 text-[11px] text-[#8B95AA]">
                Performance overview across divisions
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-[10px]
                    font-medium text-[#8993A7]">

            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Excellent
            </span>

            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Warning
            </span>

            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Below Target
            </span>
        </div>
    </div>

    {{-- DIVISION GRID --}}
    <div class="grid flex-1 min-h-0 grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:grid-rows-3">

        @foreach ($divisions as $division)

            @php
                $percentage = (float) $division['percentage'];
                $progress = min(100, max(0, $percentage));

                if ($percentage >= 95) {
                    $color = 'text-emerald-600';
                    $bar = 'bg-emerald-500';
                    $badge = 'Excellent';
                    $badgeStyle = 'bg-emerald-50 text-emerald-600';
                } elseif ($percentage >= 90) {
                    $color = 'text-amber-600';
                    $bar = 'bg-amber-500';
                    $badge = 'Warning';
                    $badgeStyle = 'bg-amber-50 text-amber-600';
                } else {
                    $color = 'text-rose-500';
                    $bar = 'bg-rose-500';
                    $badge = 'Below Target';
                    $badgeStyle = 'bg-rose-50 text-rose-500';
                }
            @endphp

            <div class="group flex min-h-0 flex-col justify-between
                        rounded-xl border border-[#E9EDF5] bg-white
                        px-3.5 py-3
                        shadow-[0_2px_12px_rgba(30,41,59,0.025)]
                        transition-all duration-200
                        hover:-translate-y-0.5
                        hover:shadow-[0_8px_22px_rgba(30,41,59,0.07)]">

                {{-- TOP --}}
                <div class="min-w-0">

                    <h3 class="line-clamp-2 min-h-8
                               text-[18px] font-semibold leading-4
                               text-[#68758E]"
                        title="{{ $division['name'] }}">
                        {{ $division['name'] }}
                    </h3>

                    <div class="flex items-center justify-between gap-1 mt-1">

                        <span class="text-[clamp(1.15rem,1.65vw,1.7rem)]
                                     font-bold leading-tight
                                     tracking-tight text-[#20263A]">
                            {{ number_format($percentage, 1) }}%
                        </span>

                        <span class="hidden shrink-0 rounded-md px-1.5
                                     py-1 text-[9px] font-semibold
                                     2xl:inline-flex {{ $badgeStyle }}">
                            {{ $badge }}
                        </span>
                    </div>
                </div>

                {{-- PROGRESS --}}
                <div class="mt-2.5">

                    <div class="h-[7px] w-full overflow-hidden
                                rounded-full bg-[#EDF1F7]"
                         role="progressbar"
                         aria-label="{{ $division['name'] }} attendance"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         aria-valuenow="{{ $percentage }}">

                        <div class="h-full rounded-full {{ $bar }}"
                             style="width: {{ $progress }}%">
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-1 mt-2">

                        <p class="text-[10px] text-[#9CA6B9]">
                            {{ $division['scheduled'] }} scheduled
                        </p>

                        <span class="text-[10px] font-medium {{ $color }}">
                            {{ $percentage >= 95 ? 'On target' : 'Under target' }}
                        </span>
                    </div>
                </div>

            </div>

        @endforeach
    </div>

</div>

@endsection
