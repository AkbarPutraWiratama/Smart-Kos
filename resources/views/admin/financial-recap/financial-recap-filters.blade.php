<div class="rounded-2xl border border-gray-200 bg-white p-3.5 sm:p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="GET" action="{{ route('admin.financial-recap.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">

        {{-- Hidden: preserve current view mode --}}
        <input type="hidden" name="view" value="{{ $viewMode }}">

        {{-- Location filter --}}
        <div class="w-full sm:w-auto sm:min-w-48">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Filter Lokasi Kos</label>
            <select name="location_id" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Semua Lokasi Kos</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" @selected((string)$selectedLocationId === (string)$loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Search input --}}
        <div class="w-full sm:min-w-64 sm:flex-1">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                @if ($viewMode === 'tenant')
                    Cari Penyewa
                @else
                    Cari No. Kamar
                @endif
            </label>
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ $viewMode === 'tenant' ? 'Nama, email, atau nomor HP penyewa...' : 'Nomor kamar...' }}"
                    class="h-9 w-full rounded-lg border border-gray-300 bg-white pe-3 ps-9 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Submit and Reset --}}
        <div class="flex w-full items-center gap-2 pt-1 sm:w-auto sm:pt-0">
            <button type="submit"
                class="inline-flex h-9 flex-1 items-center justify-center rounded-lg bg-brand-500 px-4 text-xs font-medium text-white transition hover:bg-brand-600 sm:flex-none">
                Terapkan
            </button>

            @if ($selectedLocationId || $search)
                <a href="{{ route('admin.financial-recap.index', ['view' => $viewMode]) }}"
                    class="inline-flex h-9 flex-1 items-center justify-center rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:bg-gray-50 sm:flex-none dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>
