<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan laporan keuangan">
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pemasukan</p><p class="mt-1 text-xs text-gray-400">{{ $timeScopeLabel }}</p></div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0L6.5 10.5M12 5l5.5 5.5M5 20h14" /></svg></span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums text-success-600 dark:text-success-400">Rp {{ number_format((float) $totalIncome, 0, ',', '.') }}</p>
    </article>

    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pengeluaran</p><p class="mt-1 text-xs text-gray-400">{{ $timeScopeLabel }}</p></div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0 5.5-5.5M12 19l-5.5-5.5M5 4h14" /></svg></span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums text-error-600 dark:text-error-400">Rp {{ number_format((float) $totalExpense, 0, ',', '.') }}</p>
    </article>

    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Arus Bersih</p><p class="mt-1 text-xs text-gray-400">Pemasukan dikurangi pengeluaran</p></div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 17l5-5 3.5 3.5L20 8m0 0h-5m5 0v5" /></svg></span>
        </div>
        <p class="mt-5 truncate text-xl font-bold tabular-nums {{ $netTotal < 0 ? 'text-error-600 dark:text-error-400' : 'text-brand-600 dark:text-brand-400' }}">{{ $netTotal < 0 ? '− ' : '' }}Rp {{ number_format(abs($netTotal), 0, ',', '.') }}</p>
    </article>

    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Transaksi</p><p class="mt-1 text-xs text-gray-400">Sesuai periode dan cakupan</p></div>
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg></span>
        </div>
        <p class="mt-5 text-xl font-bold tabular-nums text-gray-800 dark:text-white/90">{{ number_format($recordCount, 0, ',', '.') }} <span class="text-sm font-medium text-gray-400">catatan</span></p>
    </article>
</section>

