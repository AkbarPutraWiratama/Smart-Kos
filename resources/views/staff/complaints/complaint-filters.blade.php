{{-- Filters for Staff Complaints --}}
<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="GET" action="{{ route('staff.complaints.index') }}" class="flex flex-wrap items-end gap-3">

        {{-- Status Filter --}}
        <div class="min-w-40 flex-1 sm:flex-none">
            <label for="status" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Status Aduan</label>
            <select name="status" id="status" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="all" @selected($status === 'all')>Semua Status</option>
                <option value="open" @selected($status === 'open')>Belum Ditangani</option>
                <option value="in_progress" @selected($status === 'in_progress')>Dalam Perbaikan</option>
                <option value="resolved" @selected($status === 'resolved')>Selesai</option>
            </select>
        </div>

        {{-- Lokasi Filter --}}
        <div class="min-w-48 flex-1 sm:flex-none">
            <label for="location_id" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Lokasi Kos</label>
            <select name="location_id" id="location_id" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Semua Lokasi Kos</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" @selected((string)$locationId === (string)$loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Pencarian --}}
        <div class="min-w-64 flex-1">
            <label for="search" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Cari Aduan / Penyewa / Kamar</label>
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    id="search"
                    value="{{ $search }}"
                    placeholder="Nama penyewa, no kamar, atau judul aduan..."
                    class="h-9 w-full rounded-lg border border-gray-300 bg-white pe-3 ps-9 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Quick Filter: Prioritas Admin --}}
        <div>
            <a href="{{ route('staff.complaints.index', array_filter(['status' => $status !== 'all' ? $status : null, 'location_id' => $locationId, 'search' => $search, 'highlighted' => ($highlighted === '1' ? null : '1')])) }}"
               class="inline-flex h-9 items-center gap-1.5 rounded-lg border px-3 text-xs font-semibold transition {{ $highlighted === '1' ? 'border-error-500 bg-error-500 text-white shadow-sm hover:bg-error-600' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                <svg class="h-3.5 w-3.5 {{ $highlighted === '1' ? 'fill-current' : 'fill-none stroke-current stroke-2' }}" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span>Prioritas ({{ $stats['highlighted'] ?? 0 }})</span>
            </a>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex items-center gap-2">
            <button type="submit"
                class="inline-flex h-9 items-center rounded-lg bg-brand-500 px-4 text-xs font-medium text-white transition hover:bg-brand-600">
                Terapkan
            </button>

            @if ($status !== 'all' || $locationId || $search || $highlighted)
                <a href="{{ route('staff.complaints.index') }}"
                    class="inline-flex h-9 items-center rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>
