{{-- Analisis Struktur Beban vs Pendapatan --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Komposisi Pendapatan --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">
            Komposisi Sumber Pendapatan
        </h4>
        @if ($totalIncome <= 0)
            <p class="py-6 text-center text-xs text-gray-400">Belum ada transaksi pendapatan pada periode ini.</p>
        @else
            <div class="space-y-3">
                @foreach ($incomeBreakdown as $inc)
                    <div>
                        <div class="flex justify-between text-xs font-medium text-gray-600 dark:text-gray-300">
                            <span>{{ $inc['name'] }}</span>
                            <span class="font-semibold text-success-600 dark:text-success-400">
                                Rp {{ number_format($inc['amount'], 0, ',', '.') }} ({{ $inc['share'] }}%)
                            </span>
                        </div>
                        <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-success-500" style="width: {{ $inc['share'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Komposisi Beban Pengeluaran --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">
            Komposisi Beban Pengeluaran
        </h4>
        @if ($totalExpenses <= 0)
            <p class="py-6 text-center text-xs text-gray-400">Belum ada transaksi beban/pengeluaran pada periode ini.</p>
        @else
            <div class="space-y-3">
                @foreach ($expenseBreakdown as $exp)
                    <div>
                        <div class="flex justify-between text-xs font-medium text-gray-600 dark:text-gray-300">
                            <span>{{ $exp['name'] }}</span>
                            <span class="font-semibold text-error-600 dark:text-error-400">
                                Rp {{ number_format($exp['amount'], 0, ',', '.') }} ({{ $exp['share'] }}%)
                            </span>
                        </div>
                        <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-error-500" style="width: {{ $exp['share'] }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
