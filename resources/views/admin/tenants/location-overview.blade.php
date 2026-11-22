<!-- Header Lokasi Kos -->
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Pilih Lokasi Kos
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Pilih atau geser kartu lokasi kos untuk memantau status hunian, tingkat okupansi, dan denah kamar.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a
            href="{{ route('admin.payments.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-theme-xs hover:border-brand-500/50 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.08] transition"
        >
            <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Verifikasi Pembayaran Manual</span>
        </a>
    </div>
</div>

<!-- Side-scrolling Location Cards -->
<div
    class="w-full max-w-full min-w-0"
    x-data="{
        canScrollLeft: false,
        canScrollRight: false,
        checkScroll() {
            const el = this.$refs.scrollContainer;
            if (!el) return;
            this.canScrollLeft = el.scrollLeft > 10;
            this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 10);
        },
        scrollLeft() {
            this.$refs.scrollContainer.scrollBy({ left: -320, behavior: 'smooth' });
        },
        scrollRight() {
            this.$refs.scrollContainer.scrollBy({ left: 320, behavior: 'smooth' });
        }
    }"
    x-init="$nextTick(() => { checkScroll(); }); window.addEventListener('resize', () => checkScroll())"
>
    <div class="relative w-full max-w-full min-w-0">
        <button
            type="button"
            x-show="canScrollLeft"
            x-cloak
            @click="scrollLeft()"
            aria-label="Scroll left"
            class="hidden sm:flex absolute -start-3 top-1/2 -translate-y-1/2 z-20 h-9 w-9 items-center justify-center rounded-full bg-white shadow-theme-md border border-gray-200 text-gray-700 hover:bg-gray-50 active:scale-95 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition"
        >
            <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            x-ref="scrollContainer"
            @scroll.debounce.50ms="checkScroll()"
            class="flex gap-3.5 sm:gap-4 overflow-x-auto overscroll-x-contain pb-3 pt-1 custom-scrollbar snap-x snap-mandatory w-full max-w-full min-w-0"
        >
            @forelse ($locations as $loc)
                @php
                    $isSelected = (string) $selectedLocationId === (string) $loc->id;
                    $cardPhoto = $loc->photos->firstWhere('type', 'location')?->path ?? $loc->photo_path ?? $loc->photos->first()?->path;
                    $activeCount = $loc->active_rooms_count ?? $loc->rooms->where('status', 'active')->count();
                    $totalCount = $loc->rooms_count ?? $loc->rooms->count();
                @endphp
                <a
                    href="{{ route('admin.tenants.index', ['location_id' => $loc->id, 'tab' => $statusTab]) }}"
                    class="group relative flex h-32 sm:h-36 w-[290px] sm:w-[340px] shrink-0 snap-start overflow-hidden rounded-2xl transition hover:-translate-y-0.5 {{ $isSelected ? 'border-2 border-brand-500 bg-brand-50/15 shadow-theme-md dark:border-brand-400 dark:bg-brand-500/10' : 'border border-gray-200 bg-white hover:border-brand-300 hover:shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]' }}"
                >
                    <div class="relative w-28 sm:w-32 shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800">
                        @if ($cardPhoto)
                            <img src="{{ asset('storage/' . $cardPhoto) }}" alt="{{ $loc->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
                        @else
                            <div class="flex h-full w-full items-center justify-center p-2 text-center text-[11px] leading-tight text-gray-400 dark:text-gray-500">
                                Belum ada foto
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col justify-between p-3 sm:p-3.5 min-w-0">
                        <div>
                            <div class="flex items-start justify-between gap-1.5">
                                <h4 class="truncate font-semibold text-sm text-gray-800 group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400">{{ $loc->name }}</h4>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium {{ $loc->status === 'active' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                    {{ $loc->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $loc->address }}</p>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 pt-1.5 dark:border-gray-800">
                            <div><span>Lantai: </span><strong class="font-semibold text-gray-800 dark:text-white/90">{{ $loc->floor_count }}</strong></div>
                            <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                            <div><span>Kamar: </span><strong class="font-semibold text-gray-800 dark:text-white/90">{{ $activeCount }}/{{ $totalCount }}</strong></div>
                        </div>
                        <div class="text-xs">
                            @if ($isSelected)
                                <span class="flex items-center gap-1 font-semibold text-brand-500 dark:text-brand-400 text-[11px] sm:text-xs">Lokasi Terpilih</span>
                            @else
                                <span class="font-medium text-gray-400 group-hover:text-brand-500 dark:text-gray-500 text-[11px] sm:text-xs">Klik untuk pilih lokasi &rarr;</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="w-full rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400">
                    Belum ada lokasi kos aktif yang terdaftar.
                </div>
            @endforelse
        </div>

        <button
            type="button"
            x-show="canScrollRight"
            x-cloak
            @click="scrollRight()"
            aria-label="Scroll right"
            class="hidden sm:flex absolute -end-3 top-1/2 -translate-y-1/2 z-20 h-9 w-9 items-center justify-center rounded-full bg-white shadow-theme-md border border-gray-200 text-gray-700 hover:bg-gray-50 active:scale-95 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition"
        >
            <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</div>

<!-- Metrics Cards -->
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Kamar</p>
        <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $metrics['total_rooms'] }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-xs font-medium text-success-600 dark:text-success-400">Kamar Terisi (Okupansi)</p>
        <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">{{ $metrics['occupied_rooms'] }} <span class="text-xs font-normal text-gray-400">({{ $metrics['occupancy_rate'] }}%)</span></p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-xs font-medium text-blue-600 dark:text-blue-400">Kamar Kosong (Tersedia)</p>
        <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $metrics['empty_rooms'] }}</p>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-xs font-medium text-warning-600 dark:text-warning-400">Penyewa Belum Ditempatkan</p>
        <p class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $metrics['unassigned_count'] }}</p>
    </div>
</div>

<!-- Navigation Tabs -->
<div class="border-b border-gray-200 dark:border-gray-800">
    <nav class="-mb-px flex gap-6" aria-label="Tabs">
        <a href="{{ route('admin.tenants.index', ['location_id' => $selectedLocationId, 'tab' => 'visualization']) }}" class="border-b-2 py-3 text-sm font-semibold transition {{ $statusTab === 'visualization' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">Visualisasi Denah Kamar</a>
        <a href="{{ route('admin.tenants.index', ['location_id' => $selectedLocationId, 'tab' => 'assigned']) }}" class="border-b-2 py-3 text-sm font-semibold transition {{ $statusTab === 'assigned' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">Penghuni Kamar ({{ $assignedTenants->total() }})</a>
        <a href="{{ route('admin.tenants.index', ['location_id' => $selectedLocationId, 'tab' => 'unassigned']) }}" class="border-b-2 py-3 text-sm font-semibold transition {{ $statusTab === 'unassigned' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">Penyewa Belum Ditempatkan ({{ $unassignedTenants->total() }})</a>
    </nav>
</div>
