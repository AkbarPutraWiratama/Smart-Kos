@extends('layouts.app')

@section('content')
<div x-data="{ isDeleteLocationOpen: false }">
	<x-common.page-breadcrumb pageTitle="Edit Lokasi" />

	<x-common.component-card title="Edit informasi lokasi" desc="Perbarui informasi lokasi tanpa mengubah riwayat kamar.">
		<form method="POST" action="{{ route('admin.locations.update', $location) }}" enctype="multipart/form-data" class="space-y-6">
			@csrf
			@method('PUT')
			<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
				<div>
					<label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama lokasi</label>
					<input id="name" name="name" value="{{ old('name', $location->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					@error('name')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
				<div>
					<label for="floor_count" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah lantai</label>
					<input id="floor_count" name="floor_count" type="number" min="1" max="50" value="{{ old('floor_count', $location->floor_count) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					@error('floor_count')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
			</div>
			<div>
				<label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
				<textarea id="address" name="address" rows="3" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('address', $location->address) }}</textarea>
				@error('address')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
			</div>
			<div>
				<label for="google_maps_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Link Google Maps</label>
				<input id="google_maps_url" name="google_maps_url" type="url" value="{{ old('google_maps_url', $location->google_maps_url) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
				@error('google_maps_url')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
			</div>
			<div>
				<label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi fasilitas</label>
				<textarea id="description" name="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('description', $location->description) }}</textarea>
			</div>

			<!-- Section Foto Lokasi -->
			<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
				<x-smart-kos.location-photo-picker
					:photos="$location->photos"
					:legacyPath="$location->photo_path"
					:location="$location"
					inputId="edit_location_photos"
					inputName="photos"
					label="Foto lokasi"
					helperText="Pilih beberapa foto lokasi sekaligus, simpan, lalu ulangi untuk menambahkan foto lainnya tanpa menghapus foto sebelumnya. Maksimal 10 foto per sesi, masing-masing 4 MB."
				/>
			</div>

			<!-- Section Foto Kamar -->
			<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
				<x-smart-kos.location-photo-picker
					:photos="collect()"
					inputId="edit_room_photos"
					inputName="photos"
					label="Foto kamar"
					helperText="Tambahkan foto contoh kamar atau tipe kamar di lokasi ini. Foto akan tersimpan bersama galeri lokasi."
				/>
			</div>

			<div>
				<label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
				<select id="status" name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
					<option value="active" @selected(old('status', $location->status) === 'active')>Aktif</option>
					<option value="inactive" @selected(old('status', $location->status) === 'inactive')>Nonaktif</option>
				</select>
			</div>

			<!-- Bottom Action Buttons: Hapus Lokasi (level yang sama dengan Batal & Simpan Perubahan) -->
			<div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
				<div>
					<button type="button" @click="isDeleteLocationOpen = true" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-error-600 transition">
						Hapus lokasi
					</button>
				</div>
				<div class="flex flex-wrap items-center gap-3">
					<a href="{{ route('admin.locations.show', $location) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</a>
					<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 shadow-theme-xs transition">Simpan perubahan</button>
				</div>
			</div>
		</form>
	</x-common.component-card>

	<!-- Modal Konfirmasi Hapus Lokasi -->
	<div x-show="isDeleteLocationOpen" x-cloak @keydown.escape.window="isDeleteLocationOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
		<div @click="isDeleteLocationOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
		<div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
			<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Konfirmasi hapus lokasi</h2>
			<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Lokasi {{ $location->name }} akan dihapus permanen. Pastikan tidak ada penyewa aktif.</p>
			<form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="mt-5 space-y-4">
				@csrf
				@method('DELETE')
				<div class="flex-1">
					<label for="modal_location_password_page" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
					<input id="modal_location_password_page" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90" />
				</div>
				<div class="flex justify-end gap-3">
					<button type="button" @click="isDeleteLocationOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
					<button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Ya, hapus lokasi</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
