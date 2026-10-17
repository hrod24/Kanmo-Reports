@extends('layouts.app')
@section('title', $settings['title'])
@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('reports') }}"
                    class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">← Back to Reports</a>
                <h1 class="mt-2 text-xl font-bold text-slate-900">{{ $settings['title'] }}</h1>
                <p class="mt-1 text-xs text-slate-500">Read-only data from MySQL • {{ number_format($rows->total()) }}
                    filtered records</p>
            </div>
        </div>

        <form method="GET" action="{{ route('reports.show', $type) }}"
            class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                <label class="block xl:col-span-2">
                    <span class="mb-1 block text-xs font-medium text-slate-600">Employee Name / ID</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search employee..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-medium text-slate-600">From
                        ({{ $columns[$settings['date_column']] }})</span>
                    <input type="date" name="from" value="{{ request('from') }}"
                        class="w-full rounded-lg border border-slate-200 px-2 py-2 text-sm outline-none focus:border-blue-500">
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-medium text-slate-600">To</span>
                    <input type="date" name="to" value="{{ request('to') }}"
                        class="w-full rounded-lg border border-slate-200 px-2 py-2 text-sm outline-none focus:border-blue-500">
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-medium text-slate-600">Status</span>
                    <select name="status"
                        class="w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm outline-none focus:border-blue-500">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-medium text-slate-600">Rows</span>
                    <select name="per_page"
                        class="w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm outline-none focus:border-blue-500">
                        @foreach ([10, 25, 50, 100] as $limit)
                            <option value="{{ $limit }}" @selected((int) request('per_page', 25) === $limit)>{{ $limit }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('reports.show', $type) }}"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Apply
                    Filters</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-max border-collapse text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3">No.</th>
                            @foreach ($columns as $key => $label)
                                @php
                                    $newDirection = $sort === $key && $direction === 'asc' ? 'desc' : 'asc';
                                    $sortUrl = route(
                                        'reports.show',
                                        array_merge(request()->except('page'), [
                                            'type' => $type,
                                            'sort' => $key,
                                            'direction' => $newDirection,
                                        ]),
                                    );
                                @endphp
                                <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3">
                                    <a href="{{ $sortUrl }}"
                                        class="inline-flex items-center gap-1 hover:text-blue-600">
                                        {{ $label }}
                                        @if ($sort === $key)
                                            <span>{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-blue-50/50">
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-400">
                                    {{ $rows->firstItem() + $loop->index }}</td>
                                @foreach ($columns as $key => $label)
                                    @php $value = $row->{$key} ?? null; @endphp
                                    <td class="max-w-80 whitespace-nowrap px-4 font-semibold py-3" title="{{ $value }}">
                                        @if ($key === $settings['status_column'])
                                            @php
                                                $normalized = strtolower(trim((string) $value));
                                                $badge = in_array($normalized, [
                                                    'present',
                                                    'approved',
                                                    'completed',
                                                    'accepted',
                                                ])
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : (in_array($normalized, ['absent', 'rejected', 'declined'])
                                                        ? 'bg-rose-50 text-rose-700'
                                                        : 'bg-slate-100 text-slate-600');
                                            @endphp
                                            <span
                                                class="inline-flex rounded-md px-2 py-1 text-xs {{ $badge }}">{{ $value ?: '—' }}</span>
                                        @else
                                            <span
                                                class="block max-w-80 truncate">{{ $value === null || $value === '' ? '—' : $value }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}"
                                    class="px-4 py-12 text-center text-sm text-slate-500">No records found. Try adjusting
                                    the filters or importing the relevant Excel file.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-4 py-3">
                <p class="text-xs text-slate-500">
                    @if ($rows->total())
                        Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ number_format($rows->total()) }}
                        records
                    @else
                        0 records
                    @endif
                </p>
                <div>{{ $rows->onEachSide(1)->links() }}</div>
            </div>
        </section>
    </div>
@endsection
