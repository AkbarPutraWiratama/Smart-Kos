@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Keuangan" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">Riwayat Pembayaran</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Riwayat pembayaran Virtual Account dan transfer manual tetap tersimpan meskipun status penyewa atau kamar berubah.</p>
        </div>

        @include('admin.finance.payment-history')
    </div>
@endsection
