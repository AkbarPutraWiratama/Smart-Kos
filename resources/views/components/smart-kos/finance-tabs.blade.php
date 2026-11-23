@props([
    'active' => 'report',
])

<div class="border-b border-gray-200 pb-2 sm:pb-0 dark:border-gray-800">
    <nav class="-mb-px flex items-center space-x-1.5 overflow-x-auto no-scrollbar scroll-smooth sm:space-x-6" aria-label="Menu Navigasi Keuangan">
        {{-- Tab 1: Laporan Keuangan & Buku Kas --}}
        <a href="{{ route('admin.finance.index') }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors sm:rounded-none sm:border-b-2 sm:px-1 sm:py-3 sm:text-sm {{ $active === 'report' ? 'bg-brand-50 text-brand-600 sm:border-brand-500 sm:bg-transparent sm:text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:sm:bg-transparent dark:sm:border-brand-400 dark:sm:text-brand-400 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 sm:border-transparent sm:hover:border-gray-300 sm:hover:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:sm:hover:border-gray-700' }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Buku Kas</span>
        </a>

        {{-- Tab 2: Pengeluaran --}}
        <a href="{{ route('admin.expenses.index') }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors sm:rounded-none sm:border-b-2 sm:px-1 sm:py-3 sm:text-sm {{ $active === 'expenses' ? 'bg-brand-50 text-brand-600 sm:border-brand-500 sm:bg-transparent sm:text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:sm:bg-transparent dark:sm:border-brand-400 dark:sm:text-brand-400 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 sm:border-transparent sm:hover:border-gray-300 sm:hover:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:sm:hover:border-gray-700' }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Pengeluaran</span>
        </a>

        {{-- Tab 3: Gaji & Kasbon --}}
        <a href="{{ route('admin.payroll.index') }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors sm:rounded-none sm:border-b-2 sm:px-1 sm:py-3 sm:text-sm {{ $active === 'payroll' ? 'bg-brand-50 text-brand-600 sm:border-brand-500 sm:bg-transparent sm:text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:sm:bg-transparent dark:sm:border-brand-400 dark:sm:text-brand-400 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 sm:border-transparent sm:hover:border-gray-300 sm:hover:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:sm:hover:border-gray-700' }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Gaji &amp; Kasbon</span>
        </a>

        {{-- Tab 4: Arus Kas --}}
        <a href="{{ route('admin.cash-flow.index') }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors sm:rounded-none sm:border-b-2 sm:px-1 sm:py-3 sm:text-sm {{ $active === 'cash-flow' ? 'bg-brand-50 text-brand-600 sm:border-brand-500 sm:bg-transparent sm:text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:sm:bg-transparent dark:sm:border-brand-400 dark:sm:text-brand-400 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 sm:border-transparent sm:hover:border-gray-300 sm:hover:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:sm:hover:border-gray-700' }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
            </svg>
            <span>Arus Kas</span>
        </a>

        {{-- Tab 5: Laba Rugi --}}
        <a href="{{ route('admin.profit-loss.index') }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors sm:rounded-none sm:border-b-2 sm:px-1 sm:py-3 sm:text-sm {{ $active === 'profit-loss' ? 'bg-brand-50 text-brand-600 sm:border-brand-500 sm:bg-transparent sm:text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:sm:bg-transparent dark:sm:border-brand-400 dark:sm:text-brand-400 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 sm:border-transparent sm:hover:border-gray-300 sm:hover:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:sm:hover:border-gray-700' }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>Laba Rugi</span>
        </a>

        {{-- Tab 6: Tunggakan & Piutang --}}
        <a href="{{ route('admin.arrears.index') }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors sm:rounded-none sm:border-b-2 sm:px-1 sm:py-3 sm:text-sm {{ $active === 'arrears' ? 'bg-brand-50 text-brand-600 sm:border-brand-500 sm:bg-transparent sm:text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:sm:bg-transparent dark:sm:border-brand-400 dark:sm:text-brand-400 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 sm:border-transparent sm:hover:border-gray-300 sm:hover:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:sm:hover:border-gray-700' }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Tunggakan</span>
        </a>

        {{-- Tab 7: Rekapitulasi --}}
        <a href="{{ route('admin.financial-recap.index') }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium whitespace-nowrap transition-colors sm:rounded-none sm:border-b-2 sm:px-1 sm:py-3 sm:text-sm {{ $active === 'recap' ? 'bg-brand-50 text-brand-600 sm:border-brand-500 sm:bg-transparent sm:text-brand-500 dark:bg-brand-500/15 dark:text-brand-400 dark:sm:bg-transparent dark:sm:border-brand-400 dark:sm:text-brand-400 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 sm:border-transparent sm:hover:border-gray-300 sm:hover:bg-transparent dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 dark:sm:hover:border-gray-700' }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Rekapitulasi</span>
        </a>
    </nav>
</div>
