@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Akun Admin" />
	<x-smart-kos.admin-dummy-page
		title="Akun Admin"
		description="Pengaturan profil dan keamanan akun admin akan tersedia pada fase account management."
		:metrics="[
			['label' => 'Nama pengguna', 'value' => auth()->user()->name ?? 'Admin', 'note' => 'Akun aktif'],
			['label' => 'Peran', 'value' => 'Admin', 'note' => 'Akses penuh'],
			['label' => 'Status', 'value' => 'Aktif', 'note' => 'Terverifikasi'],
			['label' => 'Keamanan', 'value' => 'Baik', 'note' => 'Password aktif'],
		]"
		:columns="[
			['label' => 'Pengaturan', 'key' => 'setting'],
			['label' => 'Nilai', 'key' => 'value'],
			['label' => 'Status', 'key' => 'status'],
		]"
		:rows="[
			['setting' => 'Username', 'value' => auth()->user()->username ?? 'admin', 'status' => 'Aktif'],
			['setting' => 'Email', 'value' => auth()->user()->email ?? 'Belum diatur', 'status' => 'Aktif'],
		]"
	/>
@endsection