{{-- Time-scope pill tabs + scope filter row --}}
<div
    class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
    x-data="{
        timeScope: @js($timeScope),
        scope: @js($scope),
        month: @js($month),
        year: @js($year),
    }"
>
    {{-- Left: time-scope pills --}}
    <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800/60">
        @foreach (['all' => 'Semua', 'year' => 'Tahun', 'month' => 'Bulan'] as $ts => $label)
            <a
                href="{{ route('admin.finance.index', array_merge(request()->except(['time_scope', 'year', 'month', 'report_page', 'va_page', 'manual_page']), ['time_scope' => $ts])) }}"
                class="rounded-md px-3 py-1 text-xs font-medium transition-all
                    {{ $timeScope === $ts
                        ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white/90'
                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >{{ $label }}</a>
        @endforeach
    </div>

    {{-- Middle: year/month sub-selector (shown only when relevant) --}}
    <form method="GET" action="{{ route('admin.finance.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="hidden" name="time_scope" value="{{ $timeScope }}">
        <input type="hidden" name="scope" value="{{ $scope }}">
        @if ($scope === 'location') <input type="hidden" name="location_id" value="{{ $scopeValues['location_id'] }}"> @endif
        @if ($scope === 'room') <input type="hidden" name="room_id" value="{{ $scopeValues['room_id'] }}"> @endif
        @if ($scope === 'person') <input type="hidden" name="person_id" value="{{ $scopeValues['person_id'] }}"> @endif
        <input type="hidden" name="per_page" value="{{ $perPage }}">

        @if ($timeScope === 'month')
            <input
                type="month"
                name="month"
                value="{{ $month }}"
                onchange="this.form.submit()"
                class="h-7 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
        @elseif ($timeScope === 'year')
            <select
                name="year"
                onchange="this.form.submit()"
                class="h-7 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                @foreach (range(now()->year, max(2020, now()->year - 5), -1) as $y)
                    <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                @endforeach
            </select>
        @endif
    </form>

    {{-- Right: scope (all/location/room/person) + per-page as compact inline form --}}
    <form method="GET" action="{{ route('admin.finance.index') }}" class="flex flex-wrap items-center gap-2">
        <input type="hidden" name="time_scope" value="{{ $timeScope }}">
        @if ($timeScope === 'month') <input type="hidden" name="month" value="{{ $month }}"> @endif
        @if ($timeScope === 'year') <input type="hidden" name="year" value="{{ $year }}"> @endif

        {{-- Scope selector --}}
        <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800/60" x-data="{ s: @js($scope) }">
            @foreach (['all' => 'Semua', 'location' => 'Lokasi', 'room' => 'Kamar', 'person' => 'Orang'] as $sv => $slabel)
                <button
                    type="{{ $sv === 'all' ? 'button' : 'button' }}"
                    @click="s = '{{ $sv }}'"
                    class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                    :class="s === '{{ $sv }}' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white/90' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                >{{ $slabel }}</button>
            @endforeach
            <input type="hidden" name="scope" :value="s">
        </div>

        {{-- Conditional sub-selectors --}}
        <div x-data="{ s: @js($scope) }" class="flex items-center gap-2">
            {{-- Location --}}
            <select
                name="location_id"
                x-show="s === 'location'"
                x-cloak
                onchange="this.closest('form').submit()"
                class="h-7 max-w-36 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                <option value="">Pilih lokasi</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected($scopeValues['location_id'] === $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>

            {{-- Room --}}
            <select
                name="room_id"
                x-show="s === 'room'"
                x-cloak
                onchange="this.closest('form').submit()"
                class="h-7 max-w-44 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                <option value="">Pilih kamar</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" @selected($scopeValues['room_id'] === $room->id)>{{ $room->location?->name ?? 'Lokasi' }} · {{ $room->room_number }}</option>
                @endforeach
            </select>

            {{-- Person --}}
            <select
                name="person_id"
                x-show="s === 'person'"
                x-cloak
                onchange="this.closest('form').submit()"
                class="h-7 max-w-44 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                <option value="">Pilih orang</option>
                @foreach ($people as $person)
                    <option value="{{ $person->id }}" @selected($scopeValues['person_id'] === $person->id)>{{ $person->name }}</option>
                @endforeach
            </select>

            {{-- Submit when scope button changes --}}
            <button
                type="submit"
                x-show="s !== @js($scope)"
                x-cloak
                class="inline-flex h-7 items-center rounded-md bg-brand-500 px-3 text-xs font-semibold text-white transition hover:bg-brand-600"
            >Terapkan</button>
        </div>

        {{-- Per page --}}
        <select
            name="per_page"
            onchange="this.closest('form').submit()"
            class="h-7 rounded-md border border-gray-300 bg-white px-2 text-xs text-gray-700 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
        >
            <option value="5" @selected($perPage === 5)>5 baris</option>
            <option value="10" @selected($perPage === 10)>10 baris</option>
        </select>

        {{-- Reset --}}
        <a
            href="{{ route('admin.finance.index') }}"
            class="inline-flex h-7 items-center rounded-md border border-gray-300 px-2.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
        >Reset</a>
    </form>
</div>
