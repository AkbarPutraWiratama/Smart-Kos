@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Dashboard Staff" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Dashboard Operasional Staff"
			description="Pantau status operasional kos harian, pengumuman, dan tugas pemeliharaan terkini."
			:metrics="[
				['label' => 'Total Lokasi Dikelola', 'value' => '2', 'note' => 'Aktif'],
				['label' => 'Kamar Perlu Dipantau', 'value' => '5', 'note' => 'Jatuh tempo'],
				['label' => 'Aduan Belum Selesai', 'value' => '3', 'note' => 'Perlu tindakan'],
				['label' => 'Pengumuman Aktif', 'value' => '4', 'note' => 'Terbit'],
			]"
			:columns="[
				['label' => 'Judul Pengumuman', 'key' => 'title'],
				['label' => 'Target', 'key' => 'target'],
				['label' => 'Tanggal', 'key' => 'date'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['title' => 'Jadwal Pembersihan Toren Air', 'target' => 'Semua Lokasi', 'date' => '24 Okt 2026', 'status' => 'Dipublikasikan'],
				['title' => 'Perbaikan Pompa Air Selesai', 'target' => 'Kos Melati', 'date' => '22 Okt 2026', 'status' => 'Dipublikasikan'],
			]"
		/>
	</div>
@endsection
