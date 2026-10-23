@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Tindakan Aduan" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Daftar Tindakan Aduan & Perbaikan"
			description="Kelola dan tindak lanjuti keluhan fasilitas atau masalah kamar dari para penyewa."
			:metrics="[
				['label' => 'Total Aduan', 'value' => '8', 'note' => 'Bulan ini'],
				['label' => 'Aduan Prioritas (Highlight)', 'value' => '2', 'note' => 'Perhatian Admin'],
				['label' => 'Dalam Perbaikan', 'value' => '3', 'note' => 'Sedang dikerjakan'],
				['label' => 'Selesai', 'value' => '3', 'note' => 'Tuntas'],
			]"
			:columns="[
				['label' => 'Judul Aduan', 'key' => 'title'],
				['label' => 'Lokasi / Kamar', 'key' => 'room'],
				['label' => 'Tanggal Masuk', 'key' => 'submitted_at'],
				['label' => 'Highlight', 'key' => 'highlight'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['title' => 'Keran Air Kamar Mandi Bocor', 'room' => 'Kos Melati - Kamar 102', 'submitted_at' => '23 Okt 2026', 'highlight' => 'Ya (Admin)', 'status' => 'Dalam Perbaikan'],
				['title' => 'Lampu Kamar Utama Mati', 'room' => 'Kos Melati - Kamar 105', 'submitted_at' => '22 Okt 2026', 'highlight' => 'Tidak', 'status' => 'Belum'],
				['title' => 'AC Kurang Dingin', 'room' => 'Kos Anggrek - Kamar 201', 'submitted_at' => '20 Okt 2026', 'highlight' => 'Tidak', 'status' => 'Selesai'],
			]"
		/>
	</div>
@endsection
