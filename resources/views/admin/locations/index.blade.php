@extends('layouts.app')

@section('content')
	<div x-data="{ isCreateLocationOpen: false }">
	<x-common.page-breadcrumb pageTitle="Lokasi & Kamar" />

	<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
		<div>
			<h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar lokasi kos</h1>
			<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola lokasi dan kamar yang tersedia.</p>
		</div>
		<button type="button" @click="isCreateLocationOpen = true"
			class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
			Tambah lokasi
		</button>
	</div>

	<div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
		@forelse ($locations as $location)
			<a href="{{ route('admin.locations.show', $location) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white transition hover:-translate-y-0.5 hover:border-brand-500 hover:shadow-theme-md dark:border-gray-800 dark:bg-white/[0.03]">
				<div class="h-36 overflow-hidden bg-gray-100 dark:bg-gray-800">
					@if ($location->photos->first())
						<img src="{{ asset('storage/' . $location->photos->first()->path) }}" alt="{{ $location->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
					@else
						<div class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500">Belum ada foto lokasi</div>
					@endif
				</div>
				<div class="p-5">
					<div class="flex items-start justify-between gap-3">
						<div>
							<h2 class="font-semibold text-gray-800 group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400">{{ $location->name }}</h2>
							<p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">{{ $location->address }}</p>
						</div>
						<span class="shrink-0 rounded-full px-2.5 py-0.5 text-theme-xs font-medium {{ $location->status === 'active' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
							{{ ucfirst($location->status) }}
						</span>
					</div>
					<div class="mt-5 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
						<div>
							<p class="text-xs text-gray-500 dark:text-gray-400">Lantai</p>
							<p class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ $location->floor_count }}</p>
						</div>
						<div>
							<p class="text-xs text-gray-500 dark:text-gray-400">Kamar aktif</p>
							<p class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ $location->active_rooms_count }} / {{ $location->rooms_count }}</p>
						</div>
					</div>
					<p class="mt-4 text-sm font-medium text-brand-500">Klik untuk kelola lokasi</p>
				</div>
			</a>
		@empty
			<div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400">Belum ada lokasi.</div>
		@endforelse
	</div>

	<div x-show="isCreateLocationOpen" x-cloak @keydown.escape.window="isCreateLocationOpen = false" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5">
		<div @click="isCreateLocationOpen = false" class="fixed inset-0 bg-gray-900/50"></div>
		<div @click.stop class="relative z-10 max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-5 shadow-theme-xl dark:bg-gray-900 sm:p-6">
			<div class="mb-6 flex items-start justify-between gap-4">
				<div>
					<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Tambah lokasi</h2>
					<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat lokasi kos baru sebelum menambahkan kamar.</p>
				</div>
				<button type="button" @click="isCreateLocationOpen = false" aria-label="Tutup" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-white">&times;</button>
			</div>
			<form method="POST" action="{{ route('admin.locations.store') }}" enctype="multipart/form-data" class="space-y-4">
				@csrf
				<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
					<div>
						<label for="create_location_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama lokasi</label>
						<input id="create_location_name" name="name" value="{{ old('name') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					</div>
					<div>
						<label for="create_location_floor_count" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah lantai</label>
						<input id="create_location_floor_count" name="floor_count" type="number" min="1" max="50" value="{{ old('floor_count', 1) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					</div>
				</div>
				<div>
					<label for="create_location_address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
					<textarea id="create_location_address" name="address" rows="3" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('address') }}</textarea>
				</div>
				<div>
					<label for="create_location_maps" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Link Google Maps</label>
					<input id="create_location_maps" name="google_maps_url" type="url" value="{{ old('google_maps_url') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
				</div>
				<div>
					<label for="create_location_description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi fasilitas</label>
					<textarea id="create_location_description" name="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('description') }}</textarea>
				</div>
				<x-smart-kos.location-photo-picker :photos="collect()" inputId="create_location_modal_photos" />
				<input type="hidden" name="status" value="active" />
				<div class="flex justify-end gap-3 pt-2">
					<button type="button" @click="isCreateLocationOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
					<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan lokasi</button>
				</div>
			</form>
		</div>
	</div>
	</div>
@endsection