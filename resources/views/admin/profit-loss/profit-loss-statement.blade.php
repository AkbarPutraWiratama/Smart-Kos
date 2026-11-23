{{-- Laporan Laba Rugi Formal (Income Statement) - STEPBYSTEP.md Specification --}}
<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Laporan Laba Rugi Operasional (Income Statement)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Periode: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $periodLabel }}</span> &bull; {{ $transactionCount }} transaksi tercatat
            </p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold
                {{ $isProfit ? 'bg-success-50 text-success-700 dark:bg-success-950/30 dark:text-success-400' : ($isLoss ? 'bg-error-50 text-error-700 dark:bg-error-950/30 dark:text-error-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300') }}">
                @if ($isProfit)
                    <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                    Laba Bersih Operasional
                @elseif ($isLoss)
                    <span class="h-1.5 w-1.5 rounded-full bg-error-500"></span>
                    Rugi Bersih Operasional
                @else
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Titik Impas (Break-Even)
                @endif
            </span>
        </div>
    </div>

    {{-- Financial Statement Structure --}}
    <div class="mt-6 space-y-6">
        {{-- Section 1: Pendapatan (Income) --}}
        <div>
            <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-700">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                    1. Pendapatan Operasional (Total Income)
                </span>
                <span class="text-sm font-bold tabular-nums text-success-600 dark:text-success-400">
                    {{ $formattedTotalIncome }}
                </span>
            </div>
            <ul class="divide-y divide-gray-50 dark:divide-gray-800/60 pt-1 text-sm">
                @foreach ($incomeBreakdown as $incItem)
                    <li class="flex items-center justify-between py-2 ps-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                            {{ $incItem['name'] }}
                        </span>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400">({{ $incItem['share'] }}%)</span>
                            <span class="font-medium tabular-nums text-gray-800 dark:text-white/90">
                                Rp {{ number_format($incItem['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Section 2: Beban / Pengeluaran (Expenses) --}}
        <div>
            <div class="flex items-center justify-between border-b border-gray-200 pb-2 dark:border-gray-700">
                <span class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                    2. Beban &amp; Biaya Operasional (Total Expenses)
                </span>
                <span class="text-sm font-bold tabular-nums text-error-600 dark:text-error-400">
                    {{ $formattedTotalExpenses }}
                </span>
            </div>
            <ul class="divide-y divide-gray-50 dark:divide-gray-800/60 pt-1 text-sm">
                @foreach ($expenseBreakdown as $expItem)
                    <li class="flex items-center justify-between py-2 ps-4 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        <span class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-error-500"></span>
                            {{ $expItem['name'] }}
                        </span>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400">({{ $expItem['share'] }}%)</span>
                            <span class="font-medium tabular-nums text-gray-800 dark:text-white/90">
                                Rp {{ number_format($expItem['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Section 3: Summary Mathematical Calculation (Matching STEPBYSTEP.md Example) --}}
        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/40">
            <div class="space-y-2 text-sm font-mono">
                <div class="flex justify-between text-gray-700 dark:text-gray-300">
                    <span>Pendapatan (Income)</span>
                    <span class="font-semibold text-success-600 dark:text-success-400">{{ $formattedTotalIncome }}</span>
                </div>
                <div class="flex justify-between text-gray-700 dark:text-gray-300">
                    <span>Beban (Expenses)</span>
                    <span class="font-semibold text-error-600 dark:text-error-400">-{{ $formattedTotalExpenses }}</span>
                </div>
                <div class="border-t-2 border-gray-300 dark:border-gray-600 pt-2 flex justify-between text-base font-bold">
                    <span class="text-gray-800 dark:text-white/90">
                        {{ $isProfit ? 'Laba Bersih (Net Income)' : ($isLoss ? 'Rugi Bersih (Net Loss)' : 'Titik Impas (Net Income)') }}
                    </span>
                    <span class="{{ $isProfit ? 'text-success-600 dark:text-success-400' : ($isLoss ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90') }}">
                        {{ $formattedNetProfitOrLoss }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
