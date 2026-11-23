<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Ringkasan kamar dan penyewa">
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Kamar</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-xl">{{ $totalRooms }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400">Unit aktif terdaftar</p>
        </div>
    </article>

    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kamar Terisi</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight text-success-600 dark:text-success-400 sm:text-xl">{{ $occupiedRooms }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400">Dihuni penyewa aktif</p>
        </div>
    </article>

    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kamar Kosong</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight text-warning-600 dark:text-warning-400 sm:text-xl">{{ $emptyRooms }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400">Siap ditawarkan</p>
        </div>
    </article>

    <a href="{{ route('admin.payments.index') }}" class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-brand-500/50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/50">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Verifikasi Pending</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight sm:text-xl {{ $pendingVerificationsCount > 0 ? 'text-blue-light-600 dark:text-blue-light-400' : 'text-gray-800 dark:text-white/90' }}">{{ $pendingVerificationsCount }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400">Transfer manual · Buka &rarr;</p>
        </div>
    </a>
</section>
