@extends('layouts.app')

@section('content')
	<div x-data="{ isAddRoomOpen: false, isEditLocationOpen: false, isDeleteLocationOpen: false }">
	<x-common.page-breadcrumb pageTitle="{{ $location->name }}" />

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
							<div x-data="{ isRoomEditOpen: false, isDeleteRoomOpen: false }">
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
										<form method="POST" action="{{ route('admin.locations.rooms.update', [$location, $room]) }}" class="space-y-4">
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
												<select id="room_status_{{ $room->id }}" name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
													<option value="active" @selected($room->status === 'active')>Aktif</option>
													<option value="inactive" @selected($room->status === 'inactive')>Nonaktif</option>
												</select>
											</div>
											<div>
												<label for="room_password_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
												<input id="room_password_{{ $room->id }}" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
											</div>
											<div class="flex justify-between gap-3 pt-2">
												@if (! $room->activeAssignment)
													<button type="button" @click="isDeleteRoomOpen = true" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Hapus kamar</button>
												@endif
												<div class="ms-auto flex gap-3">
													<button type="button" @click="isRoomEditOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
													<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan</button>
												</div>
											</div>
										</form>
									</div>
								</div>

								<div x-show="isDeleteRoomOpen" x-cloak @keydown.escape.window="isDeleteRoomOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
									<div @click="isDeleteRoomOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
									<div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
										<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Konfirmasi hapus kamar</h2>
										<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Kamar {{ $room->room_number }} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
										<form method="POST" action="{{ route('admin.locations.rooms.destroy', [$location, $room]) }}" class="mt-5 space-y-4">
											@csrf
											@method('DELETE')
											<div>
												<label for="delete_room_password_{{ $room->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
												<input id="delete_room_password_{{ $room->id }}" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90" />
											</div>
											<div class="flex justify-end gap-3">
												<button type="button" @click="isDeleteRoomOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
												<button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Ya, hapus kamar</button>
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
				<x-smart-kos.location-photo-picker
					:photos="$location->photos"
					:legacyPath="$location->photo_path"
					:location="$location"
					inputId="modal_location_photos"
					slider
				/>
				<div>
					<label for="modal_location_status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
					<select id="modal_location_status" name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
						<option value="active" @selected(old('status', $location->status) === 'active')>Aktif</option>
						<option value="inactive" @selected(old('status', $location->status) === 'inactive')>Nonaktif</option>
					</select>
				</div>
				<div class="flex justify-end gap-3 pt-2">
					<button type="button" @click="isEditLocationOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
					<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan perubahan</button>
				</div>
			</form>
			<div class="mt-6 border-t border-error-100 pt-5 dark:border-error-500/20">
				<p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Hapus lokasi hanya dapat dilakukan jika tidak ada penyewa aktif. Password admin diperlukan.</p>
				<button type="button" @click="isDeleteLocationOpen = true" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Hapus lokasi</button>
			</div>
		</div>
	</div>

	<div x-show="isDeleteLocationOpen" x-cloak @keydown.escape.window="isDeleteLocationOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
		<div @click="isDeleteLocationOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
		<div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
			<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Konfirmasi hapus lokasi</h2>
			<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Lokasi {{ $location->name }} akan dihapus permanen. Pastikan tidak ada penyewa aktif.</p>
			<form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="mt-5 space-y-4">
					@csrf
					@method('DELETE')
					<div class="flex-1">
						<label for="modal_location_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
						<input id="modal_location_password" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90" />
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