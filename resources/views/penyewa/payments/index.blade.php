@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Pembayaran Sewa" />

<div
    x-data="{
        activeTab: 'va',
        selectedBank: 'bca',
        copied: false,
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }"
    class="space-y-6"
>
    <!-- Session Messages -->
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
                <span>Terdapat kesalahan pengisian formulir:</span>
            </div>
            <ul class="mt-2 list-inside list-disc text-xs text-error-700 dark:text-error-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $assignment)
        <!-- Fallback Notice When Tenant Has No Room -->
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-gray-800 dark:text-white/90">
                Belum Ada Tagihan Sewa Aktif
            </h3>
            <p class="mx-auto mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">
                Anda saat ini belum memiliki penugasan kamar kos aktif. Formulir pembayaran dan Virtual Account akan tersedia otomatis setelah Anda ditempatkan di kamar oleh pengelola.
            </p>
        </div>
    @else
        @php
            $room = $assignment->room;
            $location = $room->location;
            $rentAmount = $assignment->rent_amount_snapshot ?? $room->rent_amount;
            $dueDate = $assignment->getPaymentDueDate();
            $paymentStatus = $assignment->getPaymentStatus();
            $badgeClass = $assignment->getDeadlineBadgeClass();
            $badgeLabel = $assignment->getDeadlineLabel();
            $vaAccount = $room->midtransVaAccount;
        @endphp

        <!-- 1. Billing Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Rent -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Nominal Sewa Bulanan</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">
                        Rp {{ number_format($rentAmount, 0, ',', '.') }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-400">Per bulan / kamar</p>
                </div>
            </div>

            <!-- Due Date -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Jatuh Tempo Pembayaran</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">
                        {{ $dueDate->translatedFormat('d M Y') }}
                    </h3>
                    <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $badgeClass }}">
                        {{ $badgeLabel }}
                    </span>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Status Pembayaran</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">
                        {{ $paymentStatus }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $assignment->isPaidForCurrentPeriod() ? 'Terbayar periode ini' : 'Menunggu pelunasan' }}
                    </p>
                </div>
            </div>

            <!-- Room & Location -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Kamar & Lokasi</span>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <h3 class="text-base font-bold text-gray-800 truncate dark:text-white/90">
                        Kamar No. {{ $room->room_number }} (Lt. {{ $room->floor_name }})
                    </h3>
                    <p class="mt-1 text-xs text-gray-400 truncate">
                        {{ $location->name }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 2. Payment Method Selector Tabs -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 dark:border-gray-800 gap-4">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                        Pilih Metode Pembayaran
                    </h3>
                    <p class="text-xs text-gray-400">
                        Gunakan Virtual Account otomatis Midtrans atau lakukan Transfer Manual dengan bukti bayar
                    </p>
                </div>

                <!-- Tab Navigation Buttons -->
                <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                    <button
                        type="button"
                        @click="activeTab = 'va'"
                        :class="activeTab === 'va' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Midtrans Virtual Account</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'manual'"
                        :class="activeTab === 'manual' ? 'bg-white text-gray-800 shadow-theme-xs dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white'"
                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Transfer Manual Bank</span>
                    </button>
                </div>
            </div>

            <!-- Tab Content 1: Midtrans VA -->
            <div x-show="activeTab === 'va'" class="mt-6 space-y-6">
                @if ($vaAccount && $vaAccount->status === 'active' && (! $vaAccount->expires_at || $vaAccount->expires_at->isFuture()))
                    <!-- Active VA Display Card -->
                    <div class="rounded-xl border border-brand-200 bg-brand-50/40 p-5 dark:border-brand-500/20 dark:bg-brand-500/5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                                    Virtual Account Aktif (Bank {{ strtoupper($vaAccount->bank_code) }})
                                </span>
                                <div class="mt-3 flex items-center gap-3">
                                    <span class="text-2xl font-black tracking-wider text-gray-800 dark:text-white font-mono">
                                        {{ $vaAccount->va_number }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="copyToClipboard('{{ $vaAccount->va_number }}')"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10a2 2 0 00-2 2v3a2 2 0 002 2h10a2 2 0 002-2v-3a2 2 0 00-2-2z" />
                                        </svg>
                                        <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    Total bayar: <span class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($vaAccount->amount, 0, ',', '.') }}</span>
                                    &bull; Berlaku hingga: <span class="font-medium text-error-600 dark:text-error-400">{{ $vaAccount->expires_at ? $vaAccount->expires_at->translatedFormat('d M Y H:i') : '24 Jam' }}</span>
                                </p>
                            </div>

                            <!-- Regenerate / Switch Bank Option -->
                            <div>
                                <form action="{{ route('penyewa.payments.va.create') }}" method="POST">
                                    @csrf
                                    <div class="flex items-center gap-2">
                                        <select
                                            name="bank_code"
                                            class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        >
                                            <option value="bca" @selected($vaAccount->bank_code === 'bca')>BCA Virtual Account</option>
                                            <option value="bni" @selected($vaAccount->bank_code === 'bni')>BNI Virtual Account</option>
                                            <option value="bri" @selected($vaAccount->bank_code === 'bri')>BRI Virtual Account</option>
                                            <option value="mandiri" @selected($vaAccount->bank_code === 'mandiri')>Mandiri Bill Payment</option>
                                            <option value="permata" @selected($vaAccount->bank_code === 'permata')>Permata VA</option>
                                        </select>
                                        <button
                                            type="submit"
                                            class="h-10 rounded-lg border border-brand-300 bg-brand-50 px-3 text-xs font-semibold text-brand-700 hover:bg-brand-100 transition dark:border-brand-500/30 dark:bg-brand-500/20 dark:text-brand-300"
                                        >
                                            Ganti Bank
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Payment Instructions Accordion -->
                        <div class="mt-5 border-t border-brand-200/60 pt-4 text-xs text-gray-600 dark:border-brand-500/20 dark:text-gray-400">
                            <p class="font-semibold text-gray-700 dark:text-gray-300">Cara Pembayaran Virtual Account:</p>
                            <ol class="mt-1.5 list-inside list-decimal space-y-1">
                                <li>Buka aplikasi Mobile Banking atau ATM bank Anda.</li>
                                <li>Pilih menu <strong>Transfer &gt; Virtual Account / Pembayaran Tagihan</strong>.</li>
                                <li>Masukkan nomor Virtual Account <span class="font-mono font-bold">{{ $vaAccount->va_number }}</span>.</li>
                                <li>Pastikan nominal transfer tepat sebesar <strong class="text-gray-800 dark:text-white">Rp {{ number_format($vaAccount->amount, 0, ',', '.') }}</strong>.</li>
                                <li>Konfirmasi transaksi. Status tagihan kamar Anda akan terbarui otomatis setelah pembayaran sukses.</li>
                            </ol>
                        </div>
                    </div>
                @else
                    <!-- Form to Generate First VA -->
                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-6 text-center dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-gray-800 dark:text-white/90">
                            Buat Nomor Virtual Account Otomatis
                        </h4>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-gray-500 dark:text-gray-400">
                            Pilih bank yang Anda gunakan untuk membayar sewa sebesar <strong>Rp {{ number_format($rentAmount, 0, ',', '.') }}</strong>.
                        </p>

                        <form action="{{ route('penyewa.payments.va.create') }}" method="POST" class="mt-5 max-w-xs mx-auto space-y-3">
                            @csrf
                            <select
                                name="bank_code"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="bca">BCA Virtual Account</option>
                                <option value="bni">BNI Virtual Account</option>
                                <option value="bri">BRI Virtual Account</option>
                                <option value="mandiri">Mandiri Bill Payment</option>
                                <option value="permata">Permata Virtual Account</option>
                            </select>
                            <button
                                type="submit"
                                class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
                            >
                                Dapatkan Nomor Virtual Account
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Tab Content 2: Manual Transfer Form -->
            <div x-show="activeTab === 'manual'" class="mt-6 space-y-6">
                <!-- Kos Destination Bank Accounts Card -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                        Rekening Tujuan Pengelola Kos
                    </h4>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @forelse ($destinationBanks as $dest)
                            <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                                <span class="inline-flex rounded-md bg-brand-50 px-2 py-0.5 text-[11px] font-bold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                    {{ $dest['bank'] }}
                                </span>
                                <p class="mt-2 font-mono text-sm font-bold text-gray-800 dark:text-white">
                                    {{ $dest['account_number'] }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    a.n. {{ $dest['account_name'] }}
                                </p>
                            </div>
                        @empty
                            <div class="sm:col-span-3 rounded-xl border border-dashed border-gray-200 p-4 text-center text-xs text-gray-400 dark:border-gray-800">
                                Informasi rekening tujuan belum dikonfigurasi. Silakan hubungi pengelola kos.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Form Upload Manual Payment -->
                <form
                    action="{{ route('penyewa.payments.manual.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02] space-y-4"
                >
                    @csrf
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white/90">
                            Unggah Bukti Transfer Manual
                        </h4>
                        @if ($rateLimited)
                            <span class="inline-flex items-center gap-1 rounded-md bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-300">
                                <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Cooldown 10 menit (sisa {{ $remainingMinutes }} mnt)
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <!-- Sender Bank -->
                        <div>
                            <label for="bank_name" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Bank Pengirim <span class="text-error-500">*</span>
                            </label>
                            <input
                                id="bank_name"
                                name="bank_name"
                                type="text"
                                placeholder="Contoh: BCA, Mandiri, Jago"
                                value="{{ old('bank_name') }}"
                                required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Sender Account Number -->
                        <div>
                            <label for="bank_account_number" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                No. Rekening Pengirim <span class="text-error-500">*</span>
                            </label>
                            <input
                                id="bank_account_number"
                                name="bank_account_number"
                                type="text"
                                placeholder="Nomor rekening Anda"
                                value="{{ old('bank_account_number') }}"
                                required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Amount Paid -->
                        <div>
                            <label for="amount" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Nominal Ditransfer (Rp) <span class="text-error-500">*</span>
                            </label>
                            <input
                                id="amount"
                                name="amount"
                                type="number"
                                value="{{ old('amount', (int)$rentAmount) }}"
                                required
                                min="1"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Proof File Upload -->
                    <div>
                        <label for="proof" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Foto / Struk Bukti Transfer (JPG, PNG, PDF maks 4MB) <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="proof"
                            name="proof"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,application/pdf"
                            required
                            class="block w-full text-xs text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-500/15 dark:file:text-brand-300"
                        />
                    </div>

                    <!-- Submission Notice -->
                    <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                        <p>&bull; Pengunggahan bukti transfer dibatasi maksimal 1 kali setiap 10 menit.</p>
                        <p>&bull; Setelah dikirim, pengelola akan memverifikasi pembayaran Anda secara manual.</p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            @disabled($rateLimited)
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span>{{ $rateLimited ? "Tunggu Cooldown ({$remainingMinutes} mnt)" : 'Kirim Bukti Pembayaran' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Payment History Section -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] space-y-6">
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Riwayat Pembayaran Sewa
                </h3>
                <p class="text-xs text-gray-400">
                    Daftar seluruh riwayat transaksi Virtual Account dan Transfer Manual kamar ini
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left: VA Payment History -->
                <div class="space-y-3">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                        Riwayat Virtual Account (Midtrans)
                    </h4>
                    @if ($vaPayments->isEmpty())
                        <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-xs text-gray-400 dark:border-gray-800">
                            Belum ada riwayat transaksi Virtual Account.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-gray-200 bg-gray-50/70 text-gray-600 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2.5 font-medium">Order ID</th>
                                        <th class="px-3 py-2.5 font-medium">Nominal</th>
                                        <th class="px-3 py-2.5 font-medium">Status</th>
                                        <th class="px-3 py-2.5 font-medium">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($vaPayments as $vp)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                            <td class="px-3 py-2.5 font-mono text-[11px] text-gray-700 dark:text-gray-300">
                                                {{ $vp->order_id }}
                                            </td>
                                            <td class="px-3 py-2.5 font-semibold text-gray-800 dark:text-white">
                                                Rp {{ number_format($vp->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2.5">
                                                @if (in_array($vp->transaction_status, ['settlement', 'paid', 'capture', 'success']))
                                                    <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                                        Lunas
                                                    </span>
                                                @elseif ($vp->transaction_status === 'pending')
                                                    <span class="inline-flex rounded-full bg-warning-50 px-2 py-0.5 text-[10px] font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                                        Pending
                                                    </span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                        {{ ucfirst($vp->transaction_status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400">
                                                {{ $vp->paid_at ? $vp->paid_at->format('d/m/Y H:i') : ($vp->transaction_time ? $vp->transaction_time->format('d/m/Y H:i') : '-') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Right: Manual Payment History -->
                <div class="space-y-3">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                        Riwayat Transfer Manual
                    </h4>
                    @if ($manualPayments->isEmpty())
                        <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-xs text-gray-400 dark:border-gray-800">
                            Belum ada riwayat pengajuan transfer manual.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                            <table class="w-full text-left text-xs">
                                <thead class="border-b border-gray-200 bg-gray-50/70 text-gray-600 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2.5 font-medium">Bank / Nominal</th>
                                        <th class="px-3 py-2.5 font-medium">Status</th>
                                        <th class="px-3 py-2.5 font-medium">Bukti</th>
                                        <th class="px-3 py-2.5 font-medium">Diajukan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($manualPayments as $mp)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                            <td class="px-3 py-2.5">
                                                <div class="font-medium text-gray-800 dark:text-white">
                                                    {{ $mp->bank_name }}
                                                </div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    Rp {{ number_format($mp->amount, 0, ',', '.') }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                @if ($mp->status === 'approved')
                                                    <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                                        Disetujui
                                                    </span>
                                                @elseif ($mp->status === 'rejected')
                                                    <span class="inline-flex rounded-full bg-error-50 px-2 py-0.5 text-[10px] font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400" title="{{ $mp->rejection_reason }}">
                                                        Ditolak
                                                    </span>
                                                    @if ($mp->rejection_reason)
                                                        <p class="mt-0.5 text-[10px] text-error-600 dark:text-error-400 italic">
                                                            {{ Str::limit($mp->rejection_reason, 25) }}
                                                        </p>
                                                    @endif
                                                @else
                                                    <span class="inline-flex rounded-full bg-warning-50 px-2 py-0.5 text-[10px] font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                                        Menunggu
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5">
                                                @if ($mp->proof_path)
                                                    <a
                                                        href="{{ asset('storage/' . $mp->proof_path) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400"
                                                    >
                                                        <span>Lihat</span>
                                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400">
                                                {{ $mp->submitted_at ? $mp->submitted_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
