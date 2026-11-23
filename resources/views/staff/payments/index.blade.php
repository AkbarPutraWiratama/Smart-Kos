@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Status Pembayaran Penyewa" />

@php
    $realAssignments = collect();
    try {
        $realAssignments = \App\Models\RoomAssignment::with([
            'room.location',
            'tenantProfile.user',
            'vaPayments',
            'manualPayments',
        ])->where('status', 'active')->get();
    } catch (\Throwable $e) {
        $realAssignments = collect();
    }

    $hasRealData = $realAssignments->isNotEmpty();

    if ($hasRealData) {
        $totalActive = $realAssignments->count();
        $urgentCount = 0;
        $approachingCount = 0;
        $safeCount = 0;

        foreach ($realAssignments as $assignment) {
            $isPaid = $assignment->isPaidForCurrentPeriod();
            $hasPending = $assignment->hasPendingPayment();
            $days = $assignment->getDaysUntilDue();

            if ($isPaid) {
                $safeCount++;
            } elseif ($hasPending) {
                $approachingCount++;
            } elseif ($days <= 0) {
                $urgentCount++;
            } elseif ($days <= 7) {
                $approachingCount++;
            } else {
                $safeCount++;
            }
        }

        $items = $realAssignments->sortBy(function ($assignment) {
            $isPaid = $assignment->isPaidForCurrentPeriod();
            $hasPending = $assignment->hasPendingPayment();
            $days = $assignment->getDaysUntilDue();

            if ($hasPending) return 0;
            if (!$isPaid && $days <= 0) return 1;
            if (!$isPaid && $days <= 7) return 2;
            return 3;
        })->map(function ($assignment) {
            $room = $assignment->room;
            $location = $room?->location;
            $tenantUser = $assignment->tenantProfile?->user;
            $dueDate = $assignment->getPaymentDueDate();
            $days = $assignment->getDaysUntilDue();
            $isPaid = $assignment->isPaidForCurrentPeriod();
            $hasPending = $assignment->hasPendingPayment();

            if ($isPaid) {
                $statusLabel = 'Lunas';
                $statusType = 'paid';
            } elseif ($hasPending) {
                $statusLabel = 'Verifikasi';
                $statusType = 'pending';
            } elseif ($days < 0) {
                $statusLabel = 'Terlambat ' . abs($days) . ' hr';
                $statusType = 'danger';
            } elseif ($days === 0) {
                $statusLabel = 'Hari Ini';
                $statusType = 'danger';
            } elseif ($days <= 7) {
                $statusLabel = 'H-' . $days;
                $statusType = 'warning';
            } else {
                $statusLabel = $days . ' Hari Lagi';
                $statusType = 'safe';
            }

            return [
                'tenant_name' => $tenantUser->name ?? 'Penyewa',
                'username' => $tenantUser->username ?? '-',
                'location' => $location->name ?? 'Kos',
                'room' => 'Kamar ' . ($room->room_number ?? '-'),
                'due_date' => $dueDate ? $dueDate->translatedFormat('d M Y') : '-',
                'status_label' => $statusLabel,
                'status_type' => $statusType,
            ];
        })->values();
    } else {
        $totalActive = 24;
        $urgentCount = 2;
        $approachingCount = 4;
        $safeCount = 18;

        $items = collect([
            [
                'tenant_name' => 'Budi Santoso',
                'username' => 'budis',
                'location' => 'Kos Melati',
                'room' => 'Kamar 101',
                'due_date' => '25 Okt 2026',
                'status_label' => 'Mendekati (H-2)',
                'status_type' => 'warning',
            ],
            [
                'tenant_name' => 'Ahmad Fajar',
                'username' => 'ahmadf',
                'location' => 'Kos Melati',
                'room' => 'Kamar 104',
                'due_date' => 'Hari Ini',
                'status_label' => 'Jatuh Tempo',
                'status_type' => 'danger',
            ],
            [
                'tenant_name' => 'Siti Rahma',
                'username' => 'sitir',
                'location' => 'Kos Anggrek',
                'room' => 'Kamar 203',
                'due_date' => '30 Okt 2026',
                'status_label' => 'Aman (7 Hari)',
                'status_type' => 'safe',
            ],
            [
                'tenant_name' => 'Dewi Lestari',
                'username' => 'dewil',
                'location' => 'Kos Anggrek',
                'room' => 'Kamar 205',
                'due_date' => '05 Nov 2026',
                'status_label' => 'Lunas',
                'status_type' => 'paid',
            ],
        ]);
    }
@endphp

<div
    class="space-y-6"
    x-data="{
        searchQuery: '',
        statusFilter: 'all',
        items: {{ Js::from($items) }},
        get filteredItems() {
            return this.items.filter(item => {
                const matchSearch = this.searchQuery === '' ||
                    item.tenant_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    item.username.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    item.room.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    item.location.toLowerCase().includes(this.searchQuery.toLowerCase());

                const matchStatus = this.statusFilter === 'all' ||
                    (this.statusFilter === 'urgent' && item.status_type === 'danger') ||
                    (this.statusFilter === 'warning' && item.status_type === 'warning') ||
                    (this.statusFilter === 'safe' && (item.status_type === 'safe' || item.status_type === 'paid'));

                return matchSearch && matchStatus;
            });
        }
    }"
>
    {{-- 1. Metric Cards (2x2 on Mobile, 4 Cols on Desktop) --}}
    <div class="grid grid-cols-2 gap-2.5 sm:gap-4 lg:grid-cols-4">
        {{-- Total Penyewa --}}
        <article class="rounded-xl border border-gray-200 bg-white p-3 sm:p-4 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Penyewa</span>
            <div class="mt-2.5 flex items-baseline justify-between gap-2">
                <strong class="text-lg font-bold tracking-tight text-gray-800 dark:text-white/90 sm:text-xl">{{ $totalActive }}</strong>
                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    Aktif
                </span>
            </div>
        </article>

        {{-- Jatuh Tempo Hari Ini / Terlambat --}}
        <article class="rounded-xl border border-gray-200 bg-white p-3 sm:p-4 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Jatuh Tempo</span>
            <div class="mt-2.5 flex items-baseline justify-between gap-2">
                <strong class="text-lg font-bold tracking-tight text-error-600 dark:text-error-400 sm:text-xl">{{ $urgentCount }}</strong>
                <span class="rounded-full bg-error-50 px-2 py-0.5 text-[11px] font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                    Hari Ini / Lewat
                </span>
            </div>
        </article>

        {{-- Mendekati Jatuh Tempo --}}
        <article class="rounded-xl border border-gray-200 bg-white p-3 sm:p-4 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Mendekati JT</span>
            <div class="mt-2.5 flex items-baseline justify-between gap-2">
                <strong class="text-lg font-bold tracking-tight text-warning-600 dark:text-warning-400 sm:text-xl">{{ $approachingCount }}</strong>
                <span class="rounded-full bg-warning-50 px-2 py-0.5 text-[11px] font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                    &le; 7 Hari
                </span>
            </div>
        </article>

        {{-- Aman / Lunas --}}
        <article class="rounded-xl border border-gray-200 bg-white p-3 sm:p-4 shadow-theme-xs transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-gray-700">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Aman / Lunas</span>
            <div class="mt-2.5 flex items-baseline justify-between gap-2">
                <strong class="text-lg font-bold tracking-tight text-success-600 dark:text-success-400 sm:text-xl">{{ $safeCount }}</strong>
                <span class="rounded-full bg-success-50 px-2 py-0.5 text-[11px] font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                    Terkonfirmasi
                </span>
            </div>
        </article>
    </div>

    {{-- 2. Monitoring Container & Controls --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Header & Search / Filters --}}
        <div class="border-b border-gray-100 p-4 dark:border-gray-800 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white/90 sm:text-lg">Pemantauan Status Pembayaran Penyewa</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        Pantau status kepatuhan sewa tanpa nominal finansial (sesuai hak akses staf).
                    </p>
                </div>

                {{-- Status Pills Filter --}}
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar rounded-lg border border-gray-200 bg-gray-50 p-0.5 dark:border-gray-700 dark:bg-gray-800/60 self-start sm:self-auto">
                    <button
                        type="button"
                        @click="statusFilter = 'all'"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                        :class="statusFilter === 'all' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white/90 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    >Semua</button>
                    <button
                        type="button"
                        @click="statusFilter = 'urgent'"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                        :class="statusFilter === 'urgent' ? 'bg-white text-error-600 shadow-theme-xs dark:bg-gray-700 dark:text-error-400 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    >Jatuh Tempo</button>
                    <button
                        type="button"
                        @click="statusFilter = 'warning'"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                        :class="statusFilter === 'warning' ? 'bg-white text-warning-600 shadow-theme-xs dark:bg-gray-700 dark:text-warning-400 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    >Mendekati</button>
                    <button
                        type="button"
                        @click="statusFilter = 'safe'"
                        class="rounded-md px-2.5 py-1 text-xs font-medium transition-all"
                        :class="statusFilter === 'safe' ? 'bg-white text-success-600 shadow-theme-xs dark:bg-gray-700 dark:text-success-400 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    >Aman</button>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="mt-3.5 relative">
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Cari nama penyewa, nomor kamar, atau lokasi..."
                    class="h-9 w-full rounded-lg border border-gray-300 bg-white pe-3 ps-9 text-xs sm:text-sm text-gray-700 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- 3. Mobile Card View (Zero Horizontal Scrolling) --}}
        <div class="divide-y divide-gray-100 sm:hidden dark:divide-gray-800">
            <template x-for="(item, idx) in filteredItems" :key="idx">
                <div class="p-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90 truncate" x-text="item.tenant_name"></p>
                            <p class="text-[11px] text-gray-400" x-text="'@' + item.username"></p>
                        </div>
                        <span
                            class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="{
                                'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400': item.status_type === 'paid',
                                'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400': item.status_type === 'pending',
                                'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400': item.status_type === 'warning',
                                'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400': item.status_type === 'danger',
                                'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300': item.status_type === 'safe',
                            }"
                            x-text="item.status_label"
                        ></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400 pt-0.5">
                        <div class="min-w-0">
                            <span class="text-[10px] text-gray-400 block">Kamar & Lokasi</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300 truncate block" x-text="item.room + ' · ' + item.location"></span>
                        </div>
                        <div class="min-w-0 text-end">
                            <span class="text-[10px] text-gray-400 block">Jatuh Tempo</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300 truncate block" x-text="item.due_date"></span>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="filteredItems.length === 0" class="py-8 text-center text-xs text-gray-400">
                Tidak ada data penyewa yang sesuai dengan filter.
            </div>
        </div>

        {{-- 4. Desktop Table View --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5 text-start">Penyewa</th>
                        <th class="px-5 py-3.5 text-start">Kamar & Lokasi</th>
                        <th class="px-5 py-3.5 text-start">Jatuh Tempo</th>
                        <th class="px-5 py-3.5 text-center">Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                    <template x-for="(item, idx) in filteredItems" :key="idx">
                        <tr class="hover:bg-gray-50/50 transition dark:hover:bg-white/[0.01]">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <span x-text="item.tenant_name.slice(0, 2).toUpperCase()"></span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white/90 text-sm" x-text="item.tenant_name"></p>
                                        <p class="text-xs text-gray-400" x-text="'@' + item.username"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-600 dark:text-gray-300">
                                <span class="font-medium text-gray-800 dark:text-white/90" x-text="item.room"></span>
                                <span class="text-gray-400 block" x-text="item.location"></span>
                            </td>
                            <td class="px-5 py-3.5 text-xs font-medium text-gray-700 dark:text-gray-300" x-text="item.due_date"></td>
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="{
                                        'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400': item.status_type === 'paid',
                                        'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400': item.status_type === 'pending',
                                        'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400': item.status_type === 'warning',
                                        'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400': item.status_type === 'danger',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300': item.status_type === 'safe',
                                    }"
                                    x-text="item.status_label"
                                ></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredItems.length === 0">
                        <td class="px-5 py-8 text-center text-xs text-gray-400" colspan="4">
                            Tidak ada data penyewa yang sesuai dengan filter.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
