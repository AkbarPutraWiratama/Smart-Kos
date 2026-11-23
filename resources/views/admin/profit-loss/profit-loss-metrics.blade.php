<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan laba rugi">
    {{-- Total Pendapatan Usaha --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendapatan</p>
                <p class="mt-1 text-xs text-gray-400">{{ $periodLabel }}</p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums text-success-600 dark:text-success-400">
            {{ $formattedTotalIncome }}
        </p>
    </article>

    {{-- Total Beban Operasional --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Beban Operasional</p>
                <p class="mt-1 text-xs text-gray-400">Biaya kos &amp; gaji staf</p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums text-error-600 dark:text-error-400">
            {{ $formattedTotalExpenses }}
        </p>
    </article>

    {{-- Laba / (Rugi) Bersih --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $isProfit ? 'Laba Bersih' : ($isLoss ? 'Rugi Bersih' : 'Laba / Rugi Bersih') }}
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    {{ $isProfit ? 'Surplus Operasional' : ($isLoss ? 'Defisit Operasional' : 'Titik Impas (BEP)') }}
                </p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $isProfit ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400' : ($isLoss ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums {{ $isProfit ? 'text-success-600 dark:text-success-400' : ($isLoss ? 'text-error-600 dark:text-error-400' : 'text-gray-700 dark:text-gray-300') }}">
            {{ $formattedNetProfitOrLoss }}
        </p>
    </article>

    {{-- Margin Laba Bersih --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Margin Laba Bersih</p>
                <p class="mt-1 text-xs text-gray-400">Persentase dari pendapatan</p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums {{ $profitMargin > 0 ? 'text-brand-600 dark:text-brand-400' : ($profitMargin < 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-700 dark:text-gray-300') }}">
            {{ $profitMargin }}%
        </p>
    </article>
</section>
