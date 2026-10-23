@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Akun Staff" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Profil & Pengaturan Akun Staff"
			description="Informasi kredensial dan detail akun operasional staff Smart Kos."
			:metrics="[
				['label' => 'Nama Staff', 'value' => auth()->user()->name ?? 'Staff', 'note' => 'Aktif'],
				['label' => 'Peran Akun', 'value' => 'Staff', 'note' => 'Operasional'],
				['label' => 'Status Akun', 'value' => 'Aktif', 'note' => 'Terverifikasi'],
				['label' => 'Akses Fitur', 'value' => 'Terbatas', 'note' => 'Tanpa nominal sewa'],
			]"
			:columns="[
				['label' => 'Pengaturan Akun', 'key' => 'setting'],
				['label' => 'Informasi', 'key' => 'value'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['setting' => 'Username', 'value' => auth()->user()->username ?? 'staff', 'status' => 'Aktif'],
				['setting' => 'Email', 'value' => auth()->user()->email ?? 'Belum diatur', 'status' => 'Aktif'],
			]"
		/>
	</div>
@endsection
