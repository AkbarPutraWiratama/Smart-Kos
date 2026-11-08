@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Dashboard Admin" />

<div
    x-data="{
        isCreateModalOpen: false,
        isEditModalOpen: false,
        isDeleteConfirmOpen: false,
        editingPost: null,
        editTitle: '',
        editContent: '',
        editTargetType: 'all',
        editLocationId: '',
        editRoomId: '',
        editHasExpiry: false,
        editExpiresAt: '',
        targetType: 'all',
        selectedLocationId: '',
        allRooms: {{ Js::from($allRooms->map(fn($r) => [
            'id' => $r->id,
            'location_id' => $r->location_id,
            'label' => 'Kamar ' . $r->room_number . ' (Lantai ' . $r->floor_name . ')',
        ])) }},
        get filteredRooms() {
            if (!this.selectedLocationId) return [];
            return this.allRooms.filter(r => String(r.location_id) === String(this.selectedLocationId));
        },
        get editFilteredRooms() {
            if (!this.editLocationId) return [];
            return this.allRooms.filter(r => String(r.location_id) === String(this.editLocationId));
        }
    }"
    class="space-y-6"
>
    {{-- Success Alert --}}
    @if (session('success'))
        <div class="flex items-center justify-between rounded-xl border border-success-200 bg-success-50 p-4 text-success-800 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Validation Errors Alert --}}
    @if ($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 p-4 text-error-800 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400">
            <div class="flex items-center gap-2 font-semibold text-sm">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terdapat kesalahan pada input pengumuman:</span>
            </div>
            <ul class="mt-2 list-inside list-disc text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── 1. Header Greeting & Admin Profile Summary ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-xl font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-800 md:text-xl dark:text-white/90">
                            Halo, {{ $user->name }}
                        </h2>
                        <span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                            Administrator
                        </span>
                        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                            Akun Aktif
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                        {{ '@' . $user->username }} &bull; {{ $user->email ?? 'Tanpa Email' }}
                        @if ($user->phone)
                            &bull; {{ $user->phone }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Quick Action Links --}}
            <div class="flex flex-wrap items-center gap-2 pt-2 sm:pt-0">
                <a
                    href="{{ route('admin.posts.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-theme-xs transition hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat Pengumuman</span>
                </a>

                <a
                    href="{{ route('admin.tenants.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-700 shadow-theme-xs transition hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Manajemen Penyewa</span>
                </a>

                <a
                    href="{{ route('admin.finance.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-700 shadow-theme-xs transition hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Laporan Keuangan</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Room & Tenant Metric Cards ── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {{-- Total Rooms --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Kamar</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $totalRooms }}</h3>
                <p class="mt-1 text-xs text-gray-400">Unit aktif terdaftar</p>
            </div>
        </div>

        {{-- Occupied Rooms --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kamar Terisi</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $occupiedRooms }}</h3>
                <p class="mt-1 text-xs text-gray-400">Dihuni penyewa aktif</p>
            </div>
        </div>

        {{-- Empty Rooms --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kamar Kosong</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $emptyRooms }}</h3>
                <p class="mt-1 text-xs text-gray-400">Siap dihuni / ditawarkan</p>
            </div>
        </div>

        {{-- Pending Verifications --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Verifikasi Pending</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold {{ $pendingVerificationsCount > 0 ? 'text-blue-light-600 dark:text-blue-light-400' : 'text-gray-800 dark:text-white/90' }}">
                    {{ $pendingVerificationsCount }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">Bukti transfer manual</p>
            </div>
        </div>
    </div>

    {{-- ── 3. Financial Summary Cards ── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        {{-- Total Income This Month --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pemasukan Bulan Ini</p>
                    <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
                </div>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-xl font-bold text-success-600 dark:text-success-400">
                    Rp {{ number_format((float) $totalIncomeMonth, 0, ',', '.') }}
                </h3>
                @if ((float) $totalIncomeMonth === 0.0)
                    <p class="mt-1 text-[11px] text-gray-400 italic">Belum ada catatan pemasukan</p>
                @else
                    <p class="mt-1 text-[11px] text-gray-400">Total pemasukan tercatat</p>
                @endif
            </div>
        </div>

        {{-- Total Expense This Month --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pengeluaran Bulan Ini</p>
                    <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
                </div>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-xl font-bold text-error-600 dark:text-error-400">
                    Rp {{ number_format((float) $totalExpenseMonth, 0, ',', '.') }}
                </h3>
                @if ((float) $totalExpenseMonth === 0.0)
                    <p class="mt-1 text-[11px] text-gray-400 italic">Belum ada catatan pengeluaran</p>
                @else
                    <p class="mt-1 text-[11px] text-gray-400">Total pengeluaran tercatat</p>
                @endif
            </div>
        </div>

        {{-- Net Income This Month --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pendapatan Bersih</p>
                    <p class="text-[11px] text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
                </div>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                @php $isNegativeNet = $netIncomeMonth < 0; @endphp
                <h3 class="text-xl font-bold {{ $isNegativeNet ? 'text-error-600 dark:text-error-400' : ($netIncomeMonth > 0 ? 'text-brand-600 dark:text-brand-400' : 'text-gray-800 dark:text-white/90') }}">
                    @if ($isNegativeNet) &minus; @endif
                    Rp {{ number_format(abs($netIncomeMonth), 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-[11px] text-gray-400">
                    {{ $isNegativeNet ? 'Defisit bulan ini' : 'Pemasukan bersih bulan ini' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ── 4. Payment Deadlines Table (Admin CAN see rent amounts) ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-2 p-5 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-800">
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Tenggat Pembayaran Penyewa
                </h3>
                <p class="text-xs text-gray-400">
                    Pantau kepatuhan sewa seluruh unit aktif beserta nominal sewa
                </p>
            </div>

            {{-- Legend Badges --}}
            <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-error-500"></span> Hari Ini / Terlambat
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-warning-500"></span> &le; 7 Hari
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-gray-400"></span> &gt; 7 Hari
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-success-500"></span> Lunas
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-start font-semibold">Penyewa</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Kamar &amp; Lokasi</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Nominal Sewa</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Tenggat Jatuh Tempo</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Sisa Waktu</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                    @forelse ($assignments as $assignment)
                        @php
                            $room = $assignment->room;
                            $location = $room->location;
                            $tenantUser = $assignment->tenantProfile?->user;
                            $dueDate = $assignment->getPaymentDueDate();
                            $days = $assignment->getDaysUntilDue();
                            $status = $assignment->getPaymentStatus();
                            $isPaid = $assignment->isPaidForCurrentPeriod();
                            $hasPending = $assignment->hasPendingPayment();
                            $deadlineColor = $assignment->getDeadlineColor();
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                            {{-- Tenant info --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
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
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-gray-800 dark:text-white/90">
                                    Kamar {{ $room->room_number }}
                                    <span class="text-gray-400 text-[11px]">(Lt. {{ $room->floor_name }})</span>
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    {{ $location->name }}
                                </div>
                            </td>

                            {{-- Rent Amount (Admin privilege — visible) --}}
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-gray-800 dark:text-white/90">
                                    Rp {{ number_format((float) $assignment->rent_amount_snapshot, 0, ',', '.') }}
                                </span>
                                <div class="text-[11px] text-gray-400">/bulan</div>
                            </td>

                            {{-- Due Date --}}
                            <td class="py-3.5 px-4 font-medium text-gray-800 dark:text-gray-200">
                                {{ $dueDate->translatedFormat('d M Y') }}
                            </td>

                            {{-- Countdown Badge --}}
                            <td class="py-3.5 px-4">
                                @if ($isPaid)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Periode Lunas
                                    </span>
                                @elseif ($hasPending)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-blue-light-50 px-2 py-0.5 text-xs font-medium text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                                        <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Verifikasi Pending
                                    </span>
                                @elseif ($days < 0)
                                    <span class="inline-flex items-center rounded-md bg-error-50 px-2 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                        Terlambat {{ abs($days) }} Hari
                                    </span>
                                @elseif ($days === 0)
                                    <span class="inline-flex items-center rounded-md bg-error-50 px-2 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                        Hari Ini
                                    </span>
                                @elseif ($days <= 7)
                                    <span class="inline-flex items-center rounded-md bg-warning-50 px-2 py-0.5 text-xs font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                        H-{{ $days }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $days }} Hari Lagi
                                    </span>
                                @endif
                            </td>

                            {{-- Payment Status Badge --}}
                            <td class="py-3.5 px-4 text-center">
                                @if ($isPaid)
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                        Lunas
                                    </span>
                                @elseif ($hasPending)
                                    <span class="inline-flex items-center rounded-full bg-blue-light-50 px-2.5 py-0.5 text-xs font-semibold text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                                        Menunggu Verifikasi
                                    </span>
                                @elseif ($status === 'Jatuh Tempo Hari Ini')
                                    <span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                        Jatuh Tempo Hari Ini
                                    </span>
                                @elseif ($status === 'Menunggak')
                                    <span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                        Menunggak
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full {{ $deadlineColor === 'yellow' ? 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }} px-2.5 py-0.5 text-xs font-semibold">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        Belum ada penyewa aktif tercatat.
                                    </p>
                                    <a
                                        href="{{ route('admin.tenants.index') }}"
                                        class="mt-2 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400"
                                    >
                                        Tambah Penyewa &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── 5. Announcements Section (Papan Pengumuman) ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Papan Pengumuman &amp; Informasi
                </h3>
                <p class="text-xs text-gray-400">
                    Klik pengumuman untuk mengedit — {{ $activeAnnouncementsCount }} aktif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('admin.posts.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 transition"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat</span>
                </a>
                <button
                    type="button"
                    @click="isCreateModalOpen = true"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 active:scale-95"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Pengumuman
                </button>
            </div>
        </div>

        @include('components.smart-kos.announcement-list-editable', [
            'announcements'    => $announcements,
            'locations'        => $locations,
            'allRooms'         => $allRooms,
            'updateRouteName'  => 'admin.posts.update',
            'destroyRouteName' => 'admin.posts.destroy',
        ])
    </div>

    {{-- ── 6. Create Post Modal (shared component) ── --}}
    <x-smart-kos.create-post-modal
        storeRoute="admin.posts.store"
        :locations="$locations"
    />
</div>
@endsection
