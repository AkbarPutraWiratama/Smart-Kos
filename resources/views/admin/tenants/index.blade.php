@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Status Penyewa" />
	<x-smart-kos.admin-dummy-page
		title="Status Penyewa"
		description="Ringkasan status penghuni dan okupansi kamar. Data operasional akan dihubungkan pada fase tenant assignment."
		:metrics="[
			['label' => 'Penyewa aktif', 'value' => '24', 'note' => '+4 bulan ini'],
			['label' => 'Kamar terisi', 'value' => '24 / 36', 'note' => '67% okupansi'],
			['label' => 'Jatuh tempo', 'value' => '6', 'note' => 'Perlu dipantau'],
			['label' => 'Belum ditugaskan', 'value' => '3', 'note' => 'Menunggu kamar'],
		]"
		:columns="[
			['label' => 'Penyewa', 'key' => 'tenant'],
			['label' => 'Lokasi', 'key' => 'location'],
			['label' => 'Kamar', 'key' => 'room'],
			['label' => 'Status', 'key' => 'status'],
		]"
		:rows="[
			['tenant' => 'Data contoh penyewa', 'location' => 'Kos Melati', 'room' => '101', 'status' => 'Aktif'],
			['tenant' => 'Data contoh penyewa', 'location' => 'Kos Anggrek', 'room' => '203', 'status' => 'Aktif'],
		]"
	/>
@endsection