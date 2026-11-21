@extends('layouts.app')

@section('content')
<div
    x-data="{
        isModalOpen: false,
        modalMode: 'create', // 'create' | 'edit'
        modalAction: '{{ route('admin.expenses.store') }}',
        formData: {
            id: null,
            title: '',
            category_id: '{{ $categories->first()?->id ?? '' }}',
            location_id: '',
            amount: '',
            expense_date: '{{ date('Y-m-d') }}',
            description: '',
        },
        isReceiptModalOpen: false,
        receiptUrl: '',
        receiptTitle: '',
        receiptIsPdf: false,
        isDeleteModalOpen: false,
        deleteAction: '',
        deleteTitle: '',

        openCreateModal() {
            this.modalMode = 'create';
            this.modalAction = '{{ route('admin.expenses.store') }}';
            this.formData = {
                id: null,
                title: '',
                category_id: '{{ $categories->first()?->id ?? '' }}',
                location_id: '{{ $selectedLocationId !== 'all' ? $selectedLocationId : '' }}',
                amount: '',
                expense_date: '{{ date('Y-m-d') }}',
                description: '',
            };
            this.isModalOpen = true;
        },

        openEditModal(item) {
            this.modalMode = 'edit';
            this.modalAction = '/admin/expenses/' + item.id;
            this.formData = {
                id: item.id,
                title: item.title,
                category_id: item.category_id,
                location_id: item.location_id ?? '',
                amount: item.amount,
                expense_date: item.expense_date,
                description: item.description ?? '',
            };
            this.isModalOpen = true;
        },

        openReceiptModal(url, title) {
            this.receiptUrl = url;
            this.receiptTitle = title;
            this.receiptIsPdf = url.toLowerCase().endsWith('.pdf');
            this.isReceiptModalOpen = true;
        },

        openDeleteModal(id, title) {
            this.deleteAction = '/admin/expenses/' + id;
            this.deleteTitle = title;
            this.isDeleteModalOpen = true;
        }
    }"
    class="space-y-6 w-full max-w-full min-w-0"
>
    {{-- Breadcrumb --}}
    <x-common.page-breadcrumb pageTitle="Manajemen Pengeluaran (Operasional &amp; Pemeliharaan)" />

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="rounded-xl border border-success-200 bg-success-50 p-4 text-xs font-semibold text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 p-4 text-xs text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
            <p class="font-bold mb-1">Terdapat kesalahan pengisian data pengeluaran:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header & Primary Action Bar --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs lg:flex-row lg:items-center lg:justify-between dark:border-gray-800 dark:bg-white/[0.03]">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Pencatatan Biaya Operasional &amp; Pemeliharaan Kos
            </h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Catat pengeluaran rutin (listrik, air, internet) dan biaya insidental (servis AC, pompa air, perbaikan kamar). Setiap pencatatan otomatis tersinkronisasi ke laporan keuangan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                @click="openCreateModal()"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Catat Pengeluaran Baru</span>
            </button>

            <a
                href="{{ route('admin.finance.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Pusat Laporan Keuangan</span>
            </a>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Pengeluaran --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pengeluaran</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-error-600 dark:text-error-400">
                    Rp {{ number_format($totalExpensesAmount, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">
                    {{ $selectedMonth !== 'all' ? 'Bulan ' . ($monthsNames[(int)$selectedMonth] ?? '') : 'Tahun ' . $selectedYear }}
                </p>
            </div>
        </div>

        {{-- Beban Operasional --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Beban Operasional Rutin</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    Rp {{ number_format($operationalTotal, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">Listrik, air, Wi-Fi, keamanan</p>
            </div>
        </div>

        {{-- Biaya Pemeliharaan / Perbaikan --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Biaya Pemeliharaan &amp; Servis</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    Rp {{ number_format($maintenanceTotal, 0, ',', '.') }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">AC, pompa air, lampu, renovasi</p>
            </div>
        </div>

        {{-- Total Catatan --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Jumlah Transaksi</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $totalExpensesCount }}
                </h3>
                <p class="mt-1 text-xs text-gray-400">Catatan pengeluaran tersimpan</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex flex-wrap items-center gap-3">
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

            {{-- Category Type Filter --}}
            <select
                name="type"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all" {{ $selectedType === 'all' ? 'selected' : '' }}>Semua Tipe Biaya</option>
                <option value="operational" {{ $selectedType === 'operational' ? 'selected' : '' }}>Operasional</option>
                <option value="maintenance" {{ $selectedType === 'maintenance' ? 'selected' : '' }}>Pemeliharaan / Insidental</option>
                <option value="other" {{ $selectedType === 'other' ? 'selected' : '' }}>Lain-lain</option>
            </select>

            {{-- Category Filter --}}
            <select
                name="category_id"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string)$selectedCategoryId === (string)$cat->id ? 'selected' : '' }}>
                        {{ $cat->name }} ({{ $cat->typeLabel() }})
                    </option>
                @endforeach
            </select>

            {{-- Month Filter --}}
            <select
                name="month"
                onchange="this.form.submit()"
                class="h-10 rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
            >
                <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                @foreach ($monthsNames as $mNum => $mLabel)
                    <option value="{{ $mNum }}" {{ (string)$selectedMonth === (string)$mNum ? 'selected' : '' }}>
                        {{ $mLabel }}
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

            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[200px]">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari judul, kategori, lokasi..."
                    class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3.5 pe-9 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                />
                <button type="submit" class="absolute end-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>

            @if ($selectedLocationId !== 'all' || $selectedCategoryId !== 'all' || $selectedType !== 'all' || $selectedMonth !== (string)date('n') || $selectedYear !== (int)date('Y') || !empty($search))
                <a
                    href="{{ route('admin.expenses.index') }}"
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

    {{-- Expense Table Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-start font-semibold">Tanggal</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Judul &amp; Keterangan</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Kategori &amp; Tipe</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Lokasi Kos</th>
                        <th class="py-3.5 px-4 text-end font-semibold">Nominal</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Bukti Nota</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Pencatat</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                    @forelse ($expenses as $expense)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                            {{-- Date --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $expense->expense_date ? $expense->expense_date->translatedFormat('d M Y') : '-' }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    {{ $expense->created_at ? $expense->created_at->format('H:i') . ' WIB' : '-' }}
                                </div>
                            </td>

                            {{-- Title & Description --}}
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ $expense->title }}
                                </div>
                                @if ($expense->description)
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                                        {{ $expense->description }}
                                    </div>
                                @endif
                            </td>

                            {{-- Category & Type --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-gray-800 dark:text-white/90">
                                    {{ $expense->category?->name ?? 'Tanpa Kategori' }}
                                </div>
                                @if ($expense->category)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold mt-0.5 {{ $expense->category->typeBadgeClass() }}">
                                        {{ $expense->category->typeLabel() }}
                                    </span>
                                @endif
                            </td>

                            {{-- Location --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-medium text-gray-800 dark:text-white/90">
                                    {{ $expense->location?->name ?? 'Umum / Semua Lokasi' }}
                                </span>
                            </td>

                            {{-- Amount --}}
                            <td class="py-3.5 px-4 text-end whitespace-nowrap">
                                <span class="font-bold text-sm text-error-600 dark:text-error-400">
                                    -{{ $expense->formattedAmount() }}
                                </span>
                            </td>

                            {{-- Receipt Link --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if ($expense->hasReceipt())
                                    <button
                                        type="button"
                                        @click="openReceiptModal('{{ $expense->receiptUrl() }}', '{{ addslashes($expense->title) }}')"
                                        class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2 py-1 text-[11px] font-semibold text-brand-600 hover:bg-brand-50 hover:text-brand-700 dark:bg-gray-800 dark:text-brand-400"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Lihat Nota</span>
                                    </button>
                                @else
                                    <span class="text-gray-400 text-[11px] italic">Tidak ada nota</span>
                                @endif
                            </td>

                            {{-- Recorded By --}}
                            <td class="py-3.5 px-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ $expense->recordedBy?->name ?? 'Admin' }}
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click="openEditModal({
                                            id: {{ $expense->id }},
                                            title: '{{ addslashes($expense->title) }}',
                                            category_id: {{ $expense->category_id }},
                                            location_id: '{{ $expense->location_id ?? '' }}',
                                            amount: {{ (float)$expense->amount }},
                                            expense_date: '{{ $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '' }}',
                                            description: '{{ addslashes($expense->description ?? '') }}'
                                        })"
                                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:text-gray-400 dark:hover:bg-gray-800"
                                        title="Ubah Pengeluaran"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        @click="openDeleteModal({{ $expense->id }}, '{{ addslashes($expense->title) }}')"
                                        class="rounded-lg p-1.5 text-gray-500 hover:bg-error-50 hover:text-error-600 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-400"
                                        title="Hapus Pengeluaran"
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
                            <td colspan="8" class="py-12 text-center text-xs text-gray-400">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-400 dark:bg-gray-800">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m0 0l-6-6m6 6H3" />
                                    </svg>
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Tidak ada data pengeluaran operasional yang sesuai dengan filter.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($expenses->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    {{-- ── MODAL: CATAT / EDIT PENGELUARAN ── --}}
    <div
        x-show="isModalOpen"
        x-cloak
        class="fixed inset-0 z-999 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @keydown.escape.window="isModalOpen = false"
    >
        <div
            @click.away="isModalOpen = false"
            class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 overflow-y-auto max-h-[90vh]"
        >
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <h4 class="text-base font-bold text-gray-800 dark:text-white" x-text="modalMode === 'create' ? 'Catat Pengeluaran Baru' : 'Ubah Data Pengeluaran'">
                    Catat Pengeluaran Baru
                </h4>
                <button type="button" @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="modalAction" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <template x-if="modalMode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                {{-- Judul Pengeluaran --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Judul / Nama Pengeluaran <span class="text-error-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        x-model="formData.title"
                        required
                        placeholder="Contoh: Pembayaran Token Listrik Kos Melati Bulan Ini"
                        class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    />
                </div>

                {{-- Kategori & Tipe --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Kategori Pengeluaran <span class="text-error-500">*</span>
                        </label>
                        <select
                            name="category_id"
                            x-model="formData.category_id"
                            required
                            class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                        >
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }} ({{ $cat->typeLabel() }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lokasi Kos --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Lokasi Kos (Opsional)
                        </label>
                        <select
                            name="location_id"
                            x-model="formData.location_id"
                            class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                        >
                            <option value="">Umum / Tidak Terikat Lokasi</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Nominal & Tanggal --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Nominal Biaya (Rp) <span class="text-error-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="amount"
                            x-model="formData.amount"
                            required
                            min="1"
                            placeholder="Contoh: 350000"
                            class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Pengeluaran <span class="text-error-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="expense_date"
                            x-model="formData.expense_date"
                            required
                            class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                        />
                    </div>
                </div>

                {{-- Deskripsi / Catatan --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Keterangan Tambahan / Catatan Servis
                    </label>
                    <textarea
                        name="description"
                        x-model="formData.description"
                        rows="2"
                        placeholder="Rincian perbaikan, no kwitansi vendor, kontak teknisi..."
                        class="w-full rounded-xl border border-gray-200 bg-white p-3 text-xs text-gray-800 outline-none transition focus:border-brand-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white"
                    ></textarea>
                </div>

                {{-- Upload Bukti Nota / Kwitansi --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Foto Bukti Nota / Kwitansi / Faktur (Maks 5 MB)
                    </label>
                    <input
                        type="file"
                        name="receipt"
                        accept="image/jpeg,image/png,image/jpg,application/pdf"
                        class="block w-full text-xs text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-500/15 dark:file:text-brand-300"
                    />
                    <p class="text-[10px] text-gray-400 mt-1">Format diperbolehkan: JPG, PNG, atau PDF.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button
                        type="button"
                        @click="isModalOpen = false"
                        class="h-10 rounded-xl border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="h-10 rounded-xl bg-brand-500 px-5 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 transition"
                    >
                        Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL: PRATINJAU BUKTI NOTA / KWITANSI ── --}}
    <div
        x-show="isReceiptModalOpen"
        x-cloak
        class="fixed inset-0 z-999 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
        @keydown.escape.window="isReceiptModalOpen = false"
    >
        <div
            @click.away="isReceiptModalOpen = false"
            class="w-full max-w-2xl rounded-2xl bg-white p-5 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800"
        >
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white" x-text="'Bukti Nota: ' + receiptTitle">
                    Bukti Nota
                </h4>
                <button type="button" @click="isReceiptModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 flex items-center justify-center min-h-[300px] max-h-[70vh] overflow-auto">
                <template x-if="receiptIsPdf">
                    <iframe :src="receiptUrl" class="w-full h-[500px] rounded-xl border border-gray-200 dark:border-gray-800"></iframe>
                </template>
                <template x-if="!receiptIsPdf">
                    <img :src="receiptUrl" alt="Bukti Kwitansi" class="max-h-[60vh] max-w-full rounded-xl object-contain shadow-theme-xs" />
                </template>
            </div>

            <div class="mt-4 flex justify-between items-center pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                <a :href="receiptUrl" target="_blank" download class="font-semibold text-brand-600 hover:underline dark:text-brand-400">
                    Unduh Berkas Nota &rarr;
                </a>
                <button
                    type="button"
                    @click="isReceiptModalOpen = false"
                    class="h-9 rounded-xl bg-gray-100 px-4 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ── MODAL: KONFIRMASI HAPUS PENGELUARAN ── --}}
    <div
        x-show="isDeleteModalOpen"
        x-cloak
        class="fixed inset-0 z-999 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
        @keydown.escape.window="isDeleteModalOpen = false"
    >
        <div
            @click.away="isDeleteModalOpen = false"
            class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-center"
        >
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <h4 class="mt-4 text-sm font-bold text-gray-900 dark:text-white">
                Hapus Catatan Pengeluaran?
            </h4>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Apakah Anda yakin ingin menghapus data pengeluaran <span class="font-bold text-gray-800 dark:text-white" x-text="deleteTitle"></span>? Data transaksi ini juga akan dihapus dari buku laporan keuangan.
            </p>

            <form :action="deleteAction" method="POST" class="mt-5 flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button
                    type="button"
                    @click="isDeleteModalOpen = false"
                    class="h-10 flex-1 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="h-10 flex-1 rounded-xl bg-error-600 text-xs font-semibold text-white shadow-theme-xs hover:bg-error-700 transition"
                >
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
