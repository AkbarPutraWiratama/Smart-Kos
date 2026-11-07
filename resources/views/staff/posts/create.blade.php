@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Buat Pengumuman Baru" />

<div
    x-data="{
        targetType: 'all',
        selectedLocationId: '{{ old('location_id', '') }}',
        allRooms: {{ Js::from($allRooms->map(fn($r) => [
            'id' => $r->id,
            'location_id' => $r->location_id,
            'label' => 'Kamar ' . $r->room_number . ' (Lantai ' . $r->floor_name . ')',
        ])) }},
        get filteredRooms() {
            if (!this.selectedLocationId) return [];
            return this.allRooms.filter(r => String(r.location_id) === String(this.selectedLocationId));
        }
    }"
    class="mx-auto max-w-3xl space-y-6"
>
    <!-- Validation Errors -->
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

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
            <div>
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Form Pembuatan Pengumuman
                </h3>
                <p class="text-xs text-gray-400">
                    Publikasikan informasi penting untuk seluruh kos, lokasi tertentu, atau kamar khusus
                </p>
            </div>

            <a
                href="{{ route('staff.dashboard') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <form action="{{ route('staff.posts.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf

            <!-- Target Selection -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Target Penerima Pengumuman <span class="text-error-500">*</span>
                </label>
                <div class="mt-2 grid grid-cols-3 gap-3">
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-3 text-xs text-center transition" :class="targetType === 'all' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold shadow-theme-xs dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="all" x-model="targetType" class="sr-only">
                        <span>Semua Lokasi & Penghuni</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-3 text-xs text-center transition" :class="targetType === 'location' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold shadow-theme-xs dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="location" x-model="targetType" class="sr-only">
                        <span>Satu Lokasi Tertentu</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-3 text-xs text-center transition" :class="targetType === 'room' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold shadow-theme-xs dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="room" x-model="targetType" class="sr-only">
                        <span>Kamar Tertentu</span>
                    </label>
                </div>
            </div>

            <!-- Target Location Selector (when location or room) -->
            <div x-show="targetType === 'location' || targetType === 'room'" x-cloak class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02] space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Pilih Lokasi Kos <span class="text-error-500">*</span>
                    </label>
                    <select
                        name="location_id"
                        x-model="selectedLocationId"
                        :required="targetType === 'location' || targetType === 'room'"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="">-- Pilih Lokasi Kos --</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->address }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Room Selector (when room) -->
                <div x-show="targetType === 'room'" x-cloak>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                        Pilih Kamar Sasaran <span class="text-error-500">*</span>
                    </label>
                    <select
                        name="room_id"
                        :required="targetType === 'room'"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="">-- Pilih Kamar --</option>
                        <template x-for="r in filteredRooms" :key="r.id">
                            <option :value="r.id" x-text="r.label"></option>
                        </template>
                    </select>
                    <p x-show="selectedLocationId && filteredRooms.length === 0" class="mt-1.5 text-xs text-warning-600">
                        Tidak ada kamar terdaftar pada lokasi yang dipilih.
                    </p>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Judul Pengumuman <span class="text-error-500">*</span>
                </label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    maxlength="255"
                    placeholder="Contoh: Jadwal Pemeliharaan Fasilitas Kos"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                />
            </div>

            <!-- Content -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Isi Pengumuman <span class="text-error-500">*</span>
                </label>
                <textarea
                    name="content"
                    rows="6"
                    required
                    placeholder="Tuliskan isi pengumuman atau instruksi yang jelas bagi penghuni..."
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                >{{ old('content') }}</textarea>
            </div>

            <!-- Optional Image Upload -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Lampiran Gambar/Foto (Opsional)
                </label>
                <input
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs text-gray-800 shadow-theme-xs file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200 dark:border-gray-800 dark:bg-gray-900 dark:text-white dark:file:bg-gray-800 dark:file:text-gray-300"
                />
                <p class="mt-1 text-[11px] text-gray-400">
                    Format: JPG, JPEG, PNG, WEBP. Maksimal ukuran 2MB.
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a
                    href="{{ route('staff.dashboard') }}"
                    class="rounded-xl border border-gray-200 px-5 py-2.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 active:scale-95 transition"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span>Publikasikan Pengumuman</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

