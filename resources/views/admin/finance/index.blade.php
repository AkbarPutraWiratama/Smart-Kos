@extends('layouts.app')

@section('content')
<div
    x-data="{
        activeTab: '{{ request('tab', $activeTab ?? 'overview') }}'
    }"
    class="space-y-6 w-full max-w-full min-w-0"
>
    {{-- Breadcrumb --}}
    <x-common.page-breadcrumb pageTitle="Laporan Keuangan &amp; Arus Kas" />

    {{-- Filter Bar --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs lg:flex-row lg:items-center lg:justify-between dark:border-gray-800 dark:bg-white/[0.03]">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Pusat Pelaporan Keuangan &amp; Arus Kas Smart Kos
            </h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Laporan komprehensif arus kas, laba/rugi, rekapitulasi performa per kamar, rincian per penyewa, serta buku catatan transaksi keuangan.
            </p>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.finance.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="type" value="{{ $selectedType }}">
            <input type="hidden" name="tab" :value="activeTab">

            {{-- Location Filter --}}
            <select
                name="location_id"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all">Semua Lokasi Kos</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" {{ (string)$selectedLocationId === (string)$loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>

            {{-- Month Filter --}}
            <select
                name="month"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Sepanjang Tahun (12 Bulan)</option>
                @foreach ($monthsNames as $mNum => $mLabel)
                    <option value="{{ $mNum }}" {{ (string)$selectedMonth === (string)$mNum ? 'selected' : '' }}>
                        Bulan {{ $mLabel }}
                    </option>
                @endforeach
            </select>

            {{-- Year Filter --}}
            <select
                name="year"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                @for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>
                        Tahun {{ $y }}
                    </option>
                @endfor
            </select>

            @if ($selectedLocationId !== 'all' || $selectedMonth !== (string)date('n') || $selectedYear !== (int)date('Y') || !empty($search) || $selectedType !== 'all')
                <a
                    href="{{ route('admin.finance.index', ['tab' => request('tab', 'overview')]) }}"
                    title="Reset Filter"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif
        </form>
    </div>

    {{-- ── 1. Summary Metric Cards (P&L & Cash Flow) ── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Income --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pemasukan</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-success-600 dark:text-success-400">
                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    {{ $selectedMonth !== 'all' ? 'Bulan ' . ($monthsNames[(int)$selectedMonth] ?? '') : 'Tahun ' . $selectedYear }}
                </p>
            </div>
        </div>

        {{-- Total Expense --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pengeluaran</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-error-600 dark:text-error-400">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    Operasional &amp; Pemeliharaan
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.expenses.index') }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-error-600 hover:underline dark:text-error-400">
                        <span>Kelola Pengeluaran &rarr;</span>
                    </a>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <a href="{{ route('admin.payroll.index') }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 hover:underline dark:text-brand-400">
                        <span>Gaji &amp; Kasbon &rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Net Profit / Loss --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Laba / Rugi Bersih (P&amp;L)</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $netProfit >= 0 ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-400' }}">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    {{ $netProfit >= 0 ? 'Surplus Operasional' : 'Defisit Operasional' }}
                </p>
            </div>
        </div>

        {{-- Total Records Count --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Transaksi</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $totalTransactions }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">Data finansial tercatat</p>
            </div>
        </div>
    </div>

    {{-- ── 2. Multi-Tab Navigation (Overview, Room Recap, Tenant Recap, Records) ── --}}
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex flex-wrap gap-4 sm:gap-6" aria-label="Tabs">
            {{-- Tab 1: Overview (Cash Flow & Location) --}}
            <button
                type="button"
                @click="activeTab = 'overview'"
                class="border-b-2 py-3 text-xs sm:text-sm font-semibold transition inline-flex items-center gap-2"
                :class="activeTab === 'overview' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Ringkasan Arus Kas</span>
            </button>

            {{-- Tab 2: Full Room Recap --}}
            <button
                type="button"
                @click="activeTab = 'rooms'"
                class="border-b-2 py-3 text-xs sm:text-sm font-semibold transition inline-flex items-center gap-2"
                :class="activeTab === 'rooms' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Rekap per Kamar ({{ $roomSummary['total_rooms'] }})</span>
            </button>

            {{-- Tab 3: Tenant Recap (Per Orang) --}}
            <button
                type="button"
                @click="activeTab = 'tenants'"
                class="border-b-2 py-3 text-xs sm:text-sm font-semibold transition inline-flex items-center gap-2"
                :class="activeTab === 'tenants' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Rekap per Penyewa ({{ $tenantSummary['total_tenants'] }})</span>
            </button>

            {{-- Tab 4: Financial Transaction Records --}}
            <button
                type="button"
                @click="activeTab = 'records'"
                class="border-b-2 py-3 text-xs sm:text-sm font-semibold transition inline-flex items-center gap-2"
                :class="activeTab === 'records' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Buku Catatan Keuangan ({{ $totalTransactions }})</span>
            </button>
        </nav>
    </div>

    {{-- ── 3. Tab 1: OVERVIEW (Monthly Cash Flow Visualization & Location Breakdown) ── --}}
    <div
        x-show="activeTab === 'overview'"
        x-data="{
            cashFlowChart: null,
            initCashFlowChart() {
                if (typeof ApexCharts === 'undefined') return;

                if (this.cashFlowChart) {
                    this.cashFlowChart.destroy();
                    this.cashFlowChart = null;
                }

                const categories = @json(array_values($chartCategories));
                const incomeData = @json($chartIncome);
                const expenseData = @json($chartExpense);

                const isDark = document.documentElement.classList.contains('dark');

                const options = {
                    series: [
                        {
                            name: 'Pemasukan (Income)',
                            data: incomeData,
                        },
                        {
                            name: 'Pengeluaran (Expense)',
                            data: expenseData,
                        }
                    ],
                    colors: ['#10B981', '#F43F5E'],
                    chart: {
                        fontFamily: 'Outfit, sans-serif',
                        type: 'bar',
                        height: 310,
                        toolbar: {
                            show: false,
                        },
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '46%',
                            borderRadius: 6,
                            borderRadiusApplication: 'end',
                        },
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        show: true,
                        width: 3,
                        colors: ['transparent'],
                    },
                    xaxis: {
                        categories: categories,
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        },
                        labels: {
                            style: {
                                colors: isDark ? '#9CA3AF' : '#6B7280',
                                fontSize: '11px',
                                fontFamily: 'Outfit, sans-serif',
                            },
                        },
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: isDark ? '#9CA3AF' : '#6B7280',
                                fontSize: '11px',
                                fontFamily: 'Outfit, sans-serif',
                            },
                            formatter: function (val) {
                                if (val >= 1000000000) {
                                    return 'Rp ' + (val / 1000000000).toFixed(1) + ' M';
                                }
                                if (val >= 1000000) {
                                    return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
                                }
                                if (val >= 1000) {
                                    return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
                                }
                                return 'Rp ' + val;
                            },
                        },
                    },
                    grid: {
                        borderColor: isDark ? '#1F2937' : '#F3F4F6',
                        strokeDashArray: 4,
                        yaxis: {
                            lines: {
                                show: true,
                            },
                        },
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        horizontalAlign: 'right',
                        fontFamily: 'Outfit, sans-serif',
                        fontSize: '12px',
                        markers: {
                            radius: 99,
                        },
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: {
                            formatter: function (val) {
                                return 'Rp ' + Number(val).toLocaleString('id-ID');
                            },
                        },
                    },
                };

                const el = document.querySelector('#cashFlowMonthlyChart');
                if (el) {
                    this.cashFlowChart = new ApexCharts(el, options);
                    this.cashFlowChart.render();
                }
            }
        }"
        x-init="
            $nextTick(() => initCashFlowChart());
            $watch('activeTab', value => {
                if (value === 'overview') {
                    $nextTick(() => initCashFlowChart());
                }
            });
            window.addEventListener('theme-changed', () => {
                if (activeTab === 'overview') {
                    $nextTick(() => initCashFlowChart());
                }
            });
        "
        class="space-y-6"
    >
        {{-- ── 3A. Visualisasi Arus Kas Bulanan (Chart + Progress Proportion Bars) ── --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- ApexCharts 12-Month Bar Chart --}}
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </span>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white/90">
                                Visualisasi Arus Kas Bulanan (Monthly Cash Flow)
                            </h4>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Perbandingan Pemasukan (Income) vs Pengeluaran (Expense) sepanjang tahun {{ $selectedYear }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 font-medium text-success-600 dark:text-success-400">
                            <span class="h-2.5 w-2.5 rounded-full bg-success-500"></span>
                            Income
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-error-600 dark:text-error-400">
                            <span class="h-2.5 w-2.5 rounded-full bg-error-500"></span>
                            Expense
                        </span>
                    </div>
                </div>

                {{-- Chart Container --}}
                <div class="custom-scrollbar max-w-full overflow-x-auto">
                    <div id="cashFlowMonthlyChart" class="min-w-[650px] xl:min-w-full"></div>
                </div>
            </div>

            {{-- Proportion & Ratio Panel (ASCII-inspired Visual Bar Display) --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] flex flex-col justify-between">
                <div>
                    <div class="pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white/90">
                            Rasio Arus Kas (Cash Flow Proportion)
                        </h4>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Perbandingan proporsi kas masuk vs kas keluar
                        </p>
                    </div>

                    {{-- Visual Proportion Bars (StepByStep Visual Specification) --}}
                    <div class="mt-4 space-y-4">
                        {{-- Income Proportion Bar --}}
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="flex items-center gap-1.5 text-success-600 dark:text-success-400">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                    </svg>
                                    Pemasukan (Income)
                                </span>
                                <span class="text-gray-900 dark:text-white font-bold">
                                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                    <span class="text-success-600 dark:text-success-400">({{ $cashFlowSummary['income_percentage'] }}%)</span>
                                </span>
                            </div>
                            <div class="h-3.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden p-0.5">
                                <div
                                    class="h-full rounded-full bg-success-500 transition-all duration-500"
                                    style="width: {{ max($cashFlowSummary['income_percentage'], $totalIncome > 0 ? 5 : 0) }}%"
                                ></div>
                            </div>
                        </div>

                        {{-- Expense Proportion Bar --}}
                        <div>
                            <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                <span class="flex items-center gap-1.5 text-error-600 dark:text-error-400">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                    </svg>
                                    Pengeluaran (Expense)
                                </span>
                                <span class="text-gray-900 dark:text-white font-bold">
                                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                                    <span class="text-error-600 dark:text-error-400">({{ $cashFlowSummary['expense_percentage'] }}%)</span>
                                </span>
                            </div>
                            <div class="h-3.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden p-0.5">
                                <div
                                    class="h-full rounded-full bg-error-500 transition-all duration-500"
                                    style="width: {{ max($cashFlowSummary['expense_percentage'], $totalExpense > 0 ? 5 : 0) }}%"
                                ></div>
                            </div>
                        </div>

                        {{-- Net Cash Flow Highlight --}}
                        <div class="rounded-xl border p-3.5 {{ $netProfit >= 0 ? 'border-success-200 bg-success-50/50 dark:border-success-500/20 dark:bg-success-500/10' : 'border-error-200 bg-error-50/50 dark:border-error-500/20 dark:bg-error-500/10' }}">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium {{ $netProfit >= 0 ? 'text-success-800 dark:text-success-300' : 'text-error-800 dark:text-error-300' }}">
                                    Arus Kas Bersih (Net Flow):
                                </span>
                                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $netProfit >= 0 ? 'bg-success-100 text-success-800 dark:bg-success-500/20 dark:text-success-300' : 'bg-error-100 text-error-800 dark:bg-error-500/20 dark:text-error-300' }}">
                                    {{ $netProfit >= 0 ? 'Surplus Operasional' : 'Defisit Operasional' }}
                                </span>
                            </div>
                            <p class="text-lg font-bold mt-1.5 {{ $netProfit >= 0 ? 'text-success-700 dark:text-success-400' : 'text-error-700 dark:text-error-400' }}">
                                Rp {{ number_format($netProfit, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Additional Key Cash Flow Statistics --}}
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-2 text-[11px]">
                    <div>
                        <span class="text-gray-400 block">Rata-rata Pemasukan:</span>
                        <span class="font-bold text-gray-800 dark:text-white/90">
                            Rp {{ number_format($cashFlowSummary['avg_monthly_income'], 0, ',', '.') }}/bln
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Rata-rata Pengeluaran:</span>
                        <span class="font-bold text-gray-800 dark:text-white/90">
                            Rp {{ number_format($cashFlowSummary['avg_monthly_expense'], 0, ',', '.') }}/bln
                        </span>
                    </div>
                    <div class="pt-1.5">
                        <span class="text-gray-400 block">Puncak Pemasukan:</span>
                        <span class="font-medium text-success-600 dark:text-success-400">
                            {{ $cashFlowSummary['peak_income_month'] }}
                        </span>
                    </div>
                    <div class="pt-1.5">
                        <span class="text-gray-400 block">Puncak Pengeluaran:</span>
                        <span class="font-medium text-error-600 dark:text-error-400">
                            {{ $cashFlowSummary['peak_expense_month'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3B. Monthly Breakdown Table (12 Months) & Location Breakdown ── --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Monthly Breakdown Table (12 Months) --}}
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
                <div class="border-b border-gray-100 p-5 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white/90">
                            Tabel Rincian Arus Kas Bulanan — Tahun {{ $selectedYear }}
                        </h4>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Pergerakan kas masuk dan kas keluar per bulan
                        </p>
                    </div>
                    <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        12 Bulan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start text-xs">
                        <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3 px-4 text-start font-semibold">Bulan</th>
                                <th class="py-3 px-4 text-end font-semibold text-success-600 dark:text-success-400">Pemasukan (+)</th>
                                <th class="py-3 px-4 text-end font-semibold text-error-600 dark:text-error-400">Pengeluaran (-)</th>
                                <th class="py-3 px-4 text-end font-semibold">Arus Kas Bersih</th>
                                <th class="py-3 px-4 text-center font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                            @foreach ($monthlyReport as $m)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition {{ $selectedMonth == $m['month_number'] ? 'bg-brand-50/20 dark:bg-brand-500/5' : '' }}">
                                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                        {{ $m['month_name'] }}
                                        @if ($selectedMonth == $m['month_number'])
                                            <span class="ms-1 rounded bg-brand-100 px-1.5 py-0.2 text-[10px] font-bold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">Terpilih</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-end font-medium text-success-600 dark:text-success-400">
                                        Rp {{ number_format($m['income'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-end font-medium text-error-600 dark:text-error-400">
                                        Rp {{ number_format($m['expense'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-end font-bold {{ $m['net'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-error-600 dark:text-error-400' }}">
                                        Rp {{ number_format($m['net'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $m['net'] > 0 ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : ($m['net'] < 0 ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300') }}">
                                            {{ $m['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Location Breakdown (P&L per Location) --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden flex flex-col">
                <div class="border-b border-gray-100 p-5 dark:border-gray-800">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-white/90">
                        Kinerja Finansial per Lokasi Kos
                    </h4>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Kontribusi pendapatan dan laba bersih tiap lokasi
                    </p>
                </div>

                <div class="p-5 space-y-4 flex-1 overflow-y-auto">
                    @forelse ($locationReport as $lr)
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02] space-y-2">
                            <div class="flex items-center justify-between">
                                <h5 class="font-bold text-sm text-gray-900 dark:text-white">
                                    {{ $lr['location_name'] }}
                                </h5>
                                <span class="rounded-lg bg-brand-50 px-2 py-0.5 text-[11px] font-bold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                    {{ $lr['contribution'] }}% Pemasukan
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <span class="text-gray-400 text-[11px]">Pemasukan:</span>
                                    <p class="font-semibold text-success-600 dark:text-success-400">
                                        Rp {{ number_format($lr['income'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-[11px]">Pengeluaran:</span>
                                    <p class="font-semibold text-error-600 dark:text-error-400">
                                        Rp {{ number_format($lr['expense'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center text-xs pt-1 border-t border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500 dark:text-gray-400">Laba Bersih Lokasi:</span>
                                <span class="font-bold {{ $lr['net'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-error-600 dark:text-error-400' }}">
                                    Rp {{ number_format($lr['net'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-gray-400">
                            Belum ada lokasi kos aktif yang terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. Tab 2: ROOM FINANCIAL RECAP (Full Kamar) ── --}}
    <div x-show="activeTab === 'rooms'" class="space-y-4">
        {{-- Room Metrics Summary Banner --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Total Kamar</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $roomSummary['total_rooms'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Kamar Terisi</span>
                <p class="text-lg font-bold text-brand-600 dark:text-brand-400 mt-1">{{ $roomSummary['occupied_rooms'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Kamar Kosong</span>
                <p class="text-lg font-bold text-gray-500 dark:text-gray-400 mt-1">{{ $roomSummary['empty_rooms'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Pendapatan Periode</span>
                <p class="text-sm font-bold text-success-600 dark:text-success-400 mt-1.5">Rp {{ number_format($roomSummary['period_income'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Pendapatan Sepanjang Masa</span>
                <p class="text-sm font-bold text-success-700 dark:text-success-300 mt-1.5">Rp {{ number_format($roomSummary['total_income'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Tunggakan Periode Ini</span>
                <p class="text-sm font-bold {{ $roomSummary['total_arrears'] > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-400' }} mt-1.5">
                    Rp {{ number_format($roomSummary['total_arrears'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Room Table Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h4 class="text-sm font-bold text-gray-800 dark:text-white/90">
                        Rincian Rekapitulasi Finansial per Kamar (Full Kamar)
                    </h4>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Monitoring pendapatan, status sewa, dan keterlambatan pembayaran seluruh kamar
                    </p>
                </div>
                <form method="GET" action="{{ route('admin.finance.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="tab" value="rooms">

                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari no kamar / lokasi / penyewa..."
                            class="h-9 w-full rounded-xl border border-gray-200 bg-white px-3 pe-8 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                        />
                        <button type="submit" class="absolute end-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 text-start font-semibold">Kamar &amp; Lantai</th>
                            <th class="py-3 px-4 text-start font-semibold">Lokasi Kos</th>
                            <th class="py-3 px-4 text-start font-semibold">Status Hunian &amp; Penyewa</th>
                            <th class="py-3 px-4 text-end font-semibold">Tarif Sewa</th>
                            <th class="py-3 px-4 text-end font-semibold text-success-600 dark:text-success-400">Pemasukan Periode</th>
                            <th class="py-3 px-4 text-end font-semibold">Total Pemasukan (All-Time)</th>
                            <th class="py-3 px-4 text-center font-semibold">Status Tagihan</th>
                            <th class="py-3 px-4 text-end font-semibold">Tunggakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse ($roomReport as $rr)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                                <td class="py-3 px-4 whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-700 font-bold text-xs dark:bg-brand-500/15 dark:text-brand-300">
                                            {{ $rr['room_number'] }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Lantai {{ $rr['floor_name'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $rr['location_name'] }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($rr['is_occupied'])
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $rr['tenant_name'] }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $rr['tenant_phone'] }}</div>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Kamar Kosong
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                                    Rp {{ number_format($rr['rent_amount'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-end whitespace-nowrap font-bold text-success-600 dark:text-success-400">
                                    Rp {{ number_format($rr['total_income_period'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-end whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($rr['total_income_all'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if ($rr['payment_status'] === 'Lunas')
                                        <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                            Lunas
                                        </span>
                                    @elseif ($rr['payment_status'] === 'Menunggu Verifikasi')
                                        <span class="inline-flex rounded-full bg-warning-50 px-2 py-0.5 text-[10px] font-bold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                            Verifikasi
                                        </span>
                                    @elseif ($rr['payment_status'] === 'Belum Bayar')
                                        <span class="inline-flex rounded-full bg-error-50 px-2 py-0.5 text-[10px] font-bold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                            Belum Bayar
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                            Kosong
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end whitespace-nowrap font-bold">
                                    @if ($rr['outstanding'] > 0)
                                        <span class="text-error-600 dark:text-error-400">
                                            Rp {{ number_format($rr['outstanding'], 0, ',', '.') }}
                                        </span>
                                        @if ($rr['days_until_due'] !== null && $rr['days_until_due'] < 0)
                                            <div class="text-[10px] text-error-500 font-medium">
                                                Terlambat {{ abs($rr['days_until_due']) }} hari
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 font-normal">Rp 0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-xs text-gray-400">
                                    Tidak ada data kamar yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── 5. Tab 3: TENANT FINANCIAL RECAP (Per Orang / Penyewa) ── --}}
    <div x-show="activeTab === 'tenants'" class="space-y-4">
        {{-- Tenant Metrics Summary Banner --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Total Penyewa</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $tenantSummary['total_tenants'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Penyewa Aktif</span>
                <p class="text-lg font-bold text-brand-600 dark:text-brand-400 mt-1">{{ $tenantSummary['active_tenants'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Status Lunas</span>
                <p class="text-lg font-bold text-success-600 dark:text-success-400 mt-1">{{ $tenantSummary['paid_tenants'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Belum Bayar</span>
                <p class="text-lg font-bold text-error-600 dark:text-error-400 mt-1">{{ $tenantSummary['unpaid_tenants'] }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Total Setoran Terkumpul</span>
                <p class="text-sm font-bold text-success-700 dark:text-success-300 mt-1.5">Rp {{ number_format($tenantSummary['total_paid'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[11px] text-gray-500 dark:text-gray-400">Total Piutang/Tunggakan</span>
                <p class="text-sm font-bold {{ $tenantSummary['total_outstanding'] > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-400' }} mt-1.5">
                    Rp {{ number_format($tenantSummary['total_outstanding'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Tenant Table Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h4 class="text-sm font-bold text-gray-800 dark:text-white/90">
                        Rincian Rekapitulasi Finansial per Penyewa (Per Orang)
                    </h4>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Rekam jejak setoran pembayaran, transaksi terverifikasi, dan status jatuh tempo penyewa
                    </p>
                </div>
                <form method="GET" action="{{ route('admin.finance.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="tab" value="tenants">

                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari nama / email / no HP / kamar..."
                            class="h-9 w-full rounded-xl border border-gray-200 bg-white px-3 pe-8 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                        />
                        <button type="submit" class="absolute end-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 text-start font-semibold">Penyewa &amp; Kontak</th>
                            <th class="py-3 px-4 text-start font-semibold">Kamar &amp; Lokasi</th>
                            <th class="py-3 px-4 text-center font-semibold">Status Akun</th>
                            <th class="py-3 px-4 text-end font-semibold text-success-600 dark:text-success-400">Total Setoran Terverifikasi</th>
                            <th class="py-3 px-4 text-center font-semibold">Frekuensi Transaksi</th>
                            <th class="py-3 px-4 text-center font-semibold">Status Periode Ini</th>
                            <th class="py-3 px-4 text-end font-semibold">Tunggakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse ($tenantReport as $tr)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $tr['name'] }}</div>
                                    <div class="text-[10px] text-gray-400 flex flex-wrap gap-2 mt-0.5">
                                        <span>{{ $tr['email'] }}</span>
                                        <span>•</span>
                                        <span class="font-mono">{{ $tr['phone'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($tr['has_active_assignment'])
                                        <div class="font-semibold text-gray-900 dark:text-white">Kamar {{ $tr['room_number'] }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $tr['location_name'] }}</div>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Tidak Ada Kamar Aktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tr['user_status'] === 'active' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                        {{ ucfirst($tr['user_status']) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end whitespace-nowrap font-bold text-success-600 dark:text-success-400">
                                    Rp {{ number_format($tr['total_paid_all'], 0, ',', '.') }}
                                    @if ($tr['total_paid_period'] > 0)
                                        <div class="text-[10px] text-success-700 font-normal">
                                            +Rp {{ number_format($tr['total_paid_period'], 0, ',', '.') }} periode ini
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <span class="rounded-lg bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $tr['transactions_count'] }} kali
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if ($tr['payment_status'] === 'Lunas')
                                        <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                            Lunas
                                        </span>
                                    @elseif ($tr['payment_status'] === 'Menunggu Verifikasi')
                                        <span class="inline-flex rounded-full bg-warning-50 px-2 py-0.5 text-[10px] font-bold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                            Menunggu Verifikasi
                                        </span>
                                    @elseif ($tr['payment_status'] === 'Belum Bayar')
                                        <span class="inline-flex rounded-full bg-error-50 px-2 py-0.5 text-[10px] font-bold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                            Belum Bayar
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                            {{ $tr['payment_status'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end whitespace-nowrap font-bold">
                                    @if ($tr['outstanding'] > 0)
                                        <span class="text-error-600 dark:text-error-400">
                                            Rp {{ number_format($tr['outstanding'], 0, ',', '.') }}
                                        </span>
                                        @if ($tr['days_until_due'] !== null && $tr['days_until_due'] < 0)
                                            <div class="text-[10px] text-error-500 font-medium">
                                                Terlambat {{ abs($tr['days_until_due']) }} hari
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 font-normal">Rp 0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-xs text-gray-400">
                                    Tidak ada data penyewa yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── 6. Tab 4: FINANCIAL RECORDS (Detailed Transaction Journal) ── --}}
    <div x-show="activeTab === 'records'" class="space-y-4">
        {{-- Controls Bar (Type Filter & Search) --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                <a
                    href="{{ route('admin.finance.index', array_merge(request()->query(), ['type' => 'all', 'tab' => 'records'])) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $selectedType === 'all' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
                >
                    <span>Semua Transaksi</span>
                </a>

                <a
                    href="{{ route('admin.finance.index', array_merge(request()->query(), ['type' => 'income', 'tab' => 'records'])) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $selectedType === 'income' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
                >
                    <span>Pemasukan</span>
                </a>

                <a
                    href="{{ route('admin.finance.index', array_merge(request()->query(), ['type' => 'expense', 'tab' => 'records'])) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $selectedType === 'expense' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
                >
                    <span>Pengeluaran</span>
                </a>
            </div>

            <form method="GET" action="{{ route('admin.finance.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <input type="hidden" name="type" value="{{ $selectedType }}">
                <input type="hidden" name="tab" value="records">

                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari transaksi, referensi..."
                        class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3.5 pe-9 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    />
                    <button type="submit" class="absolute end-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                @if (!empty($search))
                    <a
                        href="{{ route('admin.finance.index', array_merge(request()->except('search'), ['tab' => 'records'])) }}"
                        title="Reset Pencarian"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        {{-- Detailed Transaction Records Table --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4 text-start font-semibold">Tanggal Transaksi</th>
                            <th class="py-3.5 px-4 text-start font-semibold">Lokasi Kos</th>
                            <th class="py-3.5 px-4 text-start font-semibold">Jenis</th>
                            <th class="py-3.5 px-4 text-start font-semibold">Deskripsi &amp; Referensi Sumber</th>
                            <th class="py-3.5 px-4 text-end font-semibold">Nominal</th>
                            <th class="py-3.5 px-4 text-start font-semibold">Pencatat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        @forelse ($records as $record)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                                {{-- Date --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                    <div class="font-medium">
                                        {{ $record->transaction_date ? $record->transaction_date->translatedFormat('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        Input: {{ $record->created_at ? $record->created_at->format('H:i') . ' WIB' : '-' }}
                                    </div>
                                </td>

                                {{-- Location --}}
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-gray-800 dark:text-white/90">
                                        {{ $record->location?->name ?? 'Semua Lokasi / Umum' }}
                                    </span>
                                </td>

                                {{-- Type Badge --}}
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $record->typeBadgeClass() }}">
                                        {{ $record->typeLabel() }}
                                    </span>
                                </td>

                                {{-- Description & Source Reference --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $record->description }}
                                    </div>
                                    @if ($record->reference_type)
                                        <div class="text-[11px] text-gray-400 font-mono">
                                            Ref: {{ $record->reference_type }} #{{ $record->reference_id }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Amount --}}
                                <td class="py-3.5 px-4 text-end whitespace-nowrap">
                                    <span class="font-bold text-sm {{ $record->isIncome() ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                                        {{ $record->isIncome() ? '+' : '-' }} {{ $record->formattedAmount() }}
                                    </span>
                                </td>

                                {{-- Recorded By --}}
                                <td class="py-3.5 px-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ $record->recordedBy?->name ?? 'Sistem Otomatis' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-400 dark:bg-gray-800">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                        Tidak ada catatan transaksi finansial
                                        @if ($selectedType !== 'all')
                                            dengan tipe {{ $selectedType }}
                                        @endif
                                        @if (!empty($search))
                                            yang cocok dengan "{{ $search }}"
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($records->hasPages())
                <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
