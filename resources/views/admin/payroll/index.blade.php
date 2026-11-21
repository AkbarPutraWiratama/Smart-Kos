@extends('layouts.app')

@section('content')
<div
    x-data="{
        activeTab: '{{ request('tab', $activeTab ?? 'payroll') }}',
        payrollModalOpen: false,
        advanceModalOpen: false,
        deletePayrollModalOpen: false,
        deleteAdvanceModalOpen: false,
        statusAdvanceModalOpen: false,

        // Staff data lookup for reactive calculations
        staffAdvances: {
            @foreach ($staffMembers as $staff)
                {{ $staff->id }}: {{ (float) $staff->totalOutstandingCashAdvance() }},
            @endforeach
        },

        // Form Payroll Data
        payrollId: null,
        payrollStaffId: '{{ $staffMembers->first()->id ?? '' }}',
        payrollGross: '',
        payrollDeduction: '0',
        payrollPaymentDate: '{{ date('Y-m-d') }}',
        payrollNotes: '',
        payrollSettleAdvances: true,
        payrollFormAction: '{{ route('admin.payroll.store') }}',
        payrollFormMethod: 'POST',

        // Form Cash Advance Data
        advanceId: null,
        advanceStaffId: '{{ $staffMembers->first()->id ?? '' }}',
        advanceAmount: '',
        advanceDate: '{{ date('Y-m-d') }}',
        advanceStatus: 'outstanding',
        advanceNotes: '',
        advanceFormAction: '{{ route('admin.cash-advances.store') }}',
        advanceFormMethod: 'POST',

        // Delete targets
        deletePayrollAction: '',
        deletePayrollText: '',
        deleteAdvanceAction: '',
        deleteAdvanceText: '',

        // Quick status modal
        statusAdvanceAction: '',
        statusAdvanceTarget: 'outstanding',
        statusAdvanceStaffName: '',

        get selectedStaffAdvance() {
            return this.staffAdvances[this.payrollStaffId] || 0;
        },

        get calculatedNetAmount() {
            const gross = parseFloat(this.payrollGross) || 0;
            const ded = parseFloat(this.payrollDeduction) || 0;
            return Math.max(0, gross - ded);
        },

        openCreatePayroll() {
            this.payrollId = null;
            this.payrollStaffId = '{{ $staffMembers->first()->id ?? '' }}';
            this.payrollGross = '';
            this.payrollDeduction = '0';
            this.payrollPaymentDate = '{{ date('Y-m-d') }}';
            this.payrollNotes = '';
            this.payrollSettleAdvances = true;
            this.payrollFormAction = '{{ route('admin.payroll.store') }}';
            this.payrollFormMethod = 'POST';
            this.payrollModalOpen = true;
        },

        openEditPayroll(item) {
            this.payrollId = item.id;
            this.payrollStaffId = item.staff_id;
            this.payrollGross = item.gross_amount;
            this.payrollDeduction = item.cash_advance_deduction;
            this.payrollPaymentDate = item.payment_date;
            this.payrollNotes = item.notes || '';
            this.payrollSettleAdvances = false;
            this.payrollFormAction = '/admin/payroll/' + item.id;
            this.payrollFormMethod = 'PUT';
            this.payrollModalOpen = true;
        },

        openCreateAdvance() {
            this.advanceId = null;
            this.advanceStaffId = '{{ $staffMembers->first()->id ?? '' }}';
            this.advanceAmount = '';
            this.advanceDate = '{{ date('Y-m-d') }}';
            this.advanceStatus = 'outstanding';
            this.advanceNotes = '';
            this.advanceFormAction = '{{ route('admin.cash-advances.store') }}';
            this.advanceFormMethod = 'POST';
            this.advanceModalOpen = true;
        },

        openEditAdvance(item) {
            this.advanceId = item.id;
            this.advanceStaffId = item.staff_id;
            this.advanceAmount = item.amount;
            this.advanceDate = item.advance_date;
            this.advanceStatus = item.status;
            this.advanceNotes = item.notes || '';
            this.advanceFormAction = '/admin/cash-advances/' + item.id;
            this.advanceFormMethod = 'PUT';
            this.advanceModalOpen = true;
        },

        openQuickStatusAdvance(item) {
            this.statusAdvanceAction = '/admin/cash-advances/' + item.id + '/status';
            this.statusAdvanceTarget = item.status;
            this.statusAdvanceStaffName = item.staff_name;
            this.statusAdvanceModalOpen = true;
        },

        confirmDeletePayroll(id, staffName, date, amount) {
            this.deletePayrollAction = '/admin/payroll/' + id;
            this.deletePayrollText = staffName + ' (' + date + ' - ' + amount + ')';
            this.deletePayrollModalOpen = true;
        },

        confirmDeleteAdvance(id, staffName, date, amount) {
            this.deleteAdvanceAction = '/admin/cash-advances/' + id;
            this.deleteAdvanceText = staffName + ' (' + date + ' - ' + amount + ')';
            this.deleteAdvanceModalOpen = true;
        }
    }"
    class="space-y-6 w-full max-w-full min-w-0"
>
    {{-- Breadcrumb --}}
    <x-common.page-breadcrumb pageTitle="Penggajian &amp; Kasbon Staff" />

    {{-- Top Navigation Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a
                href="{{ route('admin.users.index') }}"
                class="border-b-2 border-transparent py-3 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
            >
                Akun Penyewa
            </a>
            <a
                href="{{ route('admin.staff.index') }}"
                class="border-b-2 border-transparent py-3 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
            >
                Akun Staff Kos
            </a>
            <a
                href="{{ route('admin.payroll.index') }}"
                class="border-b-2 border-brand-500 py-3 text-sm font-semibold text-brand-500 dark:border-brand-400 dark:text-brand-400 transition"
                aria-current="page"
            >
                Penggajian &amp; Kasbon Staff
            </a>
        </nav>
    </div>

    {{-- ── 1. Summary Metric Cards ── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Net Paid --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Gaji Bersih Dibayar</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-success-600 dark:text-success-400">
                    Rp {{ number_format($totalNetPaid, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    {{ $totalPayrollCount }} transaksi penggajian
                </p>
            </div>
        </div>

        {{-- Total Gross Amount --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Gaji Kotor</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    Rp {{ number_format($totalGross, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    Sebelum potongan kasbon
                </p>
            </div>
        </div>

        {{-- Total Deductions --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Potongan Kasbon</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-error-600 dark:text-error-400">
                    Rp {{ number_format($totalDeductions, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    Dipotong dari gaji staff
                </p>
            </div>
        </div>

        {{-- Outstanding Cash Advances --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kasbon Aktif (Outstanding)</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-warning-600 dark:text-warning-400">
                    Rp {{ number_format($totalOutstandingAdvances, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    Belum lunas / sisa dicicil
                </p>
            </div>
        </div>
    </div>

    {{-- ── 2. Filter Bar & Action Buttons ── --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                    Pusat Pengelolaan Gaji &amp; Kasbon Staff
                </h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Catat penggajian staff dengan potongan kasbon, kelola pinjaman kasbon aktif, dan otomatis dibukukan ke jurnal pembukuan umum Smart Kos.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="openCreatePayroll()"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:ring-2 focus:ring-brand-500/20"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Gaji Staff</span>
                </button>

                <button
                    type="button"
                    @click="openCreateAdvance()"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.06]"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Catat Kasbon Baru</span>
                </button>
            </div>
        </div>

        {{-- Filter Controls Form --}}
        <form method="GET" action="{{ route('admin.payroll.index') }}" class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            <input type="hidden" name="tab" :value="activeTab">

            {{-- Staff Filter --}}
            <select
                name="staff_id"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all">Semua Staff</option>
                @foreach ($staffMembers as $staff)
                    <option value="{{ $staff->id }}" {{ (string)$selectedStaffId === (string)$staff->id ? 'selected' : '' }}>
                        {{ $staff->name }} {{ $staff->isActive() ? '' : '(Nonaktif)' }}
                    </option>
                @endforeach
            </select>

            {{-- Month Filter --}}
            <select
                name="month"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Sepanjang Tahun (12 Bulan)</option>
                @foreach ($monthsNames as $mNum => $mLabel)
                    <option value="{{ $mNum }}" {{ (string)$selectedMonth === (string)$mNum ? 'selected' : '' }}>
                        Bulan {{ $mLabel }}
                    </option>
                @endforeach
            </select>

            {{-- Year Filter --}}
            <select
                name="year"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                @for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ $selectedYear === $y ? 'selected' : '' }}>
                        Tahun {{ $y }}
                    </option>
                @endfor
            </select>

            {{-- Status Filter (when on advances tab) --}}
            <template x-if="activeTab === 'advances'">
                <select
                    name="status"
                    onchange="this.form.submit()"
                    class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                >
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status Kasbon</option>
                    <option value="outstanding" {{ $selectedStatus === 'outstanding' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="partially_repaid" {{ $selectedStatus === 'partially_repaid' ? 'selected' : '' }}>Sebagian Lunas</option>
                    <option value="repaid" {{ $selectedStatus === 'repaid' ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ $selectedStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </template>

            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[200px]">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari staff, username, atau catatan..."
                    class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 placeholder-gray-400 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                />
            </div>

            <button
                type="submit"
                class="h-10 rounded-xl bg-gray-100 px-4 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                Filter
            </button>

            @if ($selectedStaffId !== 'all' || $selectedMonth !== 'all' || $selectedYear !== (int)date('Y') || !empty($search) || $selectedStatus !== 'all')
                <a
                    href="{{ route('admin.payroll.index', ['tab' => request('tab', 'payroll')]) }}"
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

    {{-- ── 3. Sub-Navigation Tabs (Payroll vs Advances) ── --}}
    <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-800">
        <button
            type="button"
            @click="activeTab = 'payroll'"
            class="relative pb-3 text-sm font-semibold transition"
            :class="activeTab === 'payroll' ? 'text-brand-500 border-b-2 border-brand-500 dark:text-brand-400 dark:border-brand-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
        >
            <span>Riwayat Penggajian (Payroll)</span>
            <span class="ms-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $totalPayrollCount }}
            </span>
        </button>

        <button
            type="button"
            @click="activeTab = 'advances'"
            class="relative pb-3 text-sm font-semibold transition"
            :class="activeTab === 'advances' ? 'text-brand-500 border-b-2 border-brand-500 dark:text-brand-400 dark:border-brand-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
        >
            <span>Catatan Kasbon Staff (Cash Advances)</span>
            <span class="ms-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ $totalAdvancesCount }}
            </span>
        </button>
    </div>

    {{-- ── 4. Tab Content: Payroll Records Table ── --}}
    <div x-show="activeTab === 'payroll'" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-start text-xs dark:divide-gray-800">
                <thead class="bg-gray-50 text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Staff Penerima</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Tanggal Bayar</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Gaji Kotor</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Potongan Kasbon</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Gaji Bersih</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Pembukuan Keuangan</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Catatan &amp; Dicatat Oleh</th>
                        <th scope="col" class="px-5 py-3.5 text-end font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($payrollRecords as $pr)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-600 font-bold text-xs dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ strtoupper(substr($pr->staff?->name ?? 'S', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white/90">
                                            {{ $pr->staff?->name ?? '-' }}
                                        </p>
                                        <p class="text-[11px] text-gray-400">
                                            {{ '@' . ($pr->staff?->username ?? '-') }}
                                            @if ($pr->staff && ! $pr->staff->isActive())
                                                <span class="ms-1 inline-flex rounded-full bg-gray-100 px-1.5 py-0.2 text-[10px] text-gray-600 dark:bg-gray-800 dark:text-gray-400">Nonaktif</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-700 dark:text-gray-300">
                                {{ $pr->formattedPaymentDate() }}
                            </td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">
                                {{ $pr->formattedGrossAmount() }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-error-600 dark:text-error-400">
                                {{ (float)$pr->cash_advance_deduction > 0 ? '- ' . $pr->formattedDeduction() : 'Rp 0' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-success-600 text-sm dark:text-success-400">
                                    {{ $pr->formattedNetAmount() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($pr->financialRecord)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-[11px] font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Terbuku (ID: #{{ $pr->financialRecord->id }})</span>
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                        Belum Terbuku
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-xs text-gray-700 dark:text-gray-300 max-w-[200px] truncate" title="{{ $pr->notes }}">
                                    {{ $pr->notes ?: '-' }}
                                </p>
                                <p class="text-[10px] text-gray-400">
                                    Dicatat: {{ $pr->recordedBy?->name ?? 'Sistem' }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-end">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="openEditPayroll({
                                            id: {{ $pr->id }},
                                            staff_id: {{ $pr->staff_id }},
                                            gross_amount: {{ $pr->gross_amount }},
                                            cash_advance_deduction: {{ $pr->cash_advance_deduction }},
                                            payment_date: '{{ $pr->payment_date ? $pr->payment_date->format('Y-m-d') : '' }}',
                                            notes: '{{ addslashes($pr->notes ?? '') }}'
                                        })"
                                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.06] dark:hover:text-white"
                                        title="Edit Penggajian"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        @click="confirmDeletePayroll(
                                            {{ $pr->id }},
                                            '{{ addslashes($pr->staff?->name ?? 'Staff') }}',
                                            '{{ $pr->formattedPaymentDate() }}',
                                            '{{ $pr->formattedNetAmount() }}'
                                        )"
                                        class="rounded-lg p-1.5 text-error-500 hover:bg-error-50 hover:text-error-700 dark:hover:bg-error-500/10"
                                        title="Hapus Penggajian"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada catatan penggajian staff yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payrollRecords->hasPages())
            <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                {{ $payrollRecords->links() }}
            </div>
        @endif
    </div>

    {{-- ── 5. Tab Content: Cash Advances Table ── --}}
    <div x-show="activeTab === 'advances'" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-start text-xs dark:divide-gray-800">
                <thead class="bg-gray-50 text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Staff Peminjam</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Tanggal Kasbon</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Nominal Kasbon</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Status Pelunasan</th>
                        <th scope="col" class="px-5 py-3.5 text-start font-medium">Keperluan &amp; Dicatat Oleh</th>
                        <th scope="col" class="px-5 py-3.5 text-end font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($cashAdvances as $ca)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-warning-50 text-warning-600 font-bold text-xs dark:bg-warning-500/15 dark:text-warning-400">
                                        {{ strtoupper(substr($ca->staff?->name ?? 'S', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white/90">
                                            {{ $ca->staff?->name ?? '-' }}
                                        </p>
                                        <p class="text-[11px] text-gray-400">
                                            {{ '@' . ($ca->staff?->username ?? '-') }}
                                            @if ($ca->staff && ! $ca->staff->isActive())
                                                <span class="ms-1 inline-flex rounded-full bg-gray-100 px-1.5 py-0.2 text-[10px] text-gray-600 dark:bg-gray-800 dark:text-gray-400">Nonaktif</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-700 dark:text-gray-300">
                                {{ $ca->formattedAdvanceDate() }}
                            </td>
                            <td class="px-5 py-3.5 font-bold text-gray-800 text-sm dark:text-white/90">
                                {{ $ca->formattedAmount() }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $ca->statusBadgeClass() }}">
                                    <span>{{ $ca->statusLabel() }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-xs text-gray-700 dark:text-gray-300 max-w-[220px] truncate" title="{{ $ca->notes }}">
                                    {{ $ca->notes ?: '-' }}
                                </p>
                                <p class="text-[10px] text-gray-400">
                                    Dicatat: {{ $ca->recordedBy?->name ?? 'Admin' }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-end">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Quick Status Toggle Button --}}
                                    <button
                                        type="button"
                                        @click="openQuickStatusAdvance({
                                            id: {{ $ca->id }},
                                            status: '{{ $ca->status }}',
                                            staff_name: '{{ addslashes($ca->staff?->name ?? 'Staff') }}'
                                        })"
                                        class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                                        title="Ubah Status Kasbon"
                                    >
                                        Ubah Status
                                    </button>

                                    <button
                                        type="button"
                                        @click="openEditAdvance({
                                            id: {{ $ca->id }},
                                            staff_id: {{ $ca->staff_id }},
                                            amount: {{ $ca->amount }},
                                            advance_date: '{{ $ca->advance_date ? $ca->advance_date->format('Y-m-d') : '' }}',
                                            status: '{{ $ca->status }}',
                                            notes: '{{ addslashes($ca->notes ?? '') }}'
                                        })"
                                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.06] dark:hover:text-white"
                                        title="Edit Kasbon"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        @click="confirmDeleteAdvance(
                                            {{ $ca->id }},
                                            '{{ addslashes($ca->staff?->name ?? 'Staff') }}',
                                            '{{ $ca->formattedAdvanceDate() }}',
                                            '{{ $ca->formattedAmount() }}'
                                        )"
                                        class="rounded-lg p-1.5 text-error-500 hover:bg-error-50 hover:text-error-700 dark:hover:bg-error-500/10"
                                        title="Hapus Kasbon"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada catatan pinjaman kasbon staff yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($cashAdvances->hasPages())
            <div class="border-t border-gray-200 p-4 dark:border-gray-800">
                {{ $cashAdvances->links() }}
            </div>
        @endif
    </div>

    {{-- ── 6. Modal: Catat / Edit Penggajian Staff ── --}}
    <div
        x-show="payrollModalOpen"
        x-cloak
        @keydown.escape.window="payrollModalOpen = false"
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
    >
        <div
            @click.outside="payrollModalOpen = false"
            class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="payrollId ? 'Edit Catatan Gaji Staff' : 'Catat Penggajian Staff Baru'"></h3>
                <button type="button" @click="payrollModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="payrollFormAction" method="POST" class="mt-4 space-y-4">
                @csrf
                <template x-if="payrollFormMethod === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                {{-- Staff Selector --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Staff Penerima Gaji <span class="text-error-500">*</span>
                    </label>
                    <select
                        name="staff_id"
                        x-model="payrollStaffId"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    >
                        @foreach ($staffMembers as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->name }} ({{ '@' . $staff->username }}) {{ $staff->isActive() ? '' : '[Nonaktif]' }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Outstanding Advance Indicator --}}
                    <div
                        x-show="selectedStaffAdvance > 0"
                        class="mt-2 flex items-center gap-2 rounded-xl bg-warning-50 p-2.5 text-xs text-warning-800 border border-warning-200 dark:bg-warning-500/10 dark:border-warning-500/20 dark:text-warning-300"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>
                            Staff ini memiliki pinjaman kasbon aktif sebesar <strong x-text="'Rp ' + Number(selectedStaffAdvance).toLocaleString('id-ID')"></strong>.
                        </span>
                    </div>
                </div>

                {{-- Payment Date --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tanggal Pembayaran Gaji <span class="text-error-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="payment_date"
                        x-model="payrollPaymentDate"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    />
                </div>

                {{-- Gross Salary & Deduction --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Gaji Kotor (Gross) <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-xs text-gray-400">Rp</span>
                            <input
                                type="number"
                                name="gross_amount"
                                x-model="payrollGross"
                                required
                                min="1"
                                placeholder="3500000"
                                class="w-full rounded-xl border border-gray-200 bg-white ps-10 pe-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Potongan Kasbon
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-xs text-gray-400">Rp</span>
                            <input
                                type="number"
                                name="cash_advance_deduction"
                                x-model="payrollDeduction"
                                min="0"
                                placeholder="0"
                                class="w-full rounded-xl border border-gray-200 bg-white ps-10 pe-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                {{-- Live Net Amount Display --}}
                <div class="rounded-xl border border-brand-100 bg-brand-50/50 p-3 dark:border-brand-900/30 dark:bg-brand-950/20">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-600 dark:text-gray-300">Gaji Bersih Diterima Staff (Net):</span>
                        <span class="text-base font-bold text-success-600 dark:text-success-400" x-text="'Rp ' + Number(calculatedNetAmount).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                {{-- Settle advances checkbox --}}
                <template x-if="!payrollId">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="settle_advances"
                            value="1"
                            x-model="payrollSettleAdvances"
                            class="rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800"
                        />
                        <span class="text-xs text-gray-700 dark:text-gray-300">
                            Potong langsung pinjaman kasbon aktif tertua milik staff ini
                        </span>
                    </label>
                </template>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Catatan Tambahan (Opsional)
                    </label>
                    <textarea
                        name="notes"
                        x-model="payrollNotes"
                        rows="2"
                        placeholder="Contoh: Gaji pokok bulan berjalan + bonus piket akhir pekan..."
                        class="w-full rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    ></textarea>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button
                        type="button"
                        @click="payrollModalOpen = false"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600"
                    >
                        Simpan Gaji
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 7. Modal: Catat / Edit Kasbon Staff ── --}}
    <div
        x-show="advanceModalOpen"
        x-cloak
        @keydown.escape.window="advanceModalOpen = false"
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
    >
        <div
            @click.outside="advanceModalOpen = false"
            class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90" x-text="advanceId ? 'Edit Catatan Kasbon' : 'Catat Kasbon Staff Baru'"></h3>
                <button type="button" @click="advanceModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="advanceFormAction" method="POST" class="mt-4 space-y-4">
                @csrf
                <template x-if="advanceFormMethod === 'PUT'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                {{-- Staff Selector --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Staff Peminjam Kasbon <span class="text-error-500">*</span>
                    </label>
                    <select
                        name="staff_id"
                        x-model="advanceStaffId"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    >
                        @foreach ($staffMembers as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->name }} ({{ '@' . $staff->username }}) {{ $staff->isActive() ? '' : '[Nonaktif]' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Advance Date & Amount --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Kasbon <span class="text-error-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="advance_date"
                            x-model="advanceDate"
                            required
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nominal Kasbon <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 start-0 flex items-center ps-3 text-xs text-gray-400">Rp</span>
                            <input
                                type="number"
                                name="amount"
                                x-model="advanceAmount"
                                required
                                min="1"
                                placeholder="200000"
                                class="w-full rounded-xl border border-gray-200 bg-white ps-10 pe-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Status Kasbon <span class="text-error-500">*</span>
                    </label>
                    <select
                        name="status"
                        x-model="advanceStatus"
                        required
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    >
                        <option value="outstanding">Belum Lunas (Outstanding)</option>
                        <option value="partially_repaid">Sebagian Lunas (Partially Repaid)</option>
                        <option value="repaid">Lunas (Repaid)</option>
                        <option value="cancelled">Dibatalkan (Cancelled)</option>
                    </select>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Keperluan / Catatan
                    </label>
                    <textarea
                        name="notes"
                        x-model="advanceNotes"
                        rows="2"
                        placeholder="Contoh: Keperluan mendesak keluarga, dicicil 2 kali..."
                        class="w-full rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    ></textarea>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button
                        type="button"
                        @click="advanceModalOpen = false"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600"
                    >
                        Simpan Kasbon
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 8. Modal: Ubah Status Kasbon Cepat ── --}}
    <div
        x-show="statusAdvanceModalOpen"
        x-cloak
        @keydown.escape.window="statusAdvanceModalOpen = false"
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
    >
        <div
            @click.outside="statusAdvanceModalOpen = false"
            class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Ubah Status Kasbon
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Pilih status terbaru untuk kasbon staff <strong x-text="statusAdvanceStaffName"></strong>.
            </p>

            <form :action="statusAdvanceAction" method="POST" class="mt-4 space-y-3">
                @csrf
                <input type="hidden" name="_method" value="PATCH">

                <select
                    name="status"
                    x-model="statusAdvanceTarget"
                    required
                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                >
                    <option value="outstanding">Belum Lunas</option>
                    <option value="partially_repaid">Sebagian Lunas</option>
                    <option value="repaid">Lunas</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="statusAdvanceModalOpen = false"
                        class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs text-gray-700 dark:border-gray-800 dark:text-gray-300"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600"
                    >
                        Perbarui Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 9. Modal: Konfirmasi Hapus Gaji ── --}}
    <div
        x-show="deletePayrollModalOpen"
        x-cloak
        @keydown.escape.window="deletePayrollModalOpen = false"
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
    >
        <div
            @click.outside="deletePayrollModalOpen = false"
            class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Hapus Catatan Penggajian
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-600 dark:text-gray-300">
                Apakah Anda yakin ingin menghapus catatan gaji untuk: <br>
                <strong class="text-gray-800 dark:text-white" x-text="deletePayrollText"></strong>? <br>
                <span class="text-error-500 mt-1 block">Catatan pengeluaran gaji terkait di laporan keuangan juga akan otomatis dihapus.</span>
            </p>

            <form :action="deletePayrollAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <button
                    type="button"
                    @click="deletePayrollModalOpen = false"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="rounded-xl bg-error-600 px-4 py-2 text-xs font-semibold text-white hover:bg-error-700"
                >
                    Ya, Hapus Penggajian
                </button>
            </form>
        </div>
    </div>

    {{-- ── 10. Modal: Konfirmasi Hapus Kasbon ── --}}
    <div
        x-show="deleteAdvanceModalOpen"
        x-cloak
        @keydown.escape.window="deleteAdvanceModalOpen = false"
        class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
    >
        <div
            @click.outside="deleteAdvanceModalOpen = false"
            class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        Hapus Catatan Kasbon
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Tindakan ini akan menghapus data pinjaman kasbon.
                    </p>
                </div>
            </div>

            <p class="mt-4 text-xs text-gray-600 dark:text-gray-300">
                Apakah Anda yakin ingin menghapus catatan kasbon: <br>
                <strong class="text-gray-800 dark:text-white" x-text="deleteAdvanceText"></strong>?
            </p>

            <form :action="deleteAdvanceAction" method="POST" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <button
                    type="button"
                    @click="deleteAdvanceModalOpen = false"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="rounded-xl bg-error-600 px-4 py-2 text-xs font-semibold text-white hover:bg-error-700"
                >
                    Ya, Hapus Kasbon
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
