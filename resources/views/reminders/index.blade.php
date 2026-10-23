@extends('layouts.app')
@section('title', 'Reminder')
@section('content')
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Absent Employee Reminders</h1>
            <p class="mt-1 text-sm text-slate-500">{{ number_format($rows->total()) }} absent employees on {{ $date }}. Open Teams to review and send a reminder.</p>
        </div>

        <form method="GET" action="{{ route('reminders.index') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <label class="block">
                <span class="mb-1 block text-xs font-medium text-slate-600">Absent Date</span>
                <input type="date" name="date" value="{{ $date }}" required class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            </label>
            <label class="block flex-1">
                <span class="mb-1 block text-xs font-medium text-slate-600">Employee Name / ID</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search employee..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
            </label>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
            <a href="{{ route('reminders.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600">Reset</a>
        </form>
        @if ($errors->any())
            <p role="alert" class="text-sm text-rose-600">{{ $errors->first() }}</p>
        @endif

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-max text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-600">
                        <tr>
                            @foreach (['No.', 'Reminder', 'Employee ID', 'Employee Name', 'Business Unit', 'Department', 'Email', 'Absent Date'] as $heading)
                                <th class="px-4 py-3">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" data-reminder-rows data-first-item="{{ $rows->firstItem() ?? 1 }}">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-slate-50" data-employee-row>
                                <td class="px-4 py-3 text-slate-400" data-row-number>{{ $rows->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3">
                                    @if ($row->teams_url)
                                        <a href="{{ $row->teams_url }}" target="_blank" rel="noopener noreferrer" data-teams-reminder class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700" aria-label="Open Teams reminder for {{ $row->employee_name }}">Teams</a>
                                    @else
                                        <button type="button" disabled title="A valid primary email is required" class="cursor-not-allowed rounded-lg bg-slate-100 px-4 py-2 text-xs text-slate-400">Teams</button>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $row->employee_id }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $row->employee_name }}</td>
                                <td class="px-4 py-3">{{ $row->business_unit }}</td>
                                <td class="px-4 py-3">{{ $row->department }}</td>
                                <td class="px-4 py-3">{{ $row->primary_email ?: 'Email not available' }}</td>
                                <td class="px-4 py-3">{{ $row->date }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">No absent employees found for this date and search.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-4 py-3">{{ $rows->onEachSide(1)->links() }}</div>
        </section>
        <p class="text-xs text-slate-500">Opened means the Teams chat was opened. Send the prepared message in Teams to complete the reminder.</p>
    </div>
    @vite('resources/js/pages/reminder.js')
@endsection
