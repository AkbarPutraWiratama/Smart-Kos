@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Akun Penyewa" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Profil & Pengaturan Akun Penyewa"
			description="Kelola informasi data diri kontak dan keamanan kata sandi akun penyewa Anda."
			:metrics="[
				['label' => 'Nama Penyewa', 'value' => auth()->user()->name ?? 'Penyewa', 'note' => 'Aktif'],
				['label' => 'Peran Akun', 'value' => 'Penyewa', 'note' => 'Penghuni Kos'],
				['label' => 'Status Akun', 'value' => 'Aktif', 'note' => 'Terverifikasi'],
				['label' => 'Kamar Terdaftar', 'value' => 'Kamar 102', 'note' => 'Kos Melati'],
			]"
			:columns="[
				['label' => 'Informasi Akun', 'key' => 'field'],
				['label' => 'Data', 'key' => 'value'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['field' => 'Username', 'value' => auth()->user()->username ?? 'penyewa', 'status' => 'Aktif'],
				['field' => 'Email', 'value' => auth()->user()->email ?? 'Belum diatur', 'status' => 'Aktif'],
			]"
		/>
	</div>
@endsection
@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Akun Penyewa" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Profil & Pengaturan Akun Penyewa"
			description="Kelola informasi data diri kontak dan keamanan kata sandi akun penyewa Anda."
			:metrics="[
				['label' => 'Nama Penyewa', 'value' => auth()->user()->name ?? 'Penyewa', 'note' => 'Aktif'],
				['label' => 'Peran Akun', 'value' => 'Penyewa', 'note' => 'Penghuni Kos'],
				['label' => 'Status Akun', 'value' => 'Aktif', 'note' => 'Terverifikasi'],
				['label' => 'Kamar Terdaftar', 'value' => 'Kamar 102', 'note' => 'Kos Melati'],
			]"
			:columns="[
				['label' => 'Informasi Akun', 'key' => 'field'],
				['label' => 'Data', 'key' => 'value'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['field' => 'Username', 'value' => auth()->user()->username ?? 'penyewa', 'status' => 'Aktif'],
				['field' => 'Email', 'value' => auth()->user()->email ?? 'Belum diatur', 'status' => 'Aktif'],
			]"
		/>
	</div>
@endsection
