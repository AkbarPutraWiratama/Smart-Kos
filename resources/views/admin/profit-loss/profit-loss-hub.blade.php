@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Keuangan" />

    {{-- Tabs Keuangan --}}
    <x-smart-kos.finance-tabs active="profit-loss" />

    {{-- Header Section --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white/90">Laporan Laba Rugi (Profit &amp; Loss)</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Analisis profitabilitas operasional kos berdasarkan total pendapatan dikurangi seluruh beban operasional dan upah.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">
                <svg class="h-4 w-4 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ $periodLabel }}
            </span>
        </div>
    </div>

    {{-- KPI Metrics --}}
    @include('admin.profit-loss.profit-loss-metrics')

    {{-- Filter Bar --}}
    @include('admin.profit-loss.profit-loss-filters')

    {{-- Financial Statement Card (Income - Expenses = Net Profit/Loss) --}}
    @include('admin.profit-loss.profit-loss-statement')

    {{-- Detailed Composition Breakdown --}}
    @include('admin.profit-loss.profit-loss-breakdown')
</div>
@endsection
