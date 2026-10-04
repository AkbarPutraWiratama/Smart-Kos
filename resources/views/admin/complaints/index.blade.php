@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Tindakan Aduan" />
	<x-smart-kos.admin-dummy-page
		title="Tindakan Aduan"
		description="Daftar aduan dan tindakan perbaikan akan tersedia setelah modul complaint diimplementasikan."
		:metrics="[
			['label' => 'Aduan masuk', 'value' => '8', 'note' => 'Bulan ini'],
			['label' => 'Belum ditangani', 'value' => '3', 'note' => 'Prioritas'],
			['label' => 'Dalam perbaikan', 'value' => '2', 'note' => 'Berjalan'],
			['label' => 'Selesai', 'value' => '12', 'note' => 'Bulan ini'],
		]"
		:columns="[
			['label' => 'Aduan', 'key' => 'complaint'],
			['label' => 'Lokasi', 'key' => 'location'],
			['label' => 'Prioritas', 'key' => 'priority'],
			['label' => 'Status', 'key' => 'status'],
		]"
		:rows="[
			['complaint' => 'Contoh kebocoran kamar mandi', 'location' => 'Kos Melati', 'priority' => 'Tinggi', 'status' => 'Belum'],
			['complaint' => 'Contoh lampu lorong', 'location' => 'Kos Anggrek', 'priority' => 'Normal', 'status' => 'Dalam perbaikan'],
		]"
	/>
@endsection