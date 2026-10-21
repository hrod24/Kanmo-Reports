<div class="overflow-x-auto">
    <table class="w-full min-w-max border-collapse text-left text-sm">
        <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600">
            <tr>
                <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3">No.</th>
                @foreach ($columns as $key => $label)
                    @php
                        $newDirection = $sort === $key && $direction === 'asc' ? 'desc' : 'asc';
                        $sortUrl = route('reports.show', array_merge(request()->except('page'), [
                            'type' => $type, 'sort' => $key, 'direction' => $newDirection,
                        ]));
                    @endphp
                    <th class="whitespace-nowrap border-b border-slate-200 px-4 py-3">
                        <a href="{{ $sortUrl }}" data-ajax-link class="inline-flex items-center gap-1 hover:text-blue-600">
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
                    <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-400">{{ $rows->firstItem() + $loop->index }}</td>
                    @foreach ($columns as $key => $label)
                        @php
                            $value = $row->{$key} ?? null;
                            $statusValue = strtolower(trim((string) $value));
                            $badge = in_array($statusValue, ['present', 'approved', 'completed', 'accepted'], true)
                                ? 'bg-emerald-50 text-emerald-700'
                                : (in_array($statusValue, ['absent', 'absent-nopunch', 'rejected', 'cancelled', 'declined'], true)
                                    ? 'bg-rose-50 text-rose-700'
                                    : 'bg-slate-100 text-slate-600');
                        @endphp
                        <td class="max-w-80 whitespace-nowrap px-4 py-3" title="{{ $value }}">
                            @if ($key === $settings['status_column'])
                                <span class="inline-flex rounded-md px-2 py-1 text-xs {{ $badge }}">{{ $value === null || $value === '' ? '—' : $value }}</span>
                            @else
                                <span class="block max-w-80 truncate">{{ $value === null || $value === '' ? '—' : $value }}</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) + 1 }}" class="px-4 py-12 text-center text-sm text-slate-500">No records found. Try adjusting the filters or importing the relevant Excel file.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-4 py-3">
    <p class="text-xs text-slate-500">
        @if($rows->total())
            Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ number_format($rows->total()) }} records
        @else
            0 records
        @endif
    </p>
    <div data-ajax-pagination>{{ $rows->onEachSide(1)->links() }}</div>
</div>
