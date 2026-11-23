@extends('layouts.fullscreen-layout')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-between">
    <!-- Navbar -->
    <header class="border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between gap-2">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2 shrink-0">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-500 font-bold text-base sm:text-xl">
                    SK
                </div>
                <span class="text-base sm:text-xl font-bold text-gray-900 dark:text-white">Smart Kos</span>
            </a>

            {{-- Right actions --}}
            <div class="flex items-center gap-1.5 sm:gap-3">
                {{-- Back to Home --}}
                <a href="/"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs sm:text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition">
                    <svg class="h-3.5 w-3.5 shrink-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="hidden sm:inline">Kembali ke Beranda</span>
                    <span class="inline sm:hidden">Beranda</span>
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
                    <a href="{{ $dashboardRoute }}"
                       class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">
                        <span class="hidden sm:inline">Buka Dashboard</span>
                        <span class="inline sm:hidden">Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">
                        Masuk
                    </a>
                @endauth

                {{-- Dark mode toggle --}}
                <button @click.prevent="$store.theme.toggle()"
                        class="p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="hidden fill-current dark:block" width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill="currentColor"/></svg>
                    <svg class="fill-current dark:hidden" width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" fill="currentColor"/></svg>
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
                @if ($locations->count() === 1)
                    Lihat ketersediaan kamar secara transparan (hanya status ketersediaan, tanpa informasi pribadi).
                @else
                    Pilih lokasi kos untuk melihat ketersediaan kamar secara transparan (hanya status ketersediaan tanpa informasi pribadi).
                @endif
            </p>
        </div>

        <!-- Location Filter Tabs: only shown when there are multiple locations -->
        @if ($locations->count() > 1)
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
                @php
                    $guestPhotos = collect();
                    if ($selectedLocation->photo_path) {
                        $guestPhotos->push([
                            'url' => asset('storage/' . $selectedLocation->photo_path),
                            'badge' => 'Foto Lokasi',
                            'type' => 'location',
                        ]);
                    }
                    // Location photos first, followed by room photos
                    $sortedGuestPhotos = $selectedLocation->photos->sortBy(fn($p) => ($p->type ?? 'location') === 'room' ? 1 : 0);
                    foreach ($sortedGuestPhotos as $photo) {
                        $photoType = $photo->type ?? 'location';
                        $guestPhotos->push([
                            'url' => asset('storage/' . $photo->path),
                            'badge' => $photoType === 'room' ? 'Foto Kamar' : 'Foto Lokasi',
                            'type' => $photoType,
                        ]);
                    }
                @endphp

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)] lg:items-stretch">
                    {{-- Foto Kos (Besar) --}}
                    <div x-data="{ currentPhoto: 0, photos: @js($guestPhotos->values()) }" class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-900 shadow-theme-xs h-72 sm:h-96 lg:h-full lg:min-h-[440px]">
                        @if ($guestPhotos->isNotEmpty())
                            <img :src="photos[currentPhoto].url" alt="Foto {{ $selectedLocation->name }}" class="h-full w-full object-cover min-h-[280px]" />
                            <!-- Photo Tag Badge -->
                            <div class="absolute top-3 start-3 z-10 pointer-events-none">
                                <span class="rounded-lg px-2.5 py-1 text-xs font-semibold shadow-md backdrop-blur-md"
                                    :class="photos[currentPhoto].type === 'room' ? 'bg-purple-600/90 text-white' : 'bg-brand-600/90 text-white'"
                                    x-text="photos[currentPhoto].badge">
                                </span>
                            </div>
                            @if ($guestPhotos->count() > 1)
                                <button type="button" @click="currentPhoto = (currentPhoto - 1 + photos.length) % photos.length" aria-label="Foto sebelumnya" class="absolute start-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-xl text-white hover:bg-black/80">&#8249;</button>
                                <button type="button" @click="currentPhoto = (currentPhoto + 1) % photos.length" aria-label="Foto berikutnya" class="absolute end-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-xl text-white hover:bg-black/80">&#8250;</button>
                                <span class="absolute bottom-3 start-1/2 -translate-x-1/2 rounded-full bg-black/60 px-2.5 py-1 text-xs text-white" x-text="`${currentPhoto + 1} / ${photos.length}`"></span>
                            @endif
                        @else
                            <div class="flex h-full min-h-[280px] items-center justify-center px-6 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada foto lokasi.</div>
                        @endif
                    </div>

                    {{-- Informasi Kos + Embed Map Kotak Sejajar Pojok Bawah --}}
                    <div class="flex flex-col justify-between h-full">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300 border border-brand-200/80 dark:border-brand-500/20">
                                    {{ $selectedLocation->floor_count }} Lantai
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300 border border-success-200/80 dark:border-success-500/20">
                                    {{ max(0, $selectedLocation->total_rooms - $selectedLocation->occupied_rooms) }} Kamar Kosong
                                </span>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1.5">{{ $selectedLocation->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                <span>{{ $selectedLocation->address }}</span>
                            </p>
                            @if ($selectedLocation->description)
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2.5 leading-relaxed">{{ $selectedLocation->description }}</p>
                            @endif
                        </div>

                        {{-- Embed Google Maps Kotak (Square: 1:1) sejajar pojok bawah foto kos --}}
                        @php
                            $embedUrl = $mapEmbedUrl ?? $selectedLocation->map_embed_url;
                        @endphp
                        @if ($embedUrl)
                            <div class="mt-4 pt-2 flex flex-col items-start">
                                <div class="mb-1.5 flex items-center justify-between w-48 sm:w-56 text-xs">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                        Peta Lokasi
                                    </span>
                                    @if ($selectedLocation->google_maps_url)
                                        <a href="{{ $selectedLocation->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 text-[11px] font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400 transition">
                                            <span>Buka Maps</span>
                                            <svg class="w-3 h-3 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        </a>
                                    @endif
                                </div>
                                {{-- Kotak (Square: 1x1) Interaktif --}}
                                <div class="w-48 h-48 sm:w-56 sm:h-56 aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 shadow-theme-xs">
                                    <iframe
                                        src="{{ $embedUrl }}"
                                        class="w-full h-full border-0 pointer-events-auto"
                                        allowfullscreen=""
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        title="Peta Lokasi {{ $selectedLocation->name }}"
                                    ></iframe>
                                </div>
                            </div>
                        @elseif ($selectedLocation->google_maps_url)
                            <div class="mt-4 pt-2">
                                <a href="{{ $selectedLocation->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    Buka di Google Maps
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rooms by Floor Layout -->
            <div class="space-y-6">
                @forelse ($roomsByFloor as $floorName => $rooms)
                    <x-common.component-card
                        :title="str_starts_with((string)$floorName, 'Lantai') ? $floorName : 'Lantai ' . $floorName"
                        :desc="count($rooms) . ' kamar terdaftar (' . collect($rooms)->where('is_occupied', false)->count() . ' kamar kosong).'"
                    >
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3">
                            @foreach ($rooms as $room)
                                <div class="rounded-xl border border-gray-200 p-4 transition dark:border-gray-800 bg-white dark:bg-gray-800/80 shadow-theme-xs {{ $room['is_occupied'] ? 'bg-error-50/20 dark:bg-error-500/5' : 'hover:border-brand-500' }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-800 dark:text-white/90">Kamar {{ $room['room_number'] }}</h4>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rp {{ number_format($room['rent_amount'], 0, ',', '.') }} / bulan</p>
                                        </div>
                                        <span class="rounded-full px-2.5 py-0.5 text-theme-xs font-medium {{ $room['is_occupied'] ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' }}">
                                            {{ $room['is_occupied'] ? 'Terisi' : 'Tersedia' }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex items-center gap-1.5 text-xs {{ $room['is_occupied'] ? 'text-gray-400 dark:text-gray-500' : 'text-success-600 dark:text-success-400 font-medium' }}">
                                        @if ($room['is_occupied'])
                                            <span>Kamar sedang dihuni</span>
                                        @else
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <span>Siap ditempati</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-common.component-card>
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
