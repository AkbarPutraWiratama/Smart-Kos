@extends('layouts.app')

@section('content')
<div x-data="{ isConfirmSaveOpen: false }">
	<x-common.page-breadcrumb pageTitle="Edit Kamar" />

	<x-common.component-card title="Edit informasi kamar {{ $room->room_number }}" desc="Perubahan harga hanya berlaku untuk konfigurasi kamar saat ini.">
		<form id="edit_room_form" method="POST" action="{{ route('admin.locations.rooms.update', [$location, $room]) }}" class="space-y-6">
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
					@if ($room->isOccupied())
						<select id="status" name="status" class="h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 text-sm text-gray-500 outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
							<option value="active" selected>Aktif (Sedang Ditempati Penyewa)</option>
						</select>
						<p class="mt-1 text-xs text-warning-600 dark:text-warning-400">Status tidak dapat diubah ke nonaktif karena kamar sedang terisi oleh penyewa aktif.</p>
					@else
						<select id="status" name="status" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
							<option value="active" @selected(old('status', $room->status) === 'active')>Aktif</option>
							<option value="inactive" @selected(old('status', $room->status) === 'inactive')>Nonaktif</option>
						</select>
					@endif
					@error('status')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
				</div>
			</div>

			<!-- Hidden password field populated from the pop-up modal -->
			<input type="hidden" id="form_password" name="password" value="" />
			@error('password')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror

			<div class="flex flex-wrap justify-end gap-3 pt-2">
				<a href="{{ route('admin.locations.show', $location) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</a>
				<button type="button" @click="isConfirmSaveOpen = true" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">Simpan perubahan</button>
			</div>
		</form>
	</x-common.component-card>

	<!-- Modal Konfirmasi Simpan Perubahan Kamar -->
	<div x-show="isConfirmSaveOpen" x-cloak @keydown.escape.window="isConfirmSaveOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
		<div @click="isConfirmSaveOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
		<div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
			<div class="mb-4 flex items-center gap-3">
				<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-500">
					<svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
						<path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
					</svg>
				</div>
				<div>
					<h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Konfirmasi Simpan Perubahan</h3>
					<p class="text-xs text-gray-500 dark:text-gray-400">Masukkan kata sandi admin untuk menyimpan</p>
				</div>
			</div>
			<p class="text-sm text-gray-600 dark:text-gray-300">Konfirmasikan perubahan data kamar <strong>{{ $room->room_number }}</strong> dengan memasukkan kata sandi admin Anda.</p>
			<form @submit.prevent="document.getElementById('form_password').value = document.getElementById('modal_save_password').value; document.getElementById('edit_room_form').submit();" class="mt-5 space-y-4">
				<div>
					<label for="modal_save_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
					<input id="modal_save_password" type="password" required autofocus class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90" />
				</div>
				<div class="flex justify-end gap-3 pt-2">
					<button type="button" @click="isConfirmSaveOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">Batal</button>
					<button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition">Konfirmasi & Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
