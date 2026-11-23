@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showPayrollModal: false, showAdvanceModal: false }">
    <x-common.page-breadcrumb pageTitle="Keuangan" />

    {{-- Tabs Keuangan --}}
    <x-smart-kos.finance-tabs active="payroll" />

    {{-- Action & Notification Bar --}}
    @if (session('success'))
        <div class="rounded-xl border border-success-200 bg-success-50/70 p-4 text-sm text-success-800 dark:border-success-900/50 dark:bg-success-950/20 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-error-200 bg-error-50/70 p-4 text-sm text-error-800 dark:border-error-900/50 dark:bg-error-950/20 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50/70 p-4 text-sm text-error-800 dark:border-error-900/50 dark:bg-error-950/20 dark:text-error-400">
            <ul class="list-disc ps-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Action Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white/90">Gaji & Kasbon Staf</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Kelola riwayat penggajian, pemotongan pinjaman muka (kasbon), dan pencatatan kas keluar.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" @click="showAdvanceModal = true"
                class="inline-flex items-center gap-2 rounded-lg border border-warning-300 bg-warning-50 px-4 py-2.5 text-xs font-semibold text-warning-800 shadow-theme-xs transition hover:bg-warning-100 dark:border-warning-700/60 dark:bg-warning-500/10 dark:text-warning-300 dark:hover:bg-warning-500/20">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Catat Kasbon
            </button>
            <button type="button" @click="showPayrollModal = true"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Catat Gaji Staf
            </button>
        </div>
    </div>

    {{-- Summary Metrics --}}
    @include('admin.payroll.payroll-metrics')

    {{-- Filter bar --}}
    @include('admin.payroll.payroll-filters')

    {{-- Payroll Table --}}
    @include('admin.payroll.payroll-table')

    {{-- Cash Advance Table --}}
    @include('admin.payroll.cash-advance-table')

    {{-- Modals --}}
    @include('admin.payroll.payroll-modal')
    @include('admin.payroll.cash-advance-modal')
</div>
@endsection
