@extends('layouts.app')

@section('content')
<div x-data="{ isDeleteLocationOpen: false }">
	<x-common.page-breadcrumb
		pageTitle="Edit Lokasi"
		:breadcrumbs="[
			['label' => 'Lokasi & Kamar', 'url' => route('admin.locations.index')],
			['label' => $location->name, 'url' => route('admin.locations.show', $location)],
		]"
	/>

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
			<div class="mb-4 flex items-center gap-3">
				<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500">
					<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
						<path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
					</svg>
				</div>
				<div>
					<h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Konfirmasi Hapus Lokasi</h2>
					<p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan</p>
				</div>
			</div>

			@if ($hasActiveTenants)
				<!-- Red Warning when location still has active tenants -->
				<div class="mb-4 rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
					<div class="flex items-start gap-3">
						<svg class="mt-0.5 h-5 w-5 shrink-0 text-error-500" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
						</svg>
						<div>
							<strong class="font-semibold">Lokasi tidak dapat dihapus!</strong>
							<p class="mt-1">Masih ada kamar yang sedang ditempati oleh penyewa aktif di lokasi ini. Kosongkan kamar atau pindahkan penyewa terlebih dahulu sebelum menghapus lokasi.</p>
						</div>
					</div>
				</div>

				<div class="flex justify-end gap-3 pt-2">
					<button type="button" @click="isDeleteLocationOpen = false" class="rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">Tutup</button>
				</div>
			@else
				<!-- Location has NO active tenants: prompt password to delete -->
				<p class="text-sm text-gray-600 dark:text-gray-300">Lokasi <strong>{{ $location->name }}</strong> akan dihapus permanen. Masukkan password admin untuk melanjutkan.</p>

				<form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="mt-4 space-y-4">
					@csrf
					@method('DELETE')
					<div>
						<label for="modal_location_password_page" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
						<input id="modal_location_password_page" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90" />
					</div>
					<div class="flex justify-end gap-3">
						<button type="button" @click="isDeleteLocationOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</button>
						<button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 shadow-theme-xs transition">Ya, hapus lokasi</button>
					</div>
				</form>
			@endif
		</div>
	</div>
</div>
@endsection
