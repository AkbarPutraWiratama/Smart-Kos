@props([
    'title',
    'description',
    'metrics' => [],
    'columns' => [],
    'rows' => [],
])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-6">
    @foreach ($metrics as $metric)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</span>
            <div class="mt-3 flex items-end justify-between gap-3">
                <strong class="text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $metric['value'] }}</strong>
                <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-theme-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ $metric['note'] }}
                </span>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="border-b border-gray-100 px-5 py-5 dark:border-gray-800 sm:px-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    @foreach ($columns as $column)
                        <th class="px-5 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                        @foreach ($columns as $column)
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-300 sm:px-6">
                                @if (($column['key'] ?? '') === 'status')
                                    <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
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
                        <td class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6" colspan="{{ count($columns) }}">
                            Belum ada data untuk ditampilkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>