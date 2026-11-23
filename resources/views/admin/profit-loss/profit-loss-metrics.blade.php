<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Ringkasan laba rugi">
    {{-- Total Pendapatan Usaha --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pendapatan</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums text-success-600 dark:text-success-400 sm:text-xl">{{ $formattedTotalIncome }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400">{{ $periodLabel }}</p>
        </div>
    </article>

    {{-- Total Beban Operasional --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Beban</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums text-error-600 dark:text-error-400 sm:text-xl">{{ $formattedTotalExpenses }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400">Biaya kos &amp; gaji staf</p>
        </div>
    </article>

    {{-- Laba / (Rugi) Bersih --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $isProfit ? 'Laba Bersih' : ($isLoss ? 'Rugi Bersih' : 'Laba / Rugi') }}</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $isProfit ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : ($isLoss ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums sm:text-xl {{ $isProfit ? 'text-success-600 dark:text-success-400' : ($isLoss ? 'text-error-600 dark:text-error-400' : 'text-gray-700 dark:text-gray-300') }}">{{ $formattedNetProfitOrLoss }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400">{{ $isProfit ? 'Surplus Operasional' : ($isLoss ? 'Defisit Operasional' : 'Titik Impas (BEP)') }}</p>
        </div>
    </article>

    {{-- Margin Laba Bersih --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Margin Laba Bersih</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-lg font-bold tracking-tight tabular-nums sm:text-xl {{ $profitMargin > 0 ? 'text-brand-600 dark:text-brand-400' : ($profitMargin < 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-700 dark:text-gray-300') }}">{{ $profitMargin }}%</p>
            <p class="mt-0.5 text-[11px] text-gray-400">Persentase dari pendapatan</p>
        </div>
    </article>
</section>
