@extends('layouts.fullscreen-layout')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-between">
    <!-- Navbar -->
    <header class="border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-500 font-bold text-xl">
                    SK
                </div>
                <span class="text-xl font-bold text-gray-900 dark:text-white">Smart Kos</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="/" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition">
                    Kembali ke Beranda
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
                        Masuk
                    </a>
                @endauth
                <button @click.prevent="$store.theme.toggle()" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                    <svg class="hidden fill-current dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill="currentColor"/></svg>
                    <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" fill="currentColor"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Daftar Lokasi & Ketersediaan Kamar
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Pilih lokasi kos untuk melihat ketersediaan kamar secara transparan (hanya status ketersediaan tanpa informasi pribadi).
            </p>
        </div>

        <!-- Location Filter Tabs -->
        @if ($locations->isNotEmpty())
            <div class="flex flex-wrap gap-3 mb-8">
                @foreach ($locations as $loc)
                    <a href="{{ route('guest.rooms', ['location_id' => $loc->id]) }}"
                        class="px-5 py-3 rounded-xl border text-sm font-medium transition flex items-center gap-3 {{ $selectedLocation && $selectedLocation->id === $loc->id ? 'bg-brand-500 text-white border-brand-500 shadow-theme-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                        <span>{{ $loc->name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $selectedLocation && $selectedLocation->id === $loc->id ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                            {{ max(0, $loc->total_rooms - $loc->occupied_rooms) }} kamar kosong
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($selectedLocation)
            <!-- Location Details Card -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 p-6 mb-8 shadow-theme-xs">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ $selectedLocation->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedLocation->address }}</p>
                        @if ($selectedLocation->description)
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ $selectedLocation->description }}</p>
                        @endif
                    </div>
                    @if ($selectedLocation->google_maps_url)
                        <div>
                            <a href="{{ $selectedLocation->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                Buka di Google Maps
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Legend -->
            <div class="flex items-center gap-6 mb-6 text-sm font-medium">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-success-500"></span>
                    <span class="text-gray-700 dark:text-gray-300">Tersedia (Kosong)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-md bg-error-500"></span>
                    <span class="text-gray-700 dark:text-gray-300">Terisi</span>
                </div>
            </div>

            <!-- Rooms by Floor Layout -->
            <div class="space-y-8">
                @forelse ($roomsByFloor as $floorName => $rooms)
                    <div>
                        <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-3 border-b border-gray-200 dark:border-gray-700 pb-2">
                            {{ $floorName }}
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            @foreach ($rooms as $room)
                                <div class="rounded-xl border p-4 text-center transition flex flex-col justify-between {{ $room['is_occupied'] ? 'border-error-200 bg-error-50/50 dark:border-error-500/20 dark:bg-error-500/5' : 'border-success-200 bg-success-50/50 dark:border-success-500/20 dark:bg-success-500/5' }}">
                                    <div>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                                            {{ $room['room_number'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            Rp {{ number_format($room['rent_amount'], 0, ',', '.') }}/bln
                                        </p>
                                    </div>
                                    <div>
                                        @if ($room['is_occupied'])
                                            <span class="inline-block px-2.5 py-1 rounded-md text-xs font-semibold bg-error-500 text-white">
                                                Terisi
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-1 rounded-md text-xs font-semibold bg-success-500 text-white">
                                                Tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        Belum ada kamar yang terdaftar di lokasi ini.
                    </div>
                @endforelse
            </div>
        @else
            <div class="text-center py-16 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800">
                <p class="text-gray-500 dark:text-gray-400">Belum ada lokasi aktif yang tersedia.</p>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-200 dark:border-gray-800 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} Smart Kos. Hak Cipta Dilindungi.
    </footer>
</div>
@endsection
