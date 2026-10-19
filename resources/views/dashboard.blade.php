@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Attendance Overview</h1>
            <p class="text-xs text-slate-500">Kanmo Group · Daily Attendance Report</p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
            <label class="text-xs text-slate-600" for="dashboard-date">Report Date</label>
            <input id="dashboard-date" name="date" type="date" value="{{ $summary['date'] }}"
                   class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" />
            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">Apply</button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Overall', 'value' => number_format($summary['overall'], 1).'%', 'note' => $summary['scheduled'].' scheduled'],
            ['label' => 'Present', 'value' => $summary['present'], 'note' => 'Incl. approved / pending requests'],
            ['label' => 'On Leave', 'value' => $summary['on_leave'], 'note' => 'Incl. pending leave'],
            ['label' => 'WFH', 'value' => $summary['wfh'], 'note' => 'On Duty type WFH'],
            ['label' => 'Absent', 'value' => $summary['absent'], 'note' => 'No punch or valid request'],
        ] as $card)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-semibold text-slate-500">{{ $card['label'] }}</h2>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ $card['value'] }}</div>
                <p class="mt-1 text-xs text-slate-400">{{ $card['note'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="text-base font-semibold text-slate-900">Attendance by Division</h2>
        <p class="mt-2 text-sm text-slate-500">
            Division breakdown requires an employee master or employee-to-division mapping.
            The four imported reports currently do not contain a division column.
            No division percentages are generated until the mapping is available.
        </p>
    </div>
</div>
@endsection
