@extends('layouts.fullscreen-layout')

@section('content')
<div class="relative min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-between">
    <!-- Navbar -->
    <header class="border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-500 font-bold text-xl">
                    SK
                </div>
                <span class="text-xl font-bold text-gray-900 dark:text-white">Smart Kos</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('guest.rooms') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition">
                    Lokasi & Kamar
                </a>
                @auth
                    @php
                        $dashboardRoute = match (auth()->user()->role) {
                            'admin' => route('admin.dashboard'),
                            'staff' => route('staff.dashboard'),
                            'penyewa' => route('penyewa.dashboard'),
                            default => route('home'),
                        };
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">
                        Masuk ke Sistem
                    </a>
                @endauth
                <!-- Dark mode toggle -->
                <button @click.prevent="$store.theme.toggle()" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                    <svg class="hidden fill-current dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill="currentColor"/></svg>
                    <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" fill="currentColor"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Content -->
    <main class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 mb-4 border border-brand-200 dark:border-brand-500/20">
                Sistem Manajemen Kos Modern & Terpadu
            </span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-6">
                Hunian Kos Nyaman, Pengelolaan Lebih Efisien
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                Smart Kos memberikan kemudahan pengelolaan kamar, pemantauan status okupansi, pembayaran fleksibel, serta penanganan aduan yang cepat dan transparan.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('guest.rooms') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-6 py-3.5 text-base font-medium text-white shadow-theme-sm hover:bg-brand-600 transition">
                    Lihat Lokasi & Kamar
                    <svg class="w-5 h-5 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </a>
                @auth
                    <a href="{{ $dashboardRoute }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-3.5 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Buka Dashboard ({{ ucfirst(auth()->user()->role) }})
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-3.5 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Masuk Portal Penghuni / Staff
                    </a>
                @endauth
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto w-full mb-16">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800/60 p-6 text-center shadow-theme-xs">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Lokasi Kos</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $locationsCount }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800/60 p-6 text-center shadow-theme-xs">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Kamar Tersedia</p>
                <p class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $availableRooms }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800/60 p-6 text-center shadow-theme-xs">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Kapasitas Kamar</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalRooms }}</p>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="border-t border-gray-200 dark:border-gray-800 pt-10 max-w-4xl mx-auto w-full">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-6">Hubungi Pengelola & Petugas</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if ($ownerContact)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800/40 p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-500/10 text-brand-500 flex items-center justify-center font-bold">
                            👑
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Pemilik / Pengelola Utama</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $ownerContact->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ownerContact->email }}</p>
                        </div>
                    </div>
                @endif
                @foreach ($staffContacts as $staff)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800/40 p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-light-500/10 text-blue-light-500 flex items-center justify-center font-bold">
                            👷
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Petugas / Staff Operasional</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $staff->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $staff->email }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-200 dark:border-gray-800 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} Smart Kos. Hak Cipta Dilindungi.
    </footer>
</div>
@endsection
