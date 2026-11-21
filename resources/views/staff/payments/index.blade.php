@extends('layouts.app')

@section('content')
<div class="space-y-6 w-full max-w-full min-w-0">
    <x-common.page-breadcrumb pageTitle="Status Pembayaran Penyewa" />

    {{-- Role Notice --}}
    <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Pemantauan Status &amp; Jatuh Tempo Pembayaran Penyewa
            </h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Gunakan halaman ini untuk memantau penghuni yang mendekati jatuh tempo sewa atau memerlukan follow-up. Sesuai kebijakan keamanan, nominal sewa dirahasiakan untuk akun staff.
            </p>
        </div>
        <div class="hidden sm:flex shrink-0 items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>Akses Terbatas: Tanpa Nominal Finansial</span>
        </div>
    </div>

    {{-- ── 1. Operational Metric Cards ── --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {{-- Total Tenants --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Penghuni Aktif</span>
            <div class="mt-2 flex items-baseline justify-between">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $totalTenants }}
                </h3>
                <span class="text-xs text-gray-400">Kamar</span>
            </div>
        </div>

        {{-- Paid --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-xs font-medium text-success-600 dark:text-success-400">Lunas Bulan Ini</span>
            <div class="mt-2 flex items-baseline justify-between">
                <h3 class="text-2xl font-bold text-success-600 dark:text-success-400">
                    {{ $paidCount }}
                </h3>
                <span class="text-xs text-success-600/70 dark:text-success-400/70">Aman</span>
            </div>
        </div>

        {{-- Pending Verification --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-xs font-medium text-warning-600 dark:text-warning-400">Menunggu Verifikasi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <h3 class="text-2xl font-bold text-warning-600 dark:text-warning-400">
                    {{ $pendingVerificationCount }}
                </h3>
                <span class="text-xs text-warning-600/70 dark:text-warning-400/70">Diproses Admin</span>
            </div>
        </div>

        {{-- Urgent (Due <= 7 Days / Overdue) --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-xs font-medium text-error-600 dark:text-error-400">Perlu Konfirmasi / Jatuh Tempo</span>
            <div class="mt-2 flex items-baseline justify-between">
                <h3 class="text-2xl font-bold text-error-600 dark:text-error-400">
                    {{ $urgentCount }}
                </h3>
                <span class="text-xs text-error-600/70 dark:text-error-400/70">&le; 7 Hari</span>
            </div>
        </div>
    </div>

    {{-- ── 2. Filters & Search Bar ── --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        {{-- Status Filter Tabs --}}
        <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800 overflow-x-auto">
            <a
                href="{{ route('staff.payments.index', ['status' => 'all', 'location_id' => $selectedLocationId, 'search' => $search]) }}"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition {{ $statusFilter === 'all' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Semua</span>
                <span class="rounded-full bg-gray-200 px-1.5 py-0.2 text-[10px] font-bold text-gray-600 dark:bg-gray-600 dark:text-gray-200">
                    {{ $totalTenants }}
                </span>
            </a>

            <a
                href="{{ route('staff.payments.index', ['status' => 'urgent', 'location_id' => $selectedLocationId, 'search' => $search]) }}"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition {{ $statusFilter === 'urgent' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Mendesak (&le; 7 Hari)</span>
                @if ($urgentCount > 0)
                    <span class="rounded-full bg-error-500 text-white px-1.5 py-0.2 text-[10px] font-bold">
                        {{ $urgentCount }}
                    </span>
                @endif
            </a>

            <a
                href="{{ route('staff.payments.index', ['status' => 'pending', 'location_id' => $selectedLocationId, 'search' => $search]) }}"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition {{ $statusFilter === 'pending' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Menunggu Verifikasi</span>
                @if ($pendingVerificationCount > 0)
                    <span class="rounded-full bg-warning-500 text-white px-1.5 py-0.2 text-[10px] font-bold">
                        {{ $pendingVerificationCount }}
                    </span>
                @endif
            </a>

            <a
                href="{{ route('staff.payments.index', ['status' => 'paid', 'location_id' => $selectedLocationId, 'search' => $search]) }}"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition {{ $statusFilter === 'paid' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
            >
                <span>Sudah Lunas</span>
                <span class="rounded-full bg-success-500/20 px-1.5 py-0.2 text-[10px] font-bold text-success-700 dark:text-success-300">
                    {{ $paidCount }}
                </span>
            </a>
        </div>

        {{-- Location Dropdown & Search Form --}}
        <form method="GET" action="{{ route('staff.payments.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            {{-- Location Filter --}}
            <select
                name="location_id"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all">Semua Lokasi Kos</option>
                @foreach ($locations as $loc)
                    <option value="{{ $loc->id }}" {{ (string)$selectedLocationId === (string)$loc->id ? 'selected' : '' }}>
                        {{ $loc->name }}
                    </option>
                @endforeach
            </select>

            {{-- Search Bar --}}
            <div class="relative w-full sm:w-56">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari penghuni, kamar..."
                    class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3.5 pe-9 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                />
                <button type="submit" class="absolute end-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>

            @if (!empty($search) || (!empty($selectedLocationId) && $selectedLocationId !== 'all'))
                <a
                    href="{{ route('staff.payments.index', ['status' => $statusFilter]) }}"
                    title="Reset Filter"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif
        </form>
    </div>

    {{-- ── 3. Table of Tenant Payment Statuses ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-start font-semibold">Kamar &amp; Lokasi</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Nama Penghuni</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Kontak (HP/WA)</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Jatuh Tempo</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Sisa Waktu</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Status Pembayaran</th>
                        <th class="py-3.5 px-4 text-end font-semibold">Aksi Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                    @forelse ($paginatedAssignments as $assignment)
                        @php
                            $room = $assignment->room;
                            $location = $room?->location;
                            $tenantUser = $assignment->tenantProfile?->user;
                            $phone = $assignment->tenantProfile?->phone ?? $tenantUser?->phone;
                            $dueDate = $assignment->getPaymentDueDate();
                            $days = $assignment->getDaysUntilDue();
                            $badgeClass = $assignment->getDeadlineBadgeClass();
                            $badgeLabel = $assignment->getDeadlineLabel();
                            $paymentStatus = $assignment->getPaymentStatus();
                            $isPaid = $assignment->isPaidForCurrentPeriod();
                            $hasPending = $assignment->hasPendingPayment();
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                            {{-- Room & Location --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 font-bold text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $room?->room_number ?? '-' }}
                                    </span>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            Kamar {{ $room?->room_number ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-gray-400">
                                            Lantai {{ $room?->floor_name ?? '-' }} &bull; {{ $location?->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Tenant Name --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $tenantUser?->name ?? 'Penyewa' }}
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    {{ '@' . ($tenantUser?->username ?? '-') }}
                                </div>
                            </td>

                            {{-- Phone / WA --}}
                            <td class="py-3.5 px-4">
                                @if ($phone)
                                    <a
                                        href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $phone)) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 font-mono text-gray-700 hover:text-brand-500 dark:text-gray-300"
                                        title="Kirim pesan WhatsApp"
                                    >
                                        <span>{{ $phone }}</span>
                                        <svg class="h-3 w-3 text-success-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">Tanpa nomor</span>
                                @endif
                            </td>

                            {{-- Due Date --}}
                            <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300">
                                <div class="font-medium">{{ $dueDate->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] text-gray-400">Siklus bulanan</div>
                            </td>

                            {{-- Days Remaining Badge --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>

                            {{-- Payment Status --}}
                            <td class="py-3.5 px-4">
                                @if ($hasPending)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-0.5 text-[11px] font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                        Menunggu Verifikasi
                                    </span>
                                @elseif ($isPaid)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-0.5 text-[11px] font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                        Lunas
                                    </span>
                                @elseif ($days <= 0)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-0.5 text-[11px] font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-error-500 animate-pulse"></span>
                                        {{ $days === 0 ? 'Jatuh Tempo Hari Ini' : 'Menunggak' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>

                            {{-- Follow Up Action --}}
                            <td class="py-3.5 px-4 text-end">
                                @if ($phone)
                                    <a
                                        href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $phone)) }}?text={{ urlencode('Halo ' . ($tenantUser?->name ?? 'Kak') . ', kami dari pengelola Smart Kos menginformasikan perihal sewa Kamar ' . ($room?->room_number ?? '') . ' (' . ($location?->name ?? '') . '). Status sewa saat ini: ' . $paymentStatus . '. Terima kasih.') }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-brand-600 hover:border-brand-500/50 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400 dark:hover:bg-gray-700 transition"
                                    >
                                        <svg class="h-3.5 w-3.5 text-success-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z" />
                                        </svg>
                                        <span>Hubungi WA</span>
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-400 dark:bg-gray-800">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Tidak ada data penghuni aktif
                                    @if ($statusFilter !== 'all')
                                        dengan filter status ini
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
        @if ($paginatedAssignments->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $paginatedAssignments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
