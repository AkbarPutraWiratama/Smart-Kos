<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Ringkasan metrik keuangan global">

    {{-- Total Pembayaran Diterima --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Diterima</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-success-600 dark:text-success-400 sm:text-xl">
                Rp {{ number_format($globalTotalPayments, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Pembayaran sewa masuk</p>
        </div>
    </article>

    {{-- Total Piutang Tertunggak --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Piutang</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-error-600 dark:text-error-400 sm:text-xl">
                Rp {{ number_format($globalTotalOutstanding, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Sewa lewat jatuh tempo</p>
        </div>
    </article>

    {{-- Total Beban Operasional --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Beban</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums text-warning-600 dark:text-warning-400 sm:text-xl">
                Rp {{ number_format($globalTotalExpenses, 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Pengeluaran & gaji staf</p>
        </div>
    </article>

    {{-- Net Amount --}}
    <article class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Net Amount</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $globalNetAmount >= 0 ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </span>
        </div>
        <div class="mt-2.5">
            <p class="truncate text-lg font-bold tracking-tight tabular-nums sm:text-xl {{ $globalNetAmount >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-400' }}">
                {{ $globalNetAmount < 0 ? '-' : '' }}Rp {{ number_format(abs($globalNetAmount), 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-gray-400">Pembayaran bersih</p>
        </div>
    </article>

</section>
