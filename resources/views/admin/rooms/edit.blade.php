@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Edit Kamar" />

	<x-common.component-card title="Edit informasi kamar" desc="Perubahan harga hanya berlaku untuk konfigurasi kamar saat ini.">
		<form method="POST" action="{{ route('admin.locations.rooms.update', [$location, $room]) }}" class="space-y-6">
			@csrf
			@method('PUT')
			<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
				<div>
					<label for="floor_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih lantai</label>
					<select id="floor_name" name="floor_name" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
						@for ($floor = 1; $floor <= $location->floor_count; $floor++)
							<option value="{{ $floor }}" @selected((string) old('floor_name', $room->floor_name) === (string) $floor)>Lantai {{ $floor }}</option>
						@endfor
					</select>
					@error('floor_name')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
				<div>
					<label for="room_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor kamar</label>
					<input id="room_number" name="room_number" type="number" min="1" max="999999" step="1" inputmode="numeric" value="{{ old('room_number', $room->room_number) }}" placeholder="Contoh: 101" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					@error('room_number')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
			</div>
			<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
				<div>
					<label for="rent_amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga sewa per bulan</label>
					<input id="rent_amount" name="rent_amount" type="number" min="0" step="0.01" value="{{ old('rent_amount', $room->rent_amount) }}" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
					@error('rent_amount')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
				<div>
					<label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status kamar</label>
					<select id="status" name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
						<option value="active" @selected(old('status', $room->status) === 'active')>Aktif</option>
						<option value="inactive" @selected(old('status', $room->status) === 'inactive')>Nonaktif</option>
					</select>
				</div>
			</div>
			<div>
				<label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
				<input id="password" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
				@error('password')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
			</div>
			<div class="flex flex-wrap justify-end gap-3">
				<a href="{{ route('admin.locations.show', $location) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</a>
				<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan perubahan</button>
			</div>
		</form>
	</x-common.component-card>
@endsection