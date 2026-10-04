@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Keuangan" />
	<x-smart-kos.admin-dummy-page
		title="Ringkasan Keuangan"
		description="Ringkasan ini adalah data contoh untuk menyiapkan tampilan laporan keuangan Smart Kos."
		:metrics="[
			['label' => 'Pendapatan', 'value' => 'Rp 30 jt', 'note' => 'Bulan ini'],
			['label' => 'Pengeluaran', 'value' => 'Rp 10 jt', 'note' => 'Bulan ini'],
			['label' => 'Laba bersih', 'value' => 'Rp 20 jt', 'note' => 'Contoh'],
			['label' => 'Tagihan tertunda', 'value' => 'Rp 4 jt', 'note' => 'Perlu ditinjau'],
		]"
		:columns="[
			['label' => 'Jenis transaksi', 'key' => 'type'],
			['label' => 'Lokasi', 'key' => 'location'],
			['label' => 'Jumlah', 'key' => 'amount'],
			['label' => 'Status', 'key' => 'status'],
		]"
		:rows="[
			['type' => 'Pembayaran sewa', 'location' => 'Kos Melati', 'amount' => 'Rp 12.000.000', 'status' => 'Tercatat'],
			['type' => 'Operasional', 'location' => 'Kos Anggrek', 'amount' => 'Rp 3.500.000', 'status' => 'Tercatat'],
		]"
	/>
@endsection