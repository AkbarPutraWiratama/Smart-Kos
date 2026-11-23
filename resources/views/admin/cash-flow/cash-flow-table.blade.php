<x-common.component-card title="Rincian Arus Kas Per Bulan (Tahun {{ $selectedYear }})" desc="Tabel ringkasan arus kas masuk, arus kas keluar, dan surplus/defisit bersih setiap bulan.">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full min-w-[700px] text-sm">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Bulan</th>
                    <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Arus Masuk (Income)</th>
                    <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Arus Keluar (Expense)</th>
                    <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Arus Bersih (Net)</th>
                    <th class="pb-3 pe-4 text-center text-xs font-semibold uppercase tracking-wide text-gray-400">Status</th>
                    <th class="pb-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($monthlyData as $m => $mRow)
                    <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.02] {{ $selectedMonthNum === $m ? 'bg-brand-50/30 dark:bg-brand-950/10' : '' }}">
                        <td class="py-3.5 pe-4 font-medium text-gray-800 dark:text-white/90 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if ($selectedMonthNum === $m)
                                    <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                                @endif
                                <span>{{ $mRow['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 pe-4 text-end font-semibold tabular-nums text-success-600 dark:text-success-400">
                            Rp {{ number_format($mRow['income'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 pe-4 text-end font-semibold tabular-nums text-error-600 dark:text-error-400">
                            Rp {{ number_format($mRow['expense'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 pe-4 text-end font-bold tabular-nums {{ $mRow['net'] > 0 ? 'text-success-600 dark:text-success-400' : ($mRow['net'] < 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-500') }}">
                            {{ $mRow['net'] > 0 ? '+' : '' }}Rp {{ number_format($mRow['net'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 pe-4 text-center">
                            @if ($mRow['status'] === 'surplus')
                                <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-950/40 dark:text-success-400">
                                    Surplus
                                </span>
                            @elseif ($mRow['status'] === 'deficit')
                                <span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-700 dark:bg-error-950/40 dark:text-error-400">
                                    Defisit
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    Seimbang
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 text-end">
                            <a href="{{ route('admin.cash-flow.index', array_merge(request()->query(), ['month' => $m])) }}"
                                class="inline-flex items-center rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-400 dark:hover:text-brand-400"
                                title="Sorot bulan {{ $mRow['name'] }}">
                                Sorot
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-200 bg-gray-50/70 font-semibold dark:border-gray-700 dark:bg-gray-800/40">
                    <td class="py-3.5 pe-4 text-gray-800 dark:text-white/90">
                        Total Tahunan ({{ $selectedYear }})
                    </td>
                    <td class="py-3.5 pe-4 text-end text-success-600 dark:text-success-400">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </td>
                    <td class="py-3.5 pe-4 text-end text-error-600 dark:text-error-400">
                        Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </td>
                    <td class="py-3.5 pe-4 text-end text-base font-bold {{ $totalNet >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                        {{ $totalNet >= 0 ? '+' : '' }}Rp {{ number_format($totalNet, 0, ',', '.') }}
                    </td>
                    <td class="py-3.5 pe-4 text-center">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                            {{ $totalNet > 0 ? 'bg-success-100 text-success-800 dark:bg-success-950/60 dark:text-success-300' : ($totalNet < 0 ? 'bg-error-100 text-error-800 dark:bg-error-950/60 dark:text-error-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200') }}">
                            {{ $totalNet > 0 ? 'Surplus Tahunan' : ($totalNet < 0 ? 'Defisit Tahunan' : 'Seimbang') }}
                        </span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-common.component-card>
