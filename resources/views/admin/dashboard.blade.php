@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Dashboard Admin" />

<div
    x-data="{
        isCreateModalOpen: false,
        isEditModalOpen: false,
        isDeleteConfirmOpen: false,
        editingPost: null,
        editTitle: '',
        editContent: '',
        editTargetType: 'all',
        editLocationId: '',
        editRoomId: '',
        editHasExpiry: false,
        editExpiresAt: '',
        targetType: 'all',
        selectedLocationId: '',
        allRooms: {{ Js::from($allRooms->map(fn($r) => ['id' => $r->id, 'location_id' => $r->location_id, 'label' => 'Kamar ' . $r->room_number . ' (Lantai ' . $r->floor_name . ')'])) }},
        get filteredRooms() { return this.selectedLocationId ? this.allRooms.filter(r => String(r.location_id) === String(this.selectedLocationId)) : []; },
        get editFilteredRooms() { return this.editLocationId ? this.allRooms.filter(r => String(r.location_id) === String(this.editLocationId)) : []; }
    }"
    class="space-y-6"
>
    @include('admin.dashboard.dashboard-notifications')
    @include('admin.dashboard.admin-profile-summary')
    @include('admin.dashboard.room-metrics')
    @include('admin.finance.financial-summary-cards')
    @include('admin.dashboard.payment-history-summary')
    @include('admin.dashboard.payment-deadlines')
    @include('admin.dashboard.announcement-board')

    <x-smart-kos.create-post-modal
        storeRoute="admin.posts.store"
        :locations="$locations"
    />
</div>
@endsection
