@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Aduan Fasilitas" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Aduan & Keluhan Kamar / Fasilitas"
			description="Kirimkan aduan terkait kerusakan fasilitas kos Anda. Batas pengiriman aduan adalah 1 kali per 10 menit."
			:metrics="[
				['label' => 'Total Aduan Saya', 'value' => '3', 'note' => 'Sepanjang sewa'],
				['label' => 'Aduan Aktif', 'value' => '1', 'note' => 'Sedang diproses'],
				['label' => 'Telah Selesai', 'value' => '2', 'note' => 'Tuntas'],
				['label' => 'Rate Limit', 'value' => '1x / 10 Menit', 'note' => 'Keamanan sistem'],
			]"
			:columns="[
				['label' => 'Judul Aduan', 'key' => 'title'],
				['label' => 'Tanggal Masuk', 'key' => 'date'],
				['label' => 'Tindakan Petugas', 'key' => 'action'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['title' => 'Keran Air Kamar Mandi Bocor', 'date' => '23 Okt 2026', 'action' => 'Penggantian klep pipa', 'status' => 'Dalam Perbaikan'],
				['title' => 'Lampu Balkon Redup', 'date' => '10 Sep 2026', 'action' => 'Diganti bohlam baru', 'status' => 'Selesai'],
			]"
		/>
	</div>
@endsection
