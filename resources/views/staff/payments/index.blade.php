@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Status Pembayaran Penyewa" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Pemantauan Status Pembayaran Penyewa"
			description="Daftar penyewa dan status jatuh tempo sewa kamar. Nominal sewa dirahasiakan sesuai hak akses staff."
			:metrics="[
				['label' => 'Total Penyewa', 'value' => '24', 'note' => 'Aktif'],
				['label' => 'Jatuh Tempo Hari Ini', 'value' => '2', 'note' => 'Segera konfirmasi'],
				['label' => 'Mendekati Jatuh Tempo', 'value' => '4', 'note' => '< 7 hari'],
				['label' => 'Aman', 'value' => '18', 'note' => '> 7 hari'],
			]"
			:columns="[
				['label' => 'Nama Penyewa', 'key' => 'tenant'],
				['label' => 'Lokasi', 'key' => 'location'],
				['label' => 'Kamar', 'key' => 'room'],
				['label' => 'Jatuh Tempo', 'key' => 'due_date'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['tenant' => 'Budi Santoso', 'location' => 'Kos Melati', 'room' => '101', 'due_date' => '25 Okt 2026', 'status' => 'Mendekati'],
				['tenant' => 'Siti Rahma', 'location' => 'Kos Anggrek', 'room' => '203', 'due_date' => '30 Okt 2026', 'status' => 'Aman'],
				['tenant' => 'Ahmad Fajar', 'location' => 'Kos Melati', 'room' => '104', 'due_date' => 'Hari Ini', 'status' => 'Jatuh Tempo'],
			]"
		/>
	</div>
@endsection
