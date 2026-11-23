{{-- Responsive Filter Card for Financial Report --}}
<div
    class="rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
    x-data="{
        ts: @js($timeScope),
        s: @js($scope),
    }"
>
    <form method="GET" action="{{ route('admin.finance.index') }}" class="space-y-3">
        <input type="hidden" name="time_scope" :value="ts">
        <input type="hidden" name="scope" :value="s">

        {{-- Row 1: Time Scope (Periode) --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <span class="text-xs font-semibold text-gray-600 sm:w-20 sm:shrink-0 dark:text-gray-300">Periode:</span>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Periode Pills --}}
                <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800/60">
                    @foreach (['all' => 'Semua', 'year' => 'Tahun', 'month' => 'Bulan'] as $key => $label)
                        <button
                            type="button"
                            @click="ts = '{{ $key }}'; if ('{{ $key }}' === 'all') { $nextTick(() => $el.closest('form').submit()); }"
                            class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                            :class="ts === '{{ $key }}' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Month Sub-picker --}}
                <div x-show="ts === 'month'" x-cloak class="flex items-center gap-1.5">
                    <input
                        type="month"
                        name="month"
                        value="{{ $month }}"
                        onchange="this.form.submit()"
                        class="h-7.5 rounded-lg border border-gray-300 bg-white px-2.5 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                </div>

                {{-- Year Sub-picker --}}
                <div x-show="ts === 'year'" x-cloak class="flex items-center gap-1.5">
                    <select
                        name="year"
                        onchange="this.form.submit()"
                        class="h-7.5 rounded-lg border border-gray-300 bg-white px-2.5 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        @foreach (range(now()->year, max(2020, now()->year - 5), -1) as $y)
                            <option value="{{ $y }}" @selected($year === $y)>Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Row 2: Scope (Cakupan Transaksi) --}}
        <div class="flex flex-col gap-2 border-t border-gray-100 pt-3 sm:flex-row sm:items-center dark:border-gray-800/60">
            <span class="text-xs font-semibold text-gray-600 sm:w-20 sm:shrink-0 dark:text-gray-300">Cakupan:</span>
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-2.5 w-full">
                {{-- Scope Pills --}}
                <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800/60 self-start">
                    @foreach (['all' => 'Semua', 'location' => 'Lokasi', 'room' => 'Kamar', 'person' => 'Orang'] as $sv => $slabel)
                        <button
                            type="button"
                            @click="s = '{{ $sv }}'; if ('{{ $sv }}' === 'all') { $nextTick(() => $el.closest('form').submit()); }"
                            class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                            :class="s === '{{ $sv }}' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        >
                            {{ $slabel }}
                        </button>
                    @endforeach
                </div>

                {{-- Location Select --}}
                <div x-show="s === 'location'" x-cloak class="w-full sm:w-auto">
                    <select
                        name="location_id"
                        onchange="this.closest('form').submit()"
                        class="h-7.5 w-full sm:w-48 rounded-lg border border-gray-300 bg-white px-2.5 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">Pilih lokasi...</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected($scopeValues['location_id'] === $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Room Select --}}
                <div x-show="s === 'room'" x-cloak class="w-full sm:w-auto">
                    <select
                        name="room_id"
                        onchange="this.closest('form').submit()"
                        class="h-7.5 w-full sm:w-56 rounded-lg border border-gray-300 bg-white px-2.5 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">Pilih kamar...</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected($scopeValues['room_id'] === $room->id)>
                                {{ $room->location?->name ?? 'Kos' }} · Kamar {{ $room->room_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Person Select --}}
                <div x-show="s === 'person'" x-cloak class="w-full sm:w-auto">
                    <select
                        name="person_id"
                        onchange="this.closest('form').submit()"
                        class="h-7.5 w-full sm:w-52 rounded-lg border border-gray-300 bg-white px-2.5 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">Pilih orang...</option>
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}" @selected($scopeValues['person_id'] === $person->id)>{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Row 3: Actions & Per Page --}}
        <div class="flex items-center justify-between border-t border-gray-100 pt-2.5 dark:border-gray-800/60">
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Tampilkan:</span>
                <select
                    name="per_page"
                    onchange="this.closest('form').submit()"
                    class="h-7 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="5" @selected($perPage === 5)>5 baris</option>
                    <option value="10" @selected($perPage === 10)>10 baris</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="submit"
                    x-show="s !== @js($scope) || ts !== @js($timeScope)"
                    x-cloak
                    class="inline-flex h-7 items-center rounded-md bg-brand-500 px-3 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    Terapkan
                </button>

                <a
                    href="{{ route('admin.finance.index') }}"
                    class="inline-flex h-7 items-center rounded-md border border-gray-200 px-2.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                >
                    Reset Filter
                </a>
            </div>
        </div>
    </form>
</div>
