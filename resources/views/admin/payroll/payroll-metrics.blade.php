<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Ringkasan penggajian dan kasbon">
    {{-- Total Gaji Bersih (Pengeluaran Riil) --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Gaji Bersih</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-error-600 dark:text-error-400 sm:text-xl">
                Rp {{ number_format($totalNet, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Pengeluaran riil gaji</p>
        </div>
    </article>

    {{-- Total Gaji Kotor --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Gaji Pokok/Kotor</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2 2 4-4M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90 sm:text-xl">
                Rp {{ number_format($totalGross, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Sebelum potongan kasbon</p>
        </div>
    </article>

    {{-- Total Potongan Kasbon --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Potongan Kasbon</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-blue-light-600 dark:text-blue-light-400 sm:text-xl">
                Rp {{ number_format($totalDeductions, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Pelunasan via payroll</p>
        </div>
    </article>

    {{-- Kasbon Belum Lunas --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kasbon Outstanding</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-warning-600 dark:text-warning-400 sm:text-xl">
                Rp {{ number_format($outstandingAdvances, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Belum dipotong/lunas</p>
        </div>
    </article>
</section>
