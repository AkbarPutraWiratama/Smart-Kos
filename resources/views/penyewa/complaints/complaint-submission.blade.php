@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Aduan Fasilitas" />

    {{-- Session Alerts --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-800 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            <svg class="h-5 w-5 shrink-0 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-800 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
            <svg class="h-5 w-5 shrink-0 text-error-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 p-4 dark:border-error-500/20 dark:bg-error-500/10">
            <div class="flex items-center gap-2 text-sm font-semibold text-error-800 dark:text-error-300">
                <svg class="h-5 w-5 text-error-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Terdapat kesalahan pada isian formulir:</span>
            </div>
            <ul class="mt-2 list-inside list-disc text-xs text-error-700 dark:text-error-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Room Information Banner --}}
    @if ($activeAssignment)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-brand-200 bg-brand-50/50 p-4 dark:border-brand-500/20 dark:bg-brand-500/5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-500 text-white shadow-theme-xs">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-brand-600 dark:text-brand-400">Kamar Aktif Anda</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-white/90">
                        {{ $activeAssignment->room?->location?->name ?? 'Kos' }} — Kamar #{{ $activeAssignment->room?->room_number }}
                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(Lantai {{ $activeAssignment->room?->floor_name }})</span>
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                Sewa Aktif
            </span>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 bg-white p-4 text-xs text-gray-600 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <span>Anda belum terhubung ke kamar aktif saat ini. Anda tetap dapat mengirimkan aduan umum fasilitas kos.</span>
            </div>
        </div>
    @endif

    {{-- Metrics Summary --}}
    @include('penyewa.complaints.complaint-metrics')

    {{-- Main 2-Column Grid: Form & History --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Submission Form (5 columns) --}}
        <div class="lg:col-span-5">
            @include('penyewa.complaints.complaint-form')
        </div>

        {{-- History & Progress (7 columns) --}}
        <div class="lg:col-span-7">
            @include('penyewa.complaints.complaint-history')
        </div>
    </div>
</div>
@endsection
