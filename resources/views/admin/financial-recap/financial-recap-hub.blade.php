@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Keuangan" />

    {{-- Finance Navigation Tabs --}}
    <x-smart-kos.finance-tabs active="recap" />

    {{-- Page Header with View Mode Switcher --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white/90">Rekapitulasi Keuangan</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Ringkasan menyeluruh kondisi keuangan per penyewa maupun per kamar, dihitung langsung dari data transaksi.
            </p>
        </div>

        {{-- View Mode Toggle --}}
        <div class="flex items-center rounded-lg border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <a href="{{ route('admin.financial-recap.index', array_filter(['view' => 'tenant', 'location_id' => $selectedLocationId, 'search' => $search])) }}"
               class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors {{ $viewMode === 'tenant' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Per Penyewa
            </a>
            <a href="{{ route('admin.financial-recap.index', array_filter(['view' => 'room', 'location_id' => $selectedLocationId, 'search' => $search])) }}"
               class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors {{ $viewMode === 'room' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Per Kamar
            </a>
        </div>
    </div>

    {{-- Global KPI Metrics --}}
    @include('admin.financial-recap.financial-recap-metrics')

    {{-- Filters --}}
    @include('admin.financial-recap.financial-recap-filters')

    {{-- Data Table (conditional on viewMode) --}}
    @if ($viewMode === 'tenant')
        @include('admin.financial-recap.financial-recap-tenant-table')
    @else
        @include('admin.financial-recap.financial-recap-room-table')
    @endif
</div>
@endsection
