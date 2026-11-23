<div class="rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="GET" action="{{ route('admin.cash-flow.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
        {{-- Year selector --}}
        <div class="w-full sm:w-auto sm:min-w-36">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Tahun Anggaran</label>
            <select name="year" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                @foreach ($availableYears as $year)
                    <option value="{{ $year }}" @selected($selectedYear === $year)>Tahun {{ $year }}</option>
                @endforeach
            </select>
        </div>

        {{-- Location filter --}}
        <div class="w-full sm:w-auto sm:min-w-48">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Lokasi Kos</label>
            <select name="location_id" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Semua Lokasi</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" @selected((string)$selectedLocationId === (string)$loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Highlight Month selector --}}
        <div class="w-full sm:w-auto sm:min-w-44">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Sorotan Bulan</label>
            <select name="month" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                @foreach ($monthlyData as $m => $mRow)
                    <option value="{{ $m }}" @selected($selectedMonthNum === $m)>{{ $mRow['name'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Reset filter button --}}
        @if ($selectedLocationId || $selectedYear !== (int) now()->year)
            <div class="w-full sm:w-auto">
                <a href="{{ route('admin.cash-flow.index') }}"
                    class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                    Reset Filter
                </a>
            </div>
        @endif
    </form>
</div>
