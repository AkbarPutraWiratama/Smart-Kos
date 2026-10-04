@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tambah Lokasi" />

    <x-common.component-card title="Informasi lokasi" desc="Tambahkan lokasi kos baru sebelum membuat kamar di dalamnya.">
        <form method="POST" action="{{ route('admin.locations.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama lokasi</label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
                    @error('name')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="floor_count" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah lantai</label>
                    <input id="floor_count" name="floor_count" type="number" min="1" max="50" value="{{ old('floor_count', 1) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
                    @error('floor_count')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                <textarea id="address" name="address" rows="3" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('address') }}</textarea>
                @error('address')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="google_maps_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Link Google Maps</label>
                <input id="google_maps_url" name="google_maps_url" type="url" value="{{ old('google_maps_url') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
                @error('google_maps_url')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi fasilitas</label>
                <textarea id="description" name="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('description') }}</textarea>
            </div>
            <x-smart-kos.location-photo-picker :photos="collect()" inputId="create_location_photos" />
            <input type="hidden" name="status" value="active" />
            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('admin.locations.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</a>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan lokasi</button>
            </div>
        </form>
    </x-common.component-card>
@endsection
