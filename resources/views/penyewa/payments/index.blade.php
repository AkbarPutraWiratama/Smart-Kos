@extends('layouts.app')

@section('content')
	<x-common.page-breadcrumb pageTitle="Pembayaran Sewa" />

	<div class="space-y-6">
		<x-smart-kos.admin-dummy-page
			title="Tagihan & Riwayat Pembayaran Sewa"
			description="Pilih metode pembayaran sewa kos melalui Midtrans Virtual Account atau Transfer Manual."
			:metrics="[
				['label' => 'Tagihan Bulan Ini', 'value' => 'Rp 1.500.000', 'note' => 'Jatuh tempo 01 Nov'],
				['label' => 'Metode Tersedia', 'value' => 'VA & Manual', 'note' => 'BCA, Mandiri, BNI'],
				['label' => 'Status Tagihan', 'value' => 'Menunggu Pembayaran', 'note' => 'Belum lunas'],
				['label' => 'Total Pembayaran Sukses', 'value' => '11 Kali', 'note' => 'Riwayat tersimpan'],
			]"
			:columns="[
				['label' => 'Periode Sewa', 'key' => 'period'],
				['label' => 'Metode', 'key' => 'method'],
				['label' => 'Jumlah Tagihan', 'key' => 'amount'],
				['label' => 'Tanggal Bayar', 'key' => 'paid_at'],
				['label' => 'Status', 'key' => 'status'],
			]"
			:rows="[
				['period' => 'Oktober 2026', 'method' => 'Transfer Manual', 'amount' => 'Rp 1.500.000', 'paid_at' => '01 Okt 2026', 'status' => 'Lunas'],
				['period' => 'September 2026', 'method' => 'Midtrans VA', 'amount' => 'Rp 1.500.000', 'paid_at' => '01 Sep 2026', 'status' => 'Lunas'],
				['period' => 'November 2026', 'method' => 'Menunggu Pilihan', 'amount' => 'Rp 1.500.000', 'paid_at' => '-', 'status' => 'Menunggu'],
			]"
		/>
	</div>
@endsection
