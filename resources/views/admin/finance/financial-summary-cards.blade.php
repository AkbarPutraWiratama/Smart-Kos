<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pemasukan Bulan Ini</p>
                <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01" />
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <h3 class="text-xl font-bold text-success-600 dark:text-success-400">Rp {{ number_format((float) $totalIncomeMonth, 0, ',', '.') }}</h3>
            <p class="mt-1 text-[11px] text-gray-400">{{ (float) $totalIncomeMonth === 0.0 ? 'Belum ada catatan pemasukan' : 'Total pemasukan tercatat' }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pengeluaran Bulan Ini</p>
                <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <h3 class="text-xl font-bold text-error-600 dark:text-error-400">Rp {{ number_format((float) $totalExpenseMonth, 0, ',', '.') }}</h3>
            <p class="mt-1 text-[11px] text-gray-400">{{ (float) $totalExpenseMonth === 0.0 ? 'Belum ada catatan pengeluaran' : 'Total pengeluaran tercatat' }}</p>
        </div>
    </div>

    @php($isNegativeNet = $netIncomeMonth < 0)
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pendapatan Bersih</p>
                <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <h3 class="text-xl font-bold {{ $isNegativeNet ? 'text-error-600 dark:text-error-400' : ($netIncomeMonth > 0 ? 'text-brand-600 dark:text-brand-400' : 'text-gray-800 dark:text-white/90') }}">
                @if ($isNegativeNet) &minus; @endif
                Rp {{ number_format(abs($netIncomeMonth), 0, ',', '.') }}
            </h3>
            <p class="mt-1 text-[11px] text-gray-400">{{ $isNegativeNet ? 'Defisit bulan ini' : 'Pemasukan bersih bulan ini' }}</p>
        </div>
    </div>
</div>
