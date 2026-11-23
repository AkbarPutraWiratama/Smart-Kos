<div>
    <div class="mb-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90">Ringkasan Keuangan</h2>
            <span class="text-[11px] text-gray-400">({{ now()->translatedFormat('F Y') }})</span>
        </div>
        <a href="{{ route('admin.finance.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-500 transition hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300">
            <span>Buka Menu Keuangan</span>
            <svg class="h-3.5 w-3.5 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3 sm:gap-4">
        {{-- Card 1: Pemasukan Bulan Ini (Redirects to Laporan Keuangan) --}}
        <a href="{{ route('admin.finance.index') }}"
           class="group block rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-success-400 hover:shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-success-500/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200">Pemasukan Bulan Ini</p>
                    <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('M Y') }}</p>
                </div>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-50 text-success-600 transition group-hover:scale-105 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01" />
                    </svg>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-success-600 sm:text-xl dark:text-success-400">
                        Rp {{ number_format((float) $totalIncomeMonth, 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-gray-400">
                        {{ (float) $totalIncomeMonth === 0.0 ? 'Belum ada pemasukan' : 'Total masuk tercatat' }}
                    </p>
                </div>
                <span class="text-[11px] font-medium text-brand-500 opacity-0 transition group-hover:opacity-100 dark:text-brand-400">Detail &rarr;</span>
            </div>
        </a>

        {{-- Card 2: Pengeluaran Bulan Ini (Redirects to Pengeluaran) --}}
        <a href="{{ route('admin.expenses.index') }}"
           class="group block rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-error-400 hover:shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-error-500/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200">Pengeluaran Bulan Ini</p>
                    <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('M Y') }}</p>
                </div>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-error-50 text-error-600 transition group-hover:scale-105 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-error-600 sm:text-xl dark:text-error-400">
                        Rp {{ number_format((float) $totalExpenseMonth, 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-gray-400">
                        {{ (float) $totalExpenseMonth === 0.0 ? 'Belum ada pengeluaran' : 'Total keluar tercatat' }}
                    </p>
                </div>
                <span class="text-[11px] font-medium text-brand-500 opacity-0 transition group-hover:opacity-100 dark:text-brand-400">Detail &rarr;</span>
            </div>
        </a>

        {{-- Card 3: Pendapatan Bersih (Redirects to Laba Rugi) --}}
        @php($isNegativeNet = $netIncomeMonth < 0)
        <a href="{{ route('admin.profit-loss.index') }}"
           class="group block rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-brand-400 hover:shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/40">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200">Pendapatan Bersih</p>
                    <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('M Y') }}</p>
                </div>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:scale-105 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
            </div>
            <div class="mt-2.5 flex items-baseline justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight sm:text-xl {{ $isNegativeNet ? 'text-error-600 dark:text-error-400' : ($netIncomeMonth > 0 ? 'text-brand-600 dark:text-brand-400' : 'text-gray-800 dark:text-white/90') }}">
                        @if ($isNegativeNet) &minus; @endif
                        Rp {{ number_format(abs($netIncomeMonth), 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-gray-400">
                        {{ $isNegativeNet ? 'Defisit bulan ini' : 'Pemasukan bersih bulan ini' }}
                    </p>
                </div>
                <span class="text-[11px] font-medium text-brand-500 opacity-0 transition group-hover:opacity-100 dark:text-brand-400">Detail &rarr;</span>
            </div>
        </a>
    </div>
</div>
