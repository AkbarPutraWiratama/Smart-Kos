@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Keuangan" />

    {{-- Tabs Keuangan --}}
    <x-smart-kos.finance-tabs active="arrears" />

    {{-- Header Section --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white/90">Tunggakan &amp; Piutang Sewa (Tenant Receivables)</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Monitoring penyewa kos yang telah melewati tanggal jatuh tempo pembayaran, terurut otomatis dari yang paling lama menunggak ke yang paling baru.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">
                <svg class="h-4 w-4 text-warning-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Terurut: Paling Lama Menunggak &darr;
            </span>
        </div>
    </div>

    {{-- KPI Summary Metrics --}}
    @include('admin.arrears.arrears-metrics')

    {{-- Filter Bar --}}
    @include('admin.arrears.arrears-filters')

    {{-- Arrears Table --}}
    @include('admin.arrears.arrears-table')
</div>
@endsection
