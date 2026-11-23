<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan metrik keuangan global">

    {{-- Total Pembayaran Diterima --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pembayaran Diterima</p>
                <p class="mt-1 text-xs text-gray-400">Akumulasi seluruh pembayaran sewa</p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums text-success-600 dark:text-success-400">
            Rp {{ number_format($globalTotalPayments, 0, ',', '.') }}
        </p>
    </article>

    {{-- Total Piutang Tertunggak --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Piutang Tertunggak</p>
                <p class="mt-1 text-xs text-gray-400">Sewa yang sudah lewat jatuh tempo</p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums text-error-600 dark:text-error-400">
            Rp {{ number_format($globalTotalOutstanding, 0, ',', '.') }}
        </p>
    </article>

    {{-- Total Beban Operasional --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Beban Operasional</p>
                <p class="mt-1 text-xs text-gray-400">Pengeluaran & gaji staf</p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums text-warning-600 dark:text-warning-400">
            Rp {{ number_format($globalTotalExpenses, 0, ',', '.') }}
        </p>
    </article>

    {{-- Net Amount --}}
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Amount</p>
                <p class="mt-1 text-xs text-gray-400">Pembayaran dikurangi beban</p>
            </div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $globalNetAmount >= 0 ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums {{ $globalNetAmount >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-400' }}">
            {{ $globalNetAmount < 0 ? '-' : '' }}Rp {{ number_format(abs($globalNetAmount), 0, ',', '.') }}
        </p>
    </article>

</section>
