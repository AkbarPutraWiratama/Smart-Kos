<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex flex-wrap items-end gap-3">
        {{-- Search --}}
        <div class="min-w-48 flex-1">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Cari judul</label>
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari pengeluaran…"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
        </div>

        {{-- Category --}}
        <div class="min-w-40">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Kategori</label>
            <select name="category_id" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Semua kategori</option>
                @foreach ($categories->groupBy('type') as $type => $cats)
                    <optgroup label="{{ ucfirst($type) }}">
                        @foreach ($cats as $cat)
                            <option value="{{ $cat->id }}" @selected((string)$categoryId === (string)$cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        {{-- Location --}}
        <div class="min-w-40">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Lokasi</label>
            <select name="location_id" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Semua lokasi</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((string)$locationId === (string)$location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Time Scope --}}
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
            <div x-data="{ ts: @js($timeScope) }">
                <input
                    type="month"
                    name="month"
                    value="{{ $month }}"
                    x-show="ts === 'month'"
                    x-cloak
                    onchange="this.form.submit()"
                    class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
            </div>
        </div>

        <button type="submit"
            class="h-9 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white transition hover:bg-brand-600">
            Terapkan
        </button>
        <a href="{{ route('admin.expenses.index') }}"
            class="inline-flex h-9 items-center rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
            Reset
        </a>
    </form>
</div>
