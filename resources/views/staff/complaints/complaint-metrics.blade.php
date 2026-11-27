{{-- Complaint Metrics for Staff (Compact & Modern) --}}
<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Statistik Aduan Kos">
    {{-- Total Aduan --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Aduan</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90 sm:text-xl">
                {{ number_format($stats['total'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Semua laporan fasilitas</p>
        </div>
    </article>

    {{-- Menunggu Penanganan --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Belum Ditangani</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums text-warning-600 dark:text-warning-400 sm:text-xl">
                {{ number_format($stats['open'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Perlu tindak lanjut</p>
        </div>
    </article>

    {{-- Dalam Perbaikan --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Dalam Perbaikan</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums text-blue-light-600 dark:text-blue-light-400 sm:text-xl">
                {{ number_format($stats['in_progress'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Sedang Anda proses</p>
        </div>
    </article>

    {{-- Selesai --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Selesai Ditangani</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums text-success-600 dark:text-success-400 sm:text-xl">
                {{ number_format($stats['resolved'] ?? 0, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Perbaikan tuntas</p>
        </div>
    </article>
</section>
