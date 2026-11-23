<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Ringkasan arus kas">
    {{-- Total Arus Kas Masuk --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Arus Masuk</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-success-600 dark:text-success-400 sm:text-xl">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Pemasukan tahun {{ $selectedYear }}</p>
        </div>
    </article>

    {{-- Total Arus Kas Keluar --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Arus Keluar</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-error-600 dark:text-error-400 sm:text-xl">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Pengeluaran tahun {{ $selectedYear }}</p>
        </div>
    </article>

    {{-- Arus Kas Bersih (Net Cash Flow) --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Arus Kas Bersih</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $totalNet >= 0 ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums sm:text-xl {{ $totalNet >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                {{ $totalNet >= 0 ? '+' : '' }}Rp {{ number_format($totalNet, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">{{ $totalNet >= 0 ? 'Surplus Kas' : 'Defisit Kas' }}</p>
        </div>
    </article>

    {{-- Rata-rata Bulanan --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Rata-rata Bulanan</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums sm:text-xl {{ $avgMonthlyNet >= 0 ? 'text-gray-800 dark:text-white/90' : 'text-error-600 dark:text-error-400' }}">
                {{ $avgMonthlyNet >= 0 ? '+' : '' }}Rp {{ number_format($avgMonthlyNet, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Bulan aktif transaksi</p>
        </div>
    </article>
</section>
