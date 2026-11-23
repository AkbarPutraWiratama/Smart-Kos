<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="GET" action="{{ route('admin.payroll.index') }}" class="flex flex-wrap items-end gap-3">
        {{-- Staff selector --}}
        <div class="min-w-48 flex-1 sm:flex-none">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Pilih Staf</label>
            <select name="staff_id" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Semua Staf</option>
                @foreach ($staffList as $staff)
                    <option value="{{ $staff->id }}" @selected((string)$selectedStaffId === (string)$staff->id)>{{ $staff->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Time Scope selector --}}
        <div x-data="{ ts: @js($timeScope) }" class="flex items-end gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Periode</label>
                <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800/60">
                    @foreach (['month' => 'Bulan', 'year' => 'Tahun', 'all' => 'Semua'] as $sv => $slabel)
                        <button type="button"
                            @click="ts = '{{ $sv }}'; if ('{{ $sv }}' === 'all') { $nextTick(() => $el.closest('form').submit()); }"
                            class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                            :class="ts === '{{ $sv }}' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                        >{{ $slabel }}</button>
                    @endforeach
                    <input type="hidden" name="time_scope" :value="ts">
                </div>
            </div>

            {{-- Month picker --}}
            <div x-show="ts === 'month'" x-cloak>
                <input
                    type="month"
                    name="month"
                    value="{{ $month }}"
                    onchange="this.form.submit()"
                    class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
            </div>

            {{-- Year picker --}}
            <div x-show="ts === 'year'" x-cloak>
                <select name="year" onchange="this.form.submit()"
                    class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @foreach (range(now()->year, max(2020, now()->year - 5), -1) as $y)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="submit"
            class="h-9 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white transition hover:bg-brand-600">
            Terapkan
        </button>

        <a href="{{ route('admin.payroll.index') }}"
            class="inline-flex h-9 items-center rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
            Reset
        </a>
    </form>
</div>
