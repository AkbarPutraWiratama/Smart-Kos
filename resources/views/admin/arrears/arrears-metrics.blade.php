<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Ringkasan tunggakan sewa">
    {{-- Total Nominal Tunggakan --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Piutang</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-error-600 dark:text-error-400 sm:text-xl">
                Rp {{ number_format($totalArrearsAmount, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Tunggakan sewa aktif</p>
        </div>
    </article>

    {{-- Jumlah Penyewa Menunggak --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Penyewa Menunggak</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90 sm:text-xl">
                {{ number_format($totalOverdueTenants, 0, ',', '.') }} <span class="text-xs font-normal text-gray-400">penyewa</span>
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Lewat jatuh tempo</p>
        </div>
    </article>

    {{-- Keterlambatan Terlama (Max Overdue) --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Terlama</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-error-600 dark:text-error-400 sm:text-xl">
                {{ $maxDaysOverdue }} <span class="text-xs font-normal text-gray-400">hari</span>
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Prioritas penagihan</p>
        </div>
    </article>

    {{-- Rata-rata Keterlambatan --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Rata-rata Terlambat</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90 sm:text-xl">
                {{ $avgDaysOverdue }} <span class="text-xs font-normal text-gray-400">hari</span>
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Rata-rata keterlambatan</p>
        </div>
    </article>
</section>
