@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Pelanggan & Staff" />
	<x-smart-kos.admin-dummy-page
		title="Pelanggan & Staff"
		description="Manajemen akun admin, staff, dan penyewa akan dihubungkan pada fase account management."
		:metrics="[
			['label' => 'Total pengguna', 'value' => '42', 'note' => '+6 bulan ini'],
			['label' => 'Penyewa', 'value' => '30', 'note' => 'Aktif'],
			['label' => 'Staff', 'value' => '8', 'note' => 'Aktif'],
			['label' => 'Nonaktif', 'value' => '4', 'note' => 'Perlu ditinjau'],
		]"
		:columns="[
			['label' => 'Nama', 'key' => 'name'],
			['label' => 'Peran', 'key' => 'role'],
			['label' => 'Kontak', 'key' => 'contact'],
			['label' => 'Status', 'key' => 'status'],
		]"
		:rows="[
			['name' => 'Contoh Staff', 'role' => 'Staff', 'contact' => 'staff@example.test', 'status' => 'Aktif'],
			['name' => 'Contoh Penyewa', 'role' => 'Penyewa', 'contact' => 'tenant@example.test', 'status' => 'Aktif'],
		]"
	/>
@endsection