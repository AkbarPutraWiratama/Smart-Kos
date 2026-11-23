@extends('layouts.app')

@section('content')
<div
    x-data="{
        showRejectModal: false,
        rejectActionUrl: '',
        rejectTenantName: '',
        rejectAmount: '',
        rejectionReason: '',

        showApproveModal: false,
        approveActionUrl: '',
        approveTenantName: '',
        approveAmount: '',

        showProofModal: false,
        proofModalUrl: '',
        proofModalTitle: '',
        isPdf: false,

        openRejectModal(actionUrl, tenantName, amount) {
            this.rejectActionUrl = actionUrl;
            this.rejectTenantName = tenantName;
            this.rejectAmount = amount;
            this.rejectionReason = '';
            this.showRejectModal = true;
        },

        openApproveModal(actionUrl, tenantName, amount) {
            this.approveActionUrl = actionUrl;
            this.approveTenantName = tenantName;
            this.approveAmount = amount;
            this.showApproveModal = true;
        },

        openProofModal(url, title) {
            this.proofModalUrl = url;
            this.proofModalTitle = title;
            this.isPdf = url.toLowerCase().endsWith('.pdf');
            this.showProofModal = true;
        }
    }"
    class="space-y-6 w-full max-w-full min-w-0"
>
    {{-- Breadcrumb --}}
    <x-common.page-breadcrumb pageTitle="Verifikasi Pembayaran Manual" />

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="rounded-xl border border-success-200 bg-success-50/80 p-4 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-success-600 dark:text-success-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="rounded-xl border border-warning-200 bg-warning-50/80 p-4 text-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-300">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="font-medium">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-error-200 bg-error-50/80 p-4 text-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-error-600 dark:text-error-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- ── 1. Summary Metric Cards (Compact & Modern) ── --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Pending --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Menunggu Verifikasi</span>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <h3 class="text-lg font-bold tracking-tight tabular-nums sm:text-xl {{ $pendingCount > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-800 dark:text-white/90' }}">
                    {{ $pendingCount }}
                </h3>
                <p class="mt-0.5 text-[11px] text-gray-400">Bukti transfer baru</p>
            </div>
        </div>

        {{-- Approved --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Telah Disetujui</span>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <h3 class="text-lg font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90 sm:text-xl">
                    {{ $approvedCount }}
                </h3>
                <p class="mt-0.5 text-[11px] text-gray-400">Transaksi diverifikasi</p>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Ditolak</span>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <h3 class="text-lg font-bold tracking-tight tabular-nums text-gray-800 dark:text-white/90 sm:text-xl">
                    {{ $rejectedCount }}
                </h3>
                <p class="mt-0.5 text-[11px] text-gray-400">Perlu pengajuan ulang</p>
            </div>
        </div>

        {{-- Total Approved Amount --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Terverifikasi</span>
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <h3 class="text-lg font-bold tracking-tight tabular-nums text-brand-600 dark:text-brand-400 sm:text-xl">
                    Rp {{ number_format((float) $totalApprovedAmount, 0, ',', '.') }}
                </h3>
                <p class="mt-0.5 text-[11px] text-gray-400">Dana transfer masuk</p>
            </div>
        </div>
    </div>

    {{-- ── 2. Filter Tabs & Search Bar ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        {{-- Status Filter Tabs --}}
        <div class="inline-flex max-w-full overflow-x-auto custom-scrollbar rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
            <a
                href="{{ route('admin.payments.index', ['status' => 'pending', 'search' => $search]) }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'pending' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Menunggu</span>
                @if ($pendingCount > 0)
                    <span class="rounded-full bg-warning-500/20 px-1.5 py-0.2 text-[10px] font-bold text-warning-700 dark:text-warning-300">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>

            <a
                href="{{ route('admin.payments.index', ['status' => 'approved', 'search' => $search]) }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'approved' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Disetujui</span>
                <span class="rounded-full bg-gray-200 px-1.5 py-0.2 text-[10px] font-bold text-gray-600 dark:bg-gray-600 dark:text-gray-200">
                    {{ $approvedCount }}
                </span>
            </a>

            <a
                href="{{ route('admin.payments.index', ['status' => 'rejected', 'search' => $search]) }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'rejected' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Ditolak</span>
                <span class="rounded-full bg-gray-200 px-1.5 py-0.2 text-[10px] font-bold text-gray-600 dark:bg-gray-600 dark:text-gray-200">
                    {{ $rejectedCount }}
                </span>
            </a>

            <a
                href="{{ route('admin.payments.index', ['status' => 'all', 'search' => $search]) }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'all' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Semua</span>
            </a>
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('admin.payments.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <div class="relative w-full sm:w-64">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari penyewa, kamar, bank..."
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
                    href="{{ route('admin.payments.index', ['status' => $statusFilter]) }}"
                    title="Reset Pencarian"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif
        </form>
    </div>

    {{-- ── 3. Table of Manual Payments (Full Visibility & Responsive) ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        {{-- Mobile Card View (sm:hidden) --}}
        <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
            @forelse ($payments as $payment)
                @php
                    $room = $payment->roomAssignment?->room;
                    $location = $room?->location;
                    $tenantUser = $payment->roomAssignment?->tenantProfile?->user ?? $payment->submittedBy;
                    $proofUrl = $payment->proofUrl();
                @endphp
                <div class="p-4 space-y-3">
                    {{-- Header: Tenant info + Status badge --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ strtoupper(substr($tenantUser->name ?? 'P', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">
                                    {{ $tenantUser->name ?? 'Penyewa' }}
                                </p>
                                <p class="text-[11px] text-gray-400 truncate">
                                    {{ '@' . ($tenantUser->username ?? '-') }}
                                    @if ($tenantUser?->phone)
                                        &bull; {{ $tenantUser->phone }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $payment->statusBadgeClass() }}">
                            {{ $payment->statusLabel() }}
                        </span>
                    </div>

                    {{-- Details Grid --}}
                    <div class="grid grid-cols-2 gap-2 rounded-xl bg-gray-50/75 p-3 text-xs dark:bg-white/[0.02] dark:border dark:border-white/[0.05]">
                        <div>
                            <span class="text-[11px] text-gray-400 block">Kamar &amp; Lokasi</span>
                            @if ($room)
                                <p class="font-medium text-gray-800 dark:text-gray-200">Kamar {{ $room->room_number }} <span class="text-[11px] text-gray-400">(Lt. {{ $room->floor_name }})</span></p>
                                <p class="text-[11px] text-gray-400 truncate">{{ $location?->name ?? '-' }}</p>
                            @else
                                <span class="text-gray-400 italic">Tidak terikat kamar</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[11px] text-gray-400 block">Nominal Transfer</span>
                            <p class="text-sm font-bold tabular-nums text-gray-900 dark:text-white">
                                {{ $payment->formattedAmount() }}
                            </p>
                        </div>
                        <div>
                            <span class="text-[11px] text-gray-400 block">Bank / Rekening</span>
                            <p class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ $payment->bank_name }}</p>
                            <p class="font-mono text-[11px] text-gray-400">{{ $payment->bank_account_number }}</p>
                        </div>
                        <div>
                            <span class="text-[11px] text-gray-400 block">Diajukan</span>
                            <p class="text-gray-600 dark:text-gray-300">{{ $payment->submitted_at ? $payment->submitted_at->translatedFormat('d M Y') : '-' }}</p>
                            <p class="text-[11px] text-gray-400">{{ $payment->submitted_at ? $payment->submitted_at->format('H:i') . ' WIB' : '' }}</p>
                        </div>
                    </div>

                    {{-- Rejection reason / Verifier info --}}
                    @if ($payment->isRejected() && $payment->rejection_reason)
                        <div class="rounded-lg bg-error-50 p-2.5 text-xs text-error-700 dark:bg-error-500/10 dark:text-error-400">
                            <strong>Alasan Ditolak:</strong> {{ $payment->rejection_reason }}
                        </div>
                    @endif
                    @if ($payment->verifiedBy)
                        <p class="text-[11px] text-gray-400">
                            Diverifikasi oleh: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $payment->verifiedBy->name }}</span>
                            @if ($payment->verified_at)
                                &bull; {{ $payment->verified_at->translatedFormat('d M Y H:i') }}
                            @endif
                        </p>
                    @endif

                    {{-- Actions Row --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-white/[0.05]">
                        {{-- Proof Button --}}
                        @if ($proofUrl)
                            <button
                                type="button"
                                @click="openProofModal('{{ $proofUrl }}', 'Bukti Transfer {{ $tenantUser?->name }}')"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400 dark:hover:bg-gray-700"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Lihat Bukti</span>
                            </button>
                        @else
                            <span class="text-xs text-gray-400 italic">Tanpa bukti file</span>
                        @endif

                        {{-- Pending Actions --}}
                        @if ($payment->isPending())
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="openRejectModal('{{ route('admin.payments.reject', $payment) }}', '{{ addslashes($tenantUser?->name ?? 'Penyewa') }}', '{{ $payment->formattedAmount() }}')"
                                    class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-1.5 text-xs font-semibold text-error-700 hover:bg-error-100 transition dark:bg-error-500/15 dark:text-error-300"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Tolak</span>
                                </button>
                                <button
                                    type="button"
                                    @click="openApproveModal('{{ route('admin.payments.approve', $payment) }}', '{{ addslashes($tenantUser?->name ?? 'Penyewa') }}', '{{ $payment->formattedAmount() }}')"
                                    class="inline-flex items-center gap-1 rounded-lg bg-success-600 px-3 py-1.5 text-xs font-semibold text-white shadow-theme-xs hover:bg-success-700 transition"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Setujui</span>
                                </button>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">
                                {{ $payment->verified_at ? $payment->verified_at->translatedFormat('d M Y H:i') : 'Selesai' }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 px-4 text-center text-xs text-gray-400 dark:text-gray-500">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Tidak ada data pembayaran manual
                        @if ($statusFilter !== 'all')
                            dengan status {{ $statusFilter }}
                        @endif
                        @if (!empty($search))
                            yang cocok dengan "{{ $search }}"
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Desktop Table View (hidden sm:block) --}}
        <div class="hidden sm:block overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[850px] text-start text-xs">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-start font-semibold">Penyewa</th>
                        <th class="py-3.5 px-4 text-start font-semibold whitespace-nowrap">Kamar &amp; Lokasi</th>
                        <th class="py-3.5 px-4 text-start font-semibold whitespace-nowrap">Nominal Transfer</th>
                        <th class="py-3.5 px-4 text-start font-semibold whitespace-nowrap">Rekening Pengirim</th>
                        <th class="py-3.5 px-4 text-center font-semibold whitespace-nowrap">Bukti Pembayaran</th>
                        <th class="py-3.5 px-4 text-start font-semibold whitespace-nowrap">Waktu Pengajuan</th>
                        <th class="py-3.5 px-4 text-start font-semibold whitespace-nowrap">Status</th>
                        <th class="py-3.5 px-4 text-end font-semibold whitespace-nowrap">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                    @forelse ($payments as $payment)
                        @php
                            $room = $payment->roomAssignment?->room;
                            $location = $room?->location;
                            $tenantUser = $payment->roomAssignment?->tenantProfile?->user ?? $payment->submittedBy;
                            $proofUrl = $payment->proofUrl();
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                            {{-- Tenant Info (Fully visible without premature clipping) --}}
                            <td class="py-3.5 px-4 min-w-[180px]">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ strtoupper(substr($tenantUser->name ?? 'P', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ $tenantUser->name ?? 'Penyewa' }}
                                        </div>
                                        <div class="text-[11px] text-gray-400">
                                            {{ '@' . ($tenantUser->username ?? '-') }}
                                            @if ($tenantUser?->phone)
                                                &bull; {{ $tenantUser->phone }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Room & Location --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($room)
                                    <div class="font-medium text-gray-800 dark:text-white/90">
                                        Kamar {{ $room->room_number }}
                                        <span class="text-gray-400 text-[11px]">(Lt. {{ $room->floor_name }})</span>
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $location?->name ?? '-' }}
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Tidak terikat kamar</span>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold tabular-nums text-gray-900 dark:text-white">
                                    {{ $payment->formattedAmount() }}
                                </span>
                            </td>

                            {{-- Sender Bank --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $payment->bank_name }}
                                </div>
                                <div class="font-mono text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $payment->bank_account_number }}
                                </div>
                            </td>

                            {{-- Proof Preview --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if ($proofUrl)
                                    <button
                                        type="button"
                                        @click="openProofModal('{{ $proofUrl }}', 'Bukti Transfer {{ $tenantUser?->name }}')"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400 dark:hover:bg-gray-700"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Lihat Bukti</span>
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Submission Date --}}
                            <td class="py-3.5 px-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                <div>{{ $payment->submitted_at ? $payment->submitted_at->translatedFormat('d M Y') : '-' }}</div>
                                <div class="text-[11px] text-gray-400">{{ $payment->submitted_at ? $payment->submitted_at->format('H:i') . ' WIB' : '' }}</div>
                            </td>

                            {{-- Status Badge (Compact & Sleek) --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $payment->statusBadgeClass() }}">
                                    {{ $payment->statusLabel() }}
                                </span>
                                @if ($payment->isRejected() && $payment->rejection_reason)
                                    <div class="mt-1 text-[11px] text-error-600 dark:text-error-400 italic max-w-xs">
                                        Alasan: {{ $payment->rejection_reason }}
                                    </div>
                                @endif
                                @if ($payment->verifiedBy)
                                    <div class="mt-1 text-[10px] text-gray-400">
                                        Oleh: {{ $payment->verifiedBy->name }}
                                    </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-end whitespace-nowrap">
                                @if ($payment->isPending())
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        {{-- Approve Button (Triggers Alpine Modal) --}}
                                        <button
                                            type="button"
                                            @click="openApproveModal('{{ route('admin.payments.approve', $payment) }}', '{{ addslashes($tenantUser?->name ?? 'Penyewa') }}', '{{ $payment->formattedAmount() }}')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-success-600 px-3 py-1.5 text-xs font-semibold text-white shadow-theme-xs hover:bg-success-700 transition"
                                            title="Setujui Pembayaran"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Setujui</span>
                                        </button>

                                        {{-- Reject Button (Triggers Alpine Modal) --}}
                                        <button
                                            type="button"
                                            @click="openRejectModal('{{ route('admin.payments.reject', $payment) }}', '{{ addslashes($tenantUser?->name ?? 'Penyewa') }}', '{{ $payment->formattedAmount() }}')"
                                            class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-1.5 text-xs font-semibold text-error-700 hover:bg-error-100 transition dark:bg-error-500/15 dark:text-error-300 dark:hover:bg-error-500/25"
                                            title="Tolak Pembayaran"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span>Tolak</span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">
                                        {{ $payment->verified_at ? $payment->verified_at->translatedFormat('d M Y H:i') : 'Selesai' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-400 dark:bg-gray-800">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Tidak ada data pembayaran manual
                                    @if ($statusFilter !== 'all')
                                        dengan status {{ $statusFilter }}
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
        @if ($payments->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    {{-- ── 4. Approve Confirmation Modal ── --}}
    <div
        x-show="showApproveModal"
        x-cloak
        class="fixed inset-0 z-9999 flex items-center justify-center p-4 bg-gray-900/60"
        @keydown.escape.window="showApproveModal = false"
    >
        <div
            x-show="showApproveModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showApproveModal = false"
            class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">
                        Konfirmasi Setujui Pembayaran
                    </h3>
                </div>
                <button
                    type="button"
                    @click="showApproveModal = false"
                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="mt-4 space-y-4">
                {{-- Info box --}}
                <div class="rounded-xl border border-success-200 bg-success-50/60 p-3.5 dark:border-success-500/20 dark:bg-success-500/10">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-success-500">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold text-success-800 dark:text-success-300">Pembayaran akan disetujui</p>
                            <p class="mt-0.5 text-[11px] text-success-700 dark:text-success-400">
                                Penyewa <strong x-text="approveTenantName"></strong> &bull; Nominal <strong x-text="approveAmount"></strong>
                            </p>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Tindakan ini akan menandai bukti transfer sebagai <span class="font-semibold text-success-700 dark:text-success-400">Disetujui</span> dan mencatat pembayaran sebagai lunas untuk periode ini. Tindakan tidak dapat dibatalkan.
                </p>

                {{-- Action Form --}}
                <form :action="approveActionUrl" method="POST">
                    @csrf
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button
                            type="button"
                            @click="showApproveModal = false"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-success-600 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-success-700 transition"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Ya, Setujui Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── 5. Reject Confirmation Modal ── --}}
    <div
        x-show="showRejectModal"
        x-cloak
        class="fixed inset-0 z-9999 flex items-center justify-center p-4 bg-gray-900/60"
        @keydown.escape.window="showRejectModal = false"
    >
        <div
            x-show="showRejectModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showRejectModal = false"
            class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <div class="flex items-center gap-2 text-error-600 dark:text-error-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">
                        Tolak Pembayaran Manual
                    </h3>
                </div>
                <button
                    type="button"
                    @click="showRejectModal = false"
                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="rejectActionUrl" method="POST" class="mt-4 space-y-4">
                @csrf
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Anda akan menolak bukti pembayaran dari <strong class="text-gray-800 dark:text-white" x-text="rejectTenantName"></strong> sebesar <strong class="text-gray-800 dark:text-white" x-text="rejectAmount"></strong>.
                </p>

                <div>
                    <label for="rejection_reason" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                        Alasan Penolakan <span class="text-error-500">*</span>
                    </label>
                    <textarea
                        id="rejection_reason"
                        name="rejection_reason"
                        x-model="rejectionReason"
                        rows="3"
                        required
                        maxlength="255"
                        placeholder="Contoh: Bukti buram / nominal tidak sesuai / dana belum masuk rekening"
                        class="w-full rounded-xl border border-gray-300 bg-transparent p-3 text-xs text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white"
                    ></textarea>
                    <p class="mt-1 text-[11px] text-gray-400">
                        Alasan ini akan ditampilkan ke penyewa agar dapat mengunggah bukti yang benar.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="showRejectModal = false"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-error-600 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-error-700 transition"
                    >
                        Konfirmasi Tolak Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 5. Proof Lightbox Modal ── --}}
    <div
        x-show="showProofModal"
        x-cloak
        class="fixed inset-0 z-9999 flex items-center justify-center p-4 bg-gray-900/70"
        @keydown.escape.window="showProofModal = false"
    >
        <div
            x-show="showProofModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="showProofModal = false"
            class="relative w-full max-w-2xl max-h-[90vh] flex flex-col rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white" x-text="proofModalTitle"></h4>
                <div class="flex items-center gap-2">
                    <a
                        :href="proofModalUrl"
                        target="_blank"
                        download
                        class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-gray-800"
                        title="Unduh / Buka di Tab Baru"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                    <button
                        type="button"
                        @click="showProofModal = false"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mt-4 overflow-auto flex-1 flex items-center justify-center min-h-[300px]">
                <template x-if="isPdf">
                    <iframe :src="proofModalUrl" class="w-full h-[500px] rounded-lg border border-gray-200 dark:border-gray-800"></iframe>
                </template>
                <template x-if="!isPdf">
                    <img :src="proofModalUrl" :alt="proofModalTitle" class="max-h-[70vh] w-auto max-w-full rounded-xl object-contain shadow-sm">
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
