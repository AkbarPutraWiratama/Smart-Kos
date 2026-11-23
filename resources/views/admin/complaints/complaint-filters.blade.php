{{-- Filters for Admin Complaints --}}
<div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="GET" action="{{ route('admin.complaints.index') }}" class="flex flex-wrap items-end gap-3">

        {{-- Status Filter --}}
        <div class="min-w-40 flex-1 sm:flex-none">
            <label for="admin-status" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Status Aduan</label>
            <select name="status" id="admin-status" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="all" @selected($status === 'all')>Semua Status</option>
                <option value="belum" @selected($status === 'belum')>Belum Ditangani</option>
                <option value="dalam_perbaikan" @selected($status === 'dalam_perbaikan')>Dalam Perbaikan</option>
                <option value="selesai" @selected($status === 'selesai')>Selesai</option>
            </select>
        </div>

        {{-- Lokasi Filter --}}
        <div class="min-w-48 flex-1 sm:flex-none">
            <label for="admin-location_id" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Lokasi Kos</label>
            <select name="location_id" id="admin-location_id" onchange="this.form.submit()"
                class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="">Semua Lokasi Kos</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" @selected((string)$locationId === (string)$loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Pencarian --}}
        <div class="min-w-64 flex-1">
            <label for="admin-search" class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Cari Aduan / Penyewa / Kamar</label>
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    id="admin-search"
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

        {{-- Tombol Aksi --}}
        <div class="flex items-center gap-2">
            <button type="submit"
                class="inline-flex h-9 items-center rounded-lg bg-brand-500 px-4 text-xs font-medium text-white transition hover:bg-brand-600">
                Terapkan
            </button>

            @if ($status !== 'all' || $locationId || $search)
                <a href="{{ route('admin.complaints.index') }}"
                    class="inline-flex h-9 items-center rounded-lg border border-gray-200 px-3 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>
