@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Dashboard Penyewa" />

<div
    x-data="{
        selectedAnnouncement: null,
        isAnnouncementModalOpen: false,
    }"
    class="space-y-6"
>
    <!-- 1. Header Greeting & Profile Summary -->
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
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                            Penyewa
                        </span>
                        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                            Akun Aktif
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                        {{ '@' . $user->username }} &bull; {{ $user->email ?? 'Tanpa Email' }}
                    </p>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="flex flex-wrap items-center gap-2 pt-2 sm:pt-0">
                <a
                    href="{{ route('penyewa.payments.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-theme-xs transition hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Pembayaran</span>
                </a>
                <a
                    href="{{ route('penyewa.complaints.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-theme-xs transition hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Ajukan Aduan</span>
                </a>
            </div>
        </div>
    </div>

    @if ($activeAssignment)
        @php
            $room = $activeAssignment->room;
            $location = $room->location;
            $deadlineColor = $activeAssignment->getDeadlineColor();
            $daysUntilDue = $activeAssignment->getDaysUntilDue();
            $dueDate = $activeAssignment->getPaymentDueDate();
            $paymentStatus = $activeAssignment->getPaymentStatus();
        @endphp

        <!-- 2. Room Information & Tenancy Card -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Room & Floor Card (2 cols) -->
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 md:p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                                Informasi Kamar & Sewa Aktif
                            </h3>
                            <p class="text-xs text-gray-400">
                                Data sewa aktif Anda di lokasi kos
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-md bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                        Sewa Berjalan
                    </span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <!-- Room Number -->
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            Nomor Kamar
                        </span>
                        <p class="mt-1 text-xl font-bold text-brand-600 dark:text-brand-400">
                            {{ $room->room_number }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Kamar Anda
                        </p>
                    </div>

                    <!-- Floor -->
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            Lantai
                        </span>
                        <p class="mt-1 text-xl font-bold text-gray-800 dark:text-white/90">
                            Lantai {{ $room->floor_name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Tingkat Gedung
                        </p>
                    </div>

                    <!-- Location -->
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            Lokasi Kos
                        </span>
                        <p class="mt-1 truncate text-sm font-bold text-gray-800 dark:text-white/90" title="{{ $location->name }}">
                            {{ $location->name }}
                        </p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $location->address }}">
                            {{ $location->address }}
                        </p>
                    </div>

                    <!-- Rent Snapshot -->
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            Tarif Sewa
                        </span>
                        <p class="mt-1 text-sm font-bold text-gray-800 dark:text-white/90">
                            Rp {{ number_format($activeAssignment->rent_amount_snapshot, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            / bulan
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-gray-50 p-3 text-xs text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai Sewa:</span>
                        <span>{{ $activeAssignment->start_date ? $activeAssignment->start_date->format('d M Y') : '-' }}</span>
                    </div>
                    @if ($activeAssignment->end_date)
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Berakhir Pada:</span>
                            <span>{{ $activeAssignment->end_date->format('d M Y') }}</span>
                        </div>
                    @else
                        <span class="text-gray-400">Sewa Bulanan (Berjalan Otomatis)</span>
                    @endif
                </div>
            </div>

            <!-- 3. Payment Deadline & Payment Status Card (1 col) -->
            <div
                class="rounded-2xl border p-5 md:p-6 shadow-theme-xs transition-colors flex flex-col justify-between
                {{ $deadlineColor === 'red' ? 'border-error-300 bg-error-50/40 dark:border-error-500/30 dark:bg-error-500/5' : '' }}
                {{ $deadlineColor === 'yellow' ? 'border-warning-300 bg-warning-50/40 dark:border-warning-500/30 dark:bg-warning-500/5' : '' }}
                {{ $deadlineColor === 'gray' ? 'border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]' : '' }}"
            >
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Status Pembayaran
                        </span>
                        <!-- Status Badge -->
                        @if ($paymentStatus === 'Lunas')
                            <span class="inline-flex items-center rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                Lunas
                            </span>
                        @elseif ($paymentStatus === 'Menunggu Verifikasi')
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-400">
                                Verifikasi
                            </span>
                        @elseif ($paymentStatus === 'Menunggak' || $paymentStatus === 'Jatuh Tempo Hari Ini')
                            <span class="inline-flex items-center rounded-full bg-error-100 px-2.5 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                {{ $paymentStatus }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ $paymentStatus }}
                            </span>
                        @endif
                    </div>

                    <!-- Payment Deadline Display -->
                    <div class="mt-4">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Tenggat Pembayaran (Payment Deadline)
                        </span>
                        <p class="mt-1 text-2xl font-black text-gray-800 dark:text-white/90">
                            {{ $dueDate->format('d M Y') }}
                        </p>

                        <!-- Deadline Color Indicator -->
                        <div class="mt-3 flex items-center gap-2">
                            @if ($deadlineColor === 'gray')
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                    <span>&gt; 7 hari (Aman) &bull; Tersisa {{ $daysUntilDue }} hari</span>
                                </span>
                            @elseif ($deadlineColor === 'yellow')
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-warning-300 bg-warning-100 px-3 py-1.5 text-xs font-semibold text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/20 dark:text-warning-300">
                                    <span class="h-2 w-2 rounded-full bg-warning-500 animate-pulse"></span>
                                    <span>&lt; 7 hari &bull; Tersisa {{ $daysUntilDue }} hari</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-error-300 bg-error-100 px-3 py-1.5 text-xs font-semibold text-error-800 dark:border-error-500/30 dark:bg-error-500/20 dark:text-error-300">
                                    <span class="h-2 w-2 rounded-full bg-error-500 animate-ping"></span>
                                    <span>{{ $daysUntilDue === 0 ? 'Jatuh Tempo Hari Ini!' : 'Terlambat ' . abs($daysUntilDue) . ' hari!' }}</span>
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            @if ($paymentStatus === 'Lunas')
                                Tagihan bulan ini telah diselesaikan. Terima kasih atas ketepatan waktu Anda!
                            @else
                                Mohon lakukan transfer atau pembayaran sewa sebelum tanggal jatuh tempo.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <a
                        href="{{ route('penyewa.payments.index') }}"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 active:scale-95"
                    >
                        <span>Kelola Pembayaran & Tagihan &rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- No Active Room Assignment Notice -->
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="mt-3 text-base font-semibold text-gray-800 dark:text-white/90">
                Belum Ada Kamar Aktif Ditugaskan
            </h3>
            <p class="mx-auto mt-1 max-w-md text-xs text-gray-500 dark:text-gray-400">
                Akun Anda telah terdaftar sebagai penyewa, namun admin kos belum menetapkan kamar aktif untuk Anda. Silakan hubungi pengelola untuk proses check-in kamar.
            </p>
        </div>
    @endif

    <!-- 4. Location-Relevant Announcements Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Pengumuman & Informasi Kos
                </h3>
                <p class="text-xs text-gray-400">
                    Pemberitahuan terkini dari pengelola kos dan staf
                </p>
            </div>

            <!-- Location Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 rounded-xl bg-gray-100 p-1 dark:bg-gray-800 text-xs">
                <a
                    href="{{ route('penyewa.dashboard', ['location_filter' => 'relevant']) }}"
                    class="rounded-lg px-3 py-1.5 font-medium transition {{ $locationFilter === 'relevant' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    Semua Relevan
                </a>
                <a
                    href="{{ route('penyewa.dashboard', ['location_filter' => 'specific']) }}"
                    class="rounded-lg px-3 py-1.5 font-medium transition {{ $locationFilter === 'specific' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    Lokasi Saya {{ $assignedLocation ? "({$assignedLocation->name})" : '' }}
                </a>
                <a
                    href="{{ route('penyewa.dashboard', ['location_filter' => 'all']) }}"
                    class="rounded-lg px-3 py-1.5 font-medium transition {{ $locationFilter === 'all' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    Semua Lokasi
                </a>
            </div>
        </div>

        <!-- Announcements List (Tap/Click to View Full) -->
        <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($announcements as $post)
                @php
                    $isGeneral = $post->targets->contains('target_type', 'all');
                    $locationTarget = $post->targets->firstWhere('target_type', 'location');
                    $roomTarget = $post->targets->firstWhere('target_type', 'room');
                @endphp
                <div
                    @click="selectedAnnouncement = {{ json_encode($post) }}; isAnnouncementModalOpen = true"
                    class="group -mx-2 flex cursor-pointer flex-col gap-2 rounded-xl p-3 sm:p-4 transition hover:bg-gray-50 active:bg-gray-100 dark:hover:bg-white/[0.03] dark:active:bg-white/[0.05]"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="font-bold text-sm text-gray-800 group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400 transition">
                                {{ $post->title }}
                            </h4>

                            <!-- Target Badge -->
                            @if ($isGeneral)
                                <span class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                                    Semua Lokasi
                                </span>
                            @elseif ($locationTarget)
                                <span class="rounded bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:bg-purple-500/15 dark:text-purple-400">
                                    Lokasi: {{ $locationTarget->location?->name ?? 'Kos' }}
                                </span>
                            @elseif ($roomTarget)
                                <span class="rounded bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                                    Kamar: {{ $roomTarget->room?->room_number ?? '-' }}
                                </span>
                            @endif
                        </div>

                        <span class="shrink-0 text-xs text-gray-400">
                            {{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}
                        </span>
                    </div>

                    <p class="line-clamp-2 text-xs text-gray-600 dark:text-gray-300">
                        {{ $post->content }}
                    </p>

                    <div class="flex items-center justify-between text-[11px] text-gray-400 pt-1">
                        <span>Oleh: <strong class="font-medium text-gray-600 dark:text-gray-300">{{ $post->author?->name ?? 'Pengelola' }}</strong></span>
                        <span class="text-brand-500 font-medium group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 transition">
                            Ketuk untuk baca selengkapnya &rarr;
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Belum ada pengumuman untuk filter ini.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Announcement Detail Modal (Mobile & Desktop Friendly) -->
    <div
        x-show="isAnnouncementModalOpen"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center p-4"
    >
        <!-- Backdrop -->
        <div
            @click="isAnnouncementModalOpen = false"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
        ></div>

        <!-- Dialog Content -->
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 dark:border dark:border-gray-800"
            @click.away="isAnnouncementModalOpen = false"
        >
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-3 dark:border-gray-800">
                <div>
                    <span class="text-[11px] font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider">
                        Pengumuman Kos
                    </span>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white/90" x-text="selectedAnnouncement?.title"></h3>
                </div>
                <button
                    @click="isAnnouncementModalOpen = false"
                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="my-4 max-h-[60vh] overflow-y-auto space-y-3">
                <p class="text-xs text-gray-400" x-text="'Dipublikasikan pada: ' + (selectedAnnouncement?.published_at ? new Date(selectedAnnouncement.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-')"></p>
                <div class="text-sm leading-relaxed text-gray-700 whitespace-pre-line dark:text-gray-200" x-text="selectedAnnouncement?.content"></div>
            </div>

            <div class="border-t border-gray-100 pt-3 dark:border-gray-800 flex justify-end">
                <button
                    @click="isAnnouncementModalOpen = false"
                    type="button"
                    class="rounded-xl bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
