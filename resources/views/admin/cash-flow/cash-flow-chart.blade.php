@php
    $categories = array_column($monthlyData, 'short_name');
    $incomeSeries = array_column($monthlyData, 'income');
    $expenseSeries = array_column($monthlyData, 'expense');

    $maxCompare = max($highlightData['income'], $highlightData['expense'], 1);
    $incomeRatio = $maxCompare > 0 ? min(100, round(($highlightData['income'] / $maxCompare) * 100)) : 0;
    $expenseRatio = $maxCompare > 0 ? min(100, round(($highlightData['expense'] / $maxCompare) * 100)) : 0;
@endphp

<div class="space-y-6">
    {{-- Monthly Cash Flow Comparison Bar (STEPBYSTEP.md Specification) --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 dark:border-gray-800">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Proporsi Arus Kas Bulan {{ $highlightData['name'] }} {{ $selectedYear }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Perbandingan langsung skala arus kas masuk versus kas keluar pada bulan terpilih.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold
                    {{ $highlightData['status'] === 'surplus' ? 'bg-success-50 text-success-700 dark:bg-success-950/30 dark:text-success-400' : '' }}
                    {{ $highlightData['status'] === 'deficit' ? 'bg-error-50 text-error-700 dark:bg-error-950/30 dark:text-error-400' : '' }}
                    {{ $highlightData['status'] === 'balanced' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                    @if ($highlightData['status'] === 'surplus')
                        <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                        Surplus +Rp {{ number_format($highlightData['net'], 0, ',', '.') }}
                    @elseif ($highlightData['status'] === 'deficit')
                        <span class="h-1.5 w-1.5 rounded-full bg-error-500"></span>
                        Defisit -Rp {{ number_format(abs($highlightData['net']), 0, ',', '.') }}
                    @else
                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                        Seimbang Rp 0
                    @endif
                </span>
            </div>
        </div>

        <div class="mt-5 space-y-4">
            {{-- Income Bar --}}
            <div>
                <div class="flex items-center justify-between text-xs font-medium text-gray-600 dark:text-gray-300">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block h-3 w-3 rounded bg-success-500"></span>
                        Arus Masuk (Income)
                    </span>
                    <span class="font-bold text-success-600 dark:text-success-400">
                        Rp {{ number_format($highlightData['income'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-2 h-4 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="h-full rounded-full bg-success-500 transition-all duration-500"
                        style="width: {{ $incomeRatio }}%; min-width: {{ $highlightData['income'] > 0 ? '6px' : '0' }};"></div>
                </div>
            </div>

            {{-- Expense Bar --}}
            <div>
                <div class="flex items-center justify-between text-xs font-medium text-gray-600 dark:text-gray-300">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block h-3 w-3 rounded bg-error-500"></span>
                        Arus Keluar (Expense)
                    </span>
                    <span class="font-bold text-error-600 dark:text-error-400">
                        Rp {{ number_format($highlightData['expense'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-2 h-4 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="h-full rounded-full bg-error-500 transition-all duration-500"
                        style="width: {{ $expenseRatio }}%; min-width: {{ $highlightData['expense'] > 0 ? '6px' : '0' }};"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ApexCharts 12-Month Bar Chart (TailAdmin Pattern) --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
        x-data="{
            chart: null,
            init() {
                const isDark = document.documentElement.classList.contains('dark');
                const options = {
                    series: [
                        {
                            name: 'Arus Masuk (Income)',
                            data: @js($incomeSeries)
                        },
                        {
                            name: 'Arus Keluar (Expense)',
                            data: @js($expenseSeries)
                        }
                    ],
                    colors: ['#10B981', '#EF4444'],
                    chart: {
                        fontFamily: 'Outfit, sans-serif',
                        type: 'bar',
                        height: 330,
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '45%',
                            borderRadius: 4,
                            borderRadiusApplication: 'end'
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: @js($categories),
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: {
                            style: {
                                colors: isDark ? '#9CA3AF' : '#6B7280',
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: isDark ? '#9CA3AF' : '#6B7280',
                                fontSize: '11px'
                            },
                            formatter: function (val) {
                                if (val >= 1000000) {
                                    return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
                                } else if (val >= 1000) {
                                    return 'Rp ' + (val / 1000).toFixed(0) + 'k';
                                }
                                return 'Rp ' + val;
                            }
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        horizontalAlign: 'right',
                        fontFamily: 'Outfit',
                        markers: { radius: 99 },
                        labels: {
                            colors: isDark ? '#D1D5DB' : '#374151'
                        }
                    },
                    grid: {
                        borderColor: isDark ? '#1F2937' : '#F3F4F6',
                        strokeDashArray: 4,
                        yaxis: { lines: { show: true } },
                        xaxis: { lines: { show: false } }
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: {
                            formatter: function (val) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                };

                if (window.ApexCharts && this.$refs.chartContainer) {
                    this.chart = new window.ApexCharts(this.$refs.chartContainer, options);
                    this.chart.render();
                }

                window.addEventListener('theme-changed', (e) => {
                    if (this.chart) {
                        const darkNow = e.detail?.theme === 'dark' || document.documentElement.classList.contains('dark');
                        this.chart.updateOptions({
                            tooltip: { theme: darkNow ? 'dark' : 'light' },
                            grid: { borderColor: darkNow ? '#1F2937' : '#F3F4F6' },
                            xaxis: { labels: { style: { colors: darkNow ? '#9CA3AF' : '#6B7280' } } },
                            legend: { labels: { colors: darkNow ? '#D1D5DB' : '#374151' } }
                        });
                    }
                });
            }
        }">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Tren Arus Kas 12 Bulan (Tahun {{ $selectedYear }})
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Pola perbandingan pemasukan kas dan pengeluaran operasional per bulan.
                </p>
            </div>
            <div class="flex items-center gap-4 text-xs font-medium">
                <span class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <span class="h-3 w-3 rounded-sm bg-success-500"></span>
                    Arus Masuk (Income)
                </span>
                <span class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <span class="h-3 w-3 rounded-sm bg-error-500"></span>
                    Arus Keluar (Expense)
                </span>
            </div>
        </div>

        <div class="custom-scrollbar max-w-full overflow-x-auto">
            <div x-ref="chartContainer" class="min-w-[650px]"></div>
        </div>
    </div>
</div>
