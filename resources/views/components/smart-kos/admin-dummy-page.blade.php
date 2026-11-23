@props([
    'title',
    'description',
    'metrics' => [],
    'columns' => [],
    'rows' => [],
])

{{-- Compact Metric Cards (2 columns on mobile, 4 columns on lg) --}}
@if (!empty($metrics))
    <div class="grid grid-cols-2 gap-2.5 sm:gap-4 lg:grid-cols-4">
        @foreach ($metrics as $metric)
            <div class="rounded-xl border border-gray-200 bg-white p-3 sm:p-4 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</span>
                <div class="mt-2.5 flex items-baseline justify-between gap-2">
                    <strong class="text-lg font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-xl">
                        {{ $metric['value'] }}
                    </strong>
                    @if (!empty($metric['note']))
                        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            {{ $metric['note'] }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Table / Card Container --}}
<div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
        <h3 class="text-base font-bold text-gray-800 dark:text-white/90 sm:text-lg">{{ $title }}</h3>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 sm:text-sm">{{ $description }}</p>
    </div>

    {{-- Mobile Card View (Zero horizontal scrolling on small screens) --}}
    <div class="divide-y divide-gray-100 sm:hidden dark:divide-gray-800">
        @forelse ($rows as $row)
            <div class="p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
                            {{ data_get($row, $columns[0]['key'] ?? 'name') }}
                        </p>
                        @if (isset($row['room']) && isset($row['location']))
                            <p class="text-xs text-gray-400">
                                Kamar {{ data_get($row, 'room') }} &bull; {{ data_get($row, 'location') }}
                            </p>
                        @endif
                    </div>
                    @if (isset($row['status']))
                        @php
                            $statusVal = strtolower((string) data_get($row, 'status'));
                            $isPaid = str_contains($statusVal, 'aman') || str_contains($statusVal, 'lunas');
                            $isWarning = str_contains($statusVal, 'dekat') || str_contains($statusVal, 'mendekati');
                            $isDanger = str_contains($statusVal, 'tempo') || str_contains($statusVal, 'terlambat');
                            $badgeCls = $isPaid
                                ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'
                                : ($isWarning
                                    ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400'
                                    : ($isDanger
                                        ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400'
                                        : 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'));
                        @endphp
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeCls }}">
                            {{ data_get($row, 'status') }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400 pt-1">
                    @foreach (array_slice($columns, 1) as $column)
                        @if (($column['key'] ?? '') !== 'status')
                            <div>
                                <span class="text-[10px] text-gray-400 block">{{ $column['label'] }}</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ data_get($row, $column['key']) }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <div class="py-8 text-center text-xs text-gray-400">
                Belum ada data untuk ditampilkan.
            </div>
        @endforelse
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-start text-xs">
            <thead class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400">
                <tr>
                    @foreach ($columns as $column)
                        <th class="px-5 py-3.5 text-start">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                @forelse ($rows as $row)
                    <tr class="hover:bg-gray-50/50 transition dark:hover:bg-white/[0.01]">
                        @foreach ($columns as $column)
                            <td class="px-5 py-3.5 text-sm">
                                @if (($column['key'] ?? '') === 'status')
                                    @php
                                        $statusVal = strtolower((string) data_get($row, $column['key']));
                                        $isPaid = str_contains($statusVal, 'aman') || str_contains($statusVal, 'lunas');
                                        $isWarning = str_contains($statusVal, 'dekat') || str_contains($statusVal, 'mendekati');
                                        $isDanger = str_contains($statusVal, 'tempo') || str_contains($statusVal, 'terlambat');
                                        $badgeCls = $isPaid
                                            ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'
                                            : ($isWarning
                                                ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400'
                                                : ($isDanger
                                                    ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400'
                                                    : 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400'));
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeCls }}">
                                        {{ data_get($row, $column['key']) }}
                                    </span>
                                @else
                                    {{ data_get($row, $column['key']) }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-8 text-center text-xs text-gray-400" colspan="{{ count($columns) }}">
                            Belum ada data untuk ditampilkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
