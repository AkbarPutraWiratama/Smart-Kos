@extends('layouts.app')

@section('content')
	<div x-data="{ isAddRoomOpen: false, isEditLocationOpen: false, isDeleteLocationOpen: false }">
	<x-common.page-breadcrumb
		pageTitle="{{ $location->name }}"
		:breadcrumbs="[
			['label' => 'Lokasi & Kamar', 'url' => route('admin.locations.index')],
		]"
	/>

	<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
		<div>
			<h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $location->name }}</h1>
			<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $location->address }}</p>
		</div>
		<div class="flex flex-wrap gap-3">
			<button type="button" @click="isEditLocationOpen = true" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Edit lokasi</button>
			<button type="button" @click="isAddRoomOpen = true" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Tambah kamar</button>
		</div>
	</div>

	@if (session('success'))
		<div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">{{ session('success') }}</div>
	@endif
	@if ($errors->any())
		<div class="mb-6 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">{{ $errors->first() }}</div>
	@endif

	@php
		$adminPhotos = collect();
		if ($location->photo_path) {
			$adminPhotos->push([
				'url' => asset('storage/' . $location->photo_path),
				'badge' => 'Foto Lokasi',
				'type' => 'location',
			]);
		}
		// Location photos first, followed by room photos
		$sortedPhotos = $location->photos->sortBy(fn($p) => ($p->type ?? 'location') === 'room' ? 1 : 0);
		foreach ($sortedPhotos as $photo) {
			$photoType = $photo->type ?? 'location';
			$adminPhotos->push([
				'url' => asset('storage/' . $photo->path),
				'badge' => $photoType === 'room' ? 'Foto Kamar' : 'Foto Lokasi',
				'type' => $photoType,
			]);
		}
	@endphp

	<!-- Gallery Card -->
	<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
		<div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] lg:items-center">
			<div x-data="{ currentPhoto: 0, photos: @js($adminPhotos->values()) }" class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-900">
				@if ($adminPhotos->isNotEmpty())
					<img :src="photos[currentPhoto].url" alt="Foto {{ $location->name }}" class="h-56 w-full object-cover sm:h-64" />
					<!-- Photo Tag Badge -->
					<div class="absolute top-3 start-3 z-10 pointer-events-none">
						<span class="rounded-lg px-2.5 py-1 text-xs font-semibold shadow-md backdrop-blur-md"
							:class="photos[currentPhoto].type === 'room' ? 'bg-purple-600/90 text-white' : 'bg-brand-600/90 text-white'"
							x-text="photos[currentPhoto].badge">
						</span>
					</div>
					@if ($adminPhotos->count() > 1)
						<button type="button" @click="currentPhoto = (currentPhoto - 1 + photos.length) % photos.length" aria-label="Foto sebelumnya" class="absolute start-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-xl text-white hover:bg-black/80">&#8249;</button>
						<button type="button" @click="currentPhoto = (currentPhoto + 1) % photos.length" aria-label="Foto berikutnya" class="absolute end-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-xl text-white hover:bg-black/80">&#8250;</button>
						<span class="absolute bottom-3 start-1/2 -translate-x-1/2 rounded-full bg-black/60 px-2.5 py-1 text-xs text-white" x-text="`${currentPhoto + 1} / ${photos.length}`"></span>
					@endif
				@else
					<div class="flex h-56 items-center justify-center px-6 text-center text-sm text-gray-400 dark:h-64 dark:text-gray-500">Belum ada foto lokasi. Klik 'Edit lokasi' untuk menambah foto.</div>
				@endif
			</div>
			<div>
				<div class="flex items-center gap-3 mb-2">
					<h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $location->name }}</h2>
					<span class="rounded-full px-2.5 py-0.5 text-theme-xs font-medium {{ $location->status === 'active' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
						{{ ucfirst($location->status) }}
					</span>
				</div>
				<p class="text-sm text-gray-500 dark:text-gray-400">{{ $location->address }}</p>
				@if ($location->description)
					<p class="mt-3 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $location->description }}</p>
				@endif
				<div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
					<span><strong>{{ $location->floor_count }}</strong> Lantai</span>
					<span>&bull;</span>
					<span><strong>{{ $location->rooms->count() }}</strong> Total Kamar</span>
					@if ($location->google_maps_url)
						<span>&bull;</span>
						<a href="{{ $location->google_maps_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-brand-500 hover:text-brand-600 dark:text-brand-400 font-medium">
							<svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
							Buka di Google Maps
						</a>
					@endif
				</div>
			</div>
		</div>
	</div>

	<div class="grid grid-cols-1 gap-6">
		<div class="space-y-6">
			<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
				<label for="floor_filter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter lantai</label>
				<form method="GET" action="{{ route('admin.locations.show', $location) }}">
					<select id="floor_filter" name="floor_order" onchange="this.form.submit()" class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-56">
						<option value="asc" @selected($floorOrder === 'asc')>Ascending: bawah ke atas</option>
						<option value="desc" @selected($floorOrder === 'desc')>Descending: atas ke bawah</option>
					</select>
				</form>
			</div>
			@forelse ($roomsByFloor as $floor => $rooms)
				<x-common.component-card
					title="Lantai {{ $floor }}"
					desc="{{ $rooms->count() }} kamar terdaftar."
				>
					<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
						@foreach ($rooms as $room)
							<div x-data="{ isRoomEditOpen: false, isDeleteRoomOpen: false, isConfirmSaveRoomOpen: false }">
								<div role="button" tabindex="0" @click="isRoomEditOpen = true" @keydown.enter="isRoomEditOpen = true" @keydown.space.prevent="isRoomEditOpen = true" class="cursor-pointer rounded-xl border border-gray-200 p-4 transition hover:border-brand-500 hover:shadow-theme-sm focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-800">
									<div class="flex items-start justify-between gap-3">
										<div>
											<h3 class="font-semibold text-gray-800 dark:text-white/90">Kamar {{ $room->room_number }}</h3>
											<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rp {{ number_format($room->rent_amount, 0, ',', '.') }} / bulan</p>
										</div>
										<span class="rounded-full px-2.5 py-0.5 text-theme-xs font-medium {{ $room->activeAssignment ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500' : 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' }}">
											{{ $room->activeAssignment ? 'Terisi' : 'Kosong' }}
										</span>
									</div>
									<p class="mt-4 text-sm font-medium text-brand-500">Klik untuk edit kamar</p>
								</div>

								<!-- Modal Edit Kamar -->
								<div x-show="isRoomEditOpen" x-cloak @keydown.escape.window="isRoomEditOpen = false" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5">
									<div @click="isRoomEditOpen = false" class="fixed inset-0 bg-gray-900/50"></div>
									<div @click.stop class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
										<div class="mb-6 flex items-start justify-between gap-4">
											<div>
												<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Edit kamar {{ $room->room_number }}</h2>
												<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui data kamar tanpa berpindah halaman.</p>
											</div>
											<button type="button" @click="isRoomEditOpen = false" aria-label="Tutup" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-white">&times;</button>
										</div>
										<form id="edit_room_form_{{ $room->id }}" method="POST" action="{{ route('admin.locations.rooms.update', [$location, $room]) }}" class="space-y-4">
											@csrf
											@method('PUT')
											<div>
												<label for="room_floor_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih lantai</label>
												<select id="room_floor_{{ $room->id }}" name="floor_name" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
													@for ($roomFloor = 1; $roomFloor <= $location->floor_count; $roomFloor++)
														<option value="{{ $roomFloor }}" @selected((int) $room->floor_name === $roomFloor)>Lantai {{ $roomFloor }}</option>
													@endfor
												</select>
											</div>
											<div>
												<label for="room_number_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor kamar</label>
												<input id="room_number_{{ $room->id }}" name="room_number" type="number" min="1" max="999999" step="1" inputmode="numeric" value="{{ $room->room_number }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
											</div>
											<div>
												<label for="room_rent_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga sewa per bulan</label>
												<input id="room_rent_{{ $room->id }}" name="rent_amount" type="number" min="0" step="0.01" value="{{ $room->rent_amount }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
											</div>
											<div>
												<label for="room_status_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status kamar</label>
												@if ($room->activeAssignment)
													<select id="room_status_{{ $room->id }}" name="status" class="h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 text-sm text-gray-500 outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
														<option value="active" selected>Aktif (Sedang Ditempati Penyewa)</option>
													</select>
													<p class="mt-1 text-xs text-warning-600 dark:text-warning-400">Status tidak dapat diubah ke nonaktif karena kamar sedang terisi oleh penyewa aktif.</p>
												@else
													<select id="room_status_{{ $room->id }}" name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
														<option value="active" @selected($room->status === 'active')>Aktif</option>
														<option value="inactive" @selected($room->status === 'inactive')>Nonaktif</option>
													</select>
												@endif
											</div>

											<input type="hidden" id="room_password_hidden_{{ $room->id }}" name="password" value="" />

											<div class="flex justify-between gap-3 pt-2">
												@if (! $room->activeAssignment)
													<button type="button" @click="isDeleteRoomOpen = true" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 transition">Hapus kamar</button>
												@endif
												<div class="ms-auto flex gap-3">
													<button type="button" @click="isRoomEditOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</button>
													<button type="button" @click="isConfirmSaveRoomOpen = true" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">Simpan</button>
												</div>
											</div>
										</form>
									</div>
								</div>

								<!-- Modal Konfirmasi Simpan Perubahan Kamar -->
								<div x-show="isConfirmSaveRoomOpen" x-cloak @keydown.escape.window="isConfirmSaveRoomOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
									<div @click="isConfirmSaveRoomOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
									<div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
										<div class="mb-4 flex items-center gap-3">
											<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-500">
												<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
													<path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
												</svg>
											</div>
											<div>
												<h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Konfirmasi Simpan Perubahan</h3>
												<p class="text-xs text-gray-500 dark:text-gray-400">Masukkan password admin untuk menyimpan</p>
											</div>
										</div>
										<p class="text-sm text-gray-600 dark:text-gray-300">Konfirmasikan perubahan kamar <strong>{{ $room->room_number }}</strong> dengan memasukkan kata sandi admin Anda.</p>
										<form @submit.prevent="document.getElementById('room_password_hidden_{{ $room->id }}').value = document.getElementById('modal_save_password_{{ $room->id }}').value; document.getElementById('edit_room_form_{{ $room->id }}').submit();" class="mt-5 space-y-4">
											<div>
												<label for="modal_save_password_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
												<input id="modal_save_password_{{ $room->id }}" type="password" required autofocus class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
											</div>
											<div class="flex justify-end gap-3 pt-2">
												<button type="button" @click="isConfirmSaveRoomOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</button>
												<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">Konfirmasi & Simpan</button>
											</div>
										</form>
									</div>
								</div>

								<!-- Modal Konfirmasi Hapus Kamar -->
								<div x-show="isDeleteRoomOpen" x-cloak @keydown.escape.window="isDeleteRoomOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
									<div @click="isDeleteRoomOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
									<div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
										<div class="mb-4 flex items-center gap-3">
											<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500">
												<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
													<path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
												</svg>
											</div>
											<div>
												<h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Konfirmasi Hapus Kamar</h2>
												<p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan</p>
											</div>
										</div>
										<p class="text-sm text-gray-600 dark:text-gray-300">Kamar <strong>{{ $room->room_number }}</strong> akan dihapus permanen. Masukkan password admin untuk melanjutkan.</p>
										<form method="POST" action="{{ route('admin.locations.rooms.destroy', [$location, $room]) }}" class="mt-5 space-y-4">
											@csrf
											@method('DELETE')
											<div>
												<label for="delete_room_password_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
												<input id="delete_room_password_{{ $room->id }}" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90" />
											</div>
											<div class="flex justify-end gap-3 pt-2">
												<button type="button" @click="isDeleteRoomOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</button>
												<button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 shadow-theme-xs transition">Ya, hapus kamar</button>
											</div>
										</form>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</x-common.component-card>
			@empty
				<x-common.component-card title="Belum ada kamar" desc="Tambahkan kamar pertama untuk lokasi ini.">
					<p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data kamar.</p>
				</x-common.component-card>
			@endforelse
		</div>

	</div>

	<div x-show="isAddRoomOpen" x-cloak @keydown.escape.window="isAddRoomOpen = false" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5">
		<div @click="isAddRoomOpen = false" class="fixed inset-0 bg-gray-900/50"></div>
		<div @click.stop class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
			<div class="mb-6 flex items-start justify-between gap-4">
				<div>
					<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Tambah kamar</h2>
					<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kamar baru dibuat tanpa penyewa.</p>
				</div>
				<button type="button" @click="isAddRoomOpen = false" aria-label="Tutup" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-white">&times;</button>
			</div>
			<form method="POST" action="{{ route('admin.locations.rooms.store', $location) }}" class="space-y-4">
				@csrf
				<div>
					<label for="modal_floor_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih lantai</label>
					<select id="modal_floor_name" name="floor_name" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
						<option value="">Pilih lantai</option>
						@for ($floor = 1; $floor <= $location->floor_count; $floor++)
							<option value="{{ $floor }}" @selected((string) old('floor_name') === (string) $floor)>Lantai {{ $floor }}</option>
						@endfor
					</select>
					@error('floor_name')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
				<div>
					<label for="modal_room_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor kamar</label>
					<input id="modal_room_number" name="room_number" type="number" min="1" max="999999" step="1" inputmode="numeric" value="{{ old('room_number') }}" placeholder="Contoh: 101" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					@error('room_number')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
				<div>
					<label for="modal_rent_amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga sewa per bulan</label>
					<input id="modal_rent_amount" name="rent_amount" type="number" min="0" step="0.01" value="{{ old('rent_amount') }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
				</div>
				<div>
					<label for="modal_room_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
					<input id="modal_room_password" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
				</div>
				<input type="hidden" name="status" value="active" />
				<div class="flex justify-end gap-3 pt-2">
					<button type="button" @click="isAddRoomOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
					<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Tambah kamar</button>
				</div>
			</form>
		</div>
	</div>

	<div x-show="isEditLocationOpen" x-cloak @keydown.escape.window="isEditLocationOpen = false" class="fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5">
		<div @click="isEditLocationOpen = false" class="fixed inset-0 bg-gray-900/50"></div>
		<div @click.stop class="relative z-10 max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-5 shadow-theme-xl dark:bg-gray-900 sm:p-6">
			<div class="mb-6 flex items-start justify-between gap-4">
				<div>
					<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Edit lokasi</h2>
					<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui informasi lokasi tanpa berpindah halaman.</p>
				</div>
				<button type="button" @click="isEditLocationOpen = false" aria-label="Tutup" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-white">&times;</button>
			</div>
			<form method="POST" action="{{ route('admin.locations.update', $location) }}" enctype="multipart/form-data" class="space-y-4">
				@csrf
				@method('PUT')
				<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
					<div>
						<label for="modal_location_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama lokasi</label>
						<input id="modal_location_name" name="name" value="{{ old('name', $location->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					</div>
					<div>
						<label for="modal_floor_count" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah lantai</label>
						<input id="modal_floor_count" name="floor_count" type="number" min="1" max="50" value="{{ old('floor_count', $location->floor_count) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					</div>
				</div>
				<div>
					<label for="modal_address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
					<textarea id="modal_address" name="address" rows="3" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('address', $location->address) }}</textarea>
				</div>
				<div>
					<label for="modal_google_maps_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Link Google Maps</label>
					<input id="modal_google_maps_url" name="google_maps_url" type="url" value="{{ old('google_maps_url', $location->google_maps_url) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
				</div>
				<div>
					<label for="modal_description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi fasilitas</label>
					<textarea id="modal_description" name="description" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('description', $location->description) }}</textarea>
				</div>
				<!-- Section Foto Lokasi -->
				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
					<x-smart-kos.location-photo-picker
						:photos="$location->photos->filter(fn($p) => ($p->type ?? 'location') !== 'room')"
						:legacyPath="$location->photo_path"
						:location="$location"
						inputId="modal_location_photos"
						inputName="photos"
						label="Foto lokasi"
						badge="Foto Lokasi"
						helperText="Pilih beberapa foto lokasi sekaligus, simpan, lalu ulangi untuk menambahkan foto lainnya tanpa menghapus foto sebelumnya. Maksimal 10 foto per sesi, masing-masing 4 MB."
					/>
				</div>

				<!-- Section Foto Kamar -->
				<div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
					<x-smart-kos.location-photo-picker
						:photos="$location->photos->filter(fn($p) => ($p->type ?? 'location') === 'room')"
						:location="$location"
						inputId="modal_room_photos"
						inputName="room_photos"
						label="Foto kamar"
						badge="Foto Kamar"
						helperText="Tambahkan foto contoh kamar atau tipe kamar di lokasi ini. Foto akan tersimpan bersama galeri lokasi dengan tag 'Foto Kamar'."
					/>
				</div>
				<div>
					<label for="modal_location_status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
					<select id="modal_location_status" name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
						<option value="active" @selected(old('status', $location->status) === 'active')>Aktif</option>
						<option value="inactive" @selected(old('status', $location->status) === 'inactive')>Nonaktif</option>
					</select>
				</div>

				<!-- Action Buttons: Hapus Lokasi selevel dengan Simpan Perubahan & Batal -->
				<div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
					<div>
						<button type="button" @click="isDeleteLocationOpen = true" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-error-600 transition">
							Hapus lokasi
						</button>
					</div>
					<div class="flex items-center gap-3">
						<button type="button" @click="isEditLocationOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</button>
						<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 shadow-theme-xs transition">Simpan perubahan</button>
					</div>
				</div>
			</form>
		</div>
	</div>

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
						<label for="modal_location_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
						<input id="modal_location_password" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90" />
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
