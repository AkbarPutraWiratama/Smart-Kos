@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Keuangan" />

        <x-smart-kos.finance-tabs active="report" />

        @include('admin.finance.financial-report-filters')
        @include('admin.finance.financial-report-summary')
        @include('admin.finance.financial-record-ledger')
        @include('admin.finance.payment-history')
    </div>
@endsection
