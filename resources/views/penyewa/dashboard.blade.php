@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Dashboard Penyewa" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Dashboard Informasi Penyewa"
			description="Informasi sewa kamar aktif Anda, tenggat pembayaran, dan pengumuman terbaru dari pengelola kos."
			:metrics="[
				['label' => 'Kamar Anda', 'value' => '102', 'note' => 'Kos Melati (Lt. 1)'],
				['label' => 'Status Sewa', 'value' => 'Aktif', 'note' => 'Masa sewa berjalan'],
				['label' => 'Jatuh Tempo Pembayaran', 'value' => '01 Nov 2026', 'note' => '> 7 hari (Aman)'],
				['label' => 'Aduan Aktif', 'value' => '1', 'note' => 'Dalam proses'],
			]"
			:columns="[
				['label' => 'Pengumuman / Pemberitahuan', 'key' => 'title'],
				['label' => 'Kategori', 'key' => 'category'],
				['label' => 'Tanggal', 'key' => 'date'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['title' => 'Pembersihan Toren Air Bersama', 'category' => 'Fasilitas', 'date' => '24 Okt 2026', 'status' => 'Penting'],
				['title' => 'Penggantian Sandi WiFi Bulanan', 'category' => 'Internet', 'date' => '20 Okt 2026', 'status' => 'Informasi'],
			]"
		/>
	</div>
@endsection
@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Dashboard Penyewa" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Dashboard Informasi Penyewa"
			description="Informasi sewa kamar aktif Anda, tenggat pembayaran, dan pengumuman terbaru dari pengelola kos."
			:metrics="[
				['label' => 'Kamar Anda', 'value' => '102', 'note' => 'Kos Melati (Lt. 1)'],
				['label' => 'Status Sewa', 'value' => 'Aktif', 'note' => 'Masa sewa berjalan'],
				['label' => 'Jatuh Tempo Pembayaran', 'value' => '01 Nov 2026', 'note' => '> 7 hari (Aman)'],
				['label' => 'Aduan Aktif', 'value' => '1', 'note' => 'Dalam proses'],
			]"
			:columns="[
				['label' => 'Pengumuman / Pemberitahuan', 'key' => 'title'],
				['label' => 'Kategori', 'key' => 'category'],
				['label' => 'Tanggal', 'key' => 'date'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['title' => 'Pembersihan Toren Air Bersama', 'category' => 'Fasilitas', 'date' => '24 Okt 2026', 'status' => 'Penting'],
				['title' => 'Penggantian Sandi WiFi Bulanan', 'category' => 'Internet', 'date' => '20 Okt 2026', 'status' => 'Informasi'],
			]"
		/>
	</div>
@endsection
