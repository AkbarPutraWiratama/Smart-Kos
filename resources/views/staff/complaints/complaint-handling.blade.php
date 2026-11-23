@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Tindakan Aduan" />

    {{-- Flash Notifications --}}
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
                <span>Terdapat kesalahan pada isian tindakan:</span>
            </div>
            <ul class="mt-2 list-inside list-disc text-xs text-error-700 dark:text-error-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Highlight Attention Banner for Staff --}}
    @if (($stats['highlighted'] ?? 0) > 0)
        <div class="rounded-2xl border border-error-200 bg-error-50 p-4 text-sm text-error-900 shadow-theme-xs dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-error-500 text-white shadow-theme-xs">
                        <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="font-bold">Perhatian: Terdapat {{ $stats['highlighted'] }} Aduan Prioritas dari Admin</p>
                        <p class="text-xs text-error-700 dark:text-error-300">
                            Aduan ini ditandai khusus oleh Admin untuk mendapatkan atensi dan penanganan segera dari staf lapangan.
                        </p>
                    </div>
                </div>
                <a href="{{ route('staff.complaints.index', ['highlighted' => '1']) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-error-600 px-3.5 py-2 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-error-700">
                    <span>Lihat Aduan Prioritas</span>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    @endif

    {{-- Metrics Summary --}}
    @include('staff.complaints.complaint-metrics')

    {{-- Filters Bar --}}
    @include('staff.complaints.complaint-filters')

    {{-- Complaints Cards Grid --}}
    @include('staff.complaints.complaint-cards')

    {{-- Action Popup Modal --}}
    @include('staff.complaints.complaint-action-modal')
</div>
@endsection
