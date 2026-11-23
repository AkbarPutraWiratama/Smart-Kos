@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <x-common.page-breadcrumb pageTitle="Keuangan" />

    <x-smart-kos.finance-tabs active="expenses" />

    {{-- Summary + actions bar (Compact & Modern) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pengeluaran (Filter Aktif)</p>
            <p class="mt-1 text-lg font-bold tabular-nums text-error-600 dark:text-error-400 sm:text-xl">
                Rp {{ number_format((float) $totalAmount, 0, ',', '.') }}
            </p>
        </div>
        <a href="{{ route('admin.expenses.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 w-full sm:w-auto">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Pengeluaran
        </a>
    </div>

    {{-- Filters --}}
    @include('admin.expenses.expense-filters')

    {{-- Table --}}
    @include('admin.expenses.expense-table')
</div>
@endsection
