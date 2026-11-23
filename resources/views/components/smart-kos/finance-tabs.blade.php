@props([
    'active' => 'report',
])

<div class="border-b border-gray-200 dark:border-gray-800">
    <nav class="-mb-px flex space-x-2 sm:space-x-6 overflow-x-auto no-scrollbar" aria-label="Financial Tabs">
        {{-- Tab 1: Laporan Keuangan & Buku Kas --}}
        <a href="{{ route('admin.finance.index') }}"
           class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'report' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-200' }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Laporan Keuangan & Buku Kas</span>
        </a>

        {{-- Tab 2: Pengeluaran --}}
        <a href="{{ route('admin.expenses.index') }}"
           class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'expenses' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-200' }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Pengeluaran</span>
        </a>

        {{-- Tab 3: Gaji & Kasbon --}}
        <a href="{{ route('admin.payroll.index') }}"
           class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'payroll' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-200' }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Gaji &amp; Kasbon</span>
        </a>

        {{-- Tab 4: Arus Kas (Phase 31) --}}
        <a href="{{ route('admin.cash-flow.index') }}"
           class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'cash-flow' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-200' }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
            </svg>
            <span>Arus Kas</span>
        </a>

        {{-- Tab 5: Laba Rugi (Phase 32) --}}
        <a href="{{ route('admin.profit-loss.index') }}"
           class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium whitespace-nowrap transition-colors {{ $active === 'profit-loss' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400 font-semibold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-200' }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>Laba Rugi</span>
        </a>

        {{-- Tab 6: Tunggakan (Upcoming Phase 33) --}}
        <span class="inline-flex items-center gap-2 border-b-2 border-transparent py-3 px-1 text-sm font-medium text-gray-400 dark:text-gray-600 cursor-not-allowed whitespace-nowrap"
              title="Akan hadir di Phase 33 (Tenant Receivables / Arrears)">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Tunggakan</span>
            <span class="rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-[10px] font-semibold text-gray-400 dark:text-gray-500">Segera</span>
        </span>
    </nav>
</div>
