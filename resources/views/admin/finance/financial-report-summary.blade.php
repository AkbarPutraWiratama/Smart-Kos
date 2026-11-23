<section class="grid grid-cols-2 gap-2.5 sm:gap-4 xl:grid-cols-4" aria-label="Ringkasan laporan keuangan">
    {{-- Card 1: Total Pemasukan --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3 shadow-theme-xs sm:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">Pemasukan</p>
                <p class="mt-0.5 truncate text-[10px] text-gray-400 sm:text-xs">{{ $timeScopeLabel }}</p>
            </div>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 sm:h-9 sm:w-9 sm:rounded-xl dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-3.5 w-3.5 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0L6.5 10.5M12 5l5.5 5.5M5 20h14" />
                </svg>
            </span>
        </div>
        <p class="mt-3 truncate text-base font-bold tabular-nums text-success-600 sm:mt-4 sm:text-xl dark:text-success-400">
            Rp {{ number_format((float) $totalIncome, 0, ',', '.') }}
        </p>
    </article>

    {{-- Card 2: Total Pengeluaran --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3 shadow-theme-xs sm:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">Pengeluaran</p>
                <p class="mt-0.5 truncate text-[10px] text-gray-400 sm:text-xs">{{ $timeScopeLabel }}</p>
            </div>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 sm:h-9 sm:w-9 sm:rounded-xl dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-3.5 w-3.5 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0 5.5-5.5M12 19l-5.5-5.5M5 4h14" />
                </svg>
            </span>
        </div>
        <p class="mt-3 truncate text-base font-bold tabular-nums text-error-600 sm:mt-4 sm:text-xl dark:text-error-400">
            Rp {{ number_format((float) $totalExpense, 0, ',', '.') }}
        </p>
    </article>

    {{-- Card 3: Arus Bersih --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3 shadow-theme-xs sm:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">Arus Bersih</p>
                <p class="mt-0.5 truncate text-[10px] text-gray-400 sm:text-xs">Netto periode</p>
            </div>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 sm:h-9 sm:w-9 sm:rounded-xl dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-3.5 w-3.5 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 17l5-5 3.5 3.5L20 8m0 0h-5m5 0v5" />
                </svg>
            </span>
        </div>
        <p class="mt-3 truncate text-base font-bold tabular-nums sm:mt-4 sm:text-xl {{ $netTotal < 0 ? 'text-error-600 dark:text-error-400' : 'text-brand-600 dark:text-brand-400' }}">
            {{ $netTotal < 0 ? '− ' : '' }}Rp {{ number_format(abs($netTotal), 0, ',', '.') }}
        </p>
    </article>

    {{-- Card 4: Transaksi --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3 shadow-theme-xs sm:p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-medium text-gray-500 sm:text-sm dark:text-gray-400">Transaksi</p>
                <p class="mt-0.5 truncate text-[10px] text-gray-400 sm:text-xs">Jumlah catatan</p>
            </div>
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 sm:h-9 sm:w-9 sm:rounded-xl dark:bg-blue-light-500/15 dark:text-blue-light-400">
                <svg class="h-3.5 w-3.5 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                </svg>
            </span>
        </div>
        <p class="mt-3 text-base font-bold tabular-nums text-gray-800 sm:mt-4 sm:text-xl dark:text-white/90">
            {{ number_format($recordCount, 0, ',', '.') }} <span class="text-xs font-normal text-gray-400">trx</span>
        </p>
    </article>
</section>
