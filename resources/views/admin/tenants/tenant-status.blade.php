@extends('layouts.app')

@section('content')
<div x-data="{
    isAssignModalOpen: false,
    isChangeRoomModalOpen: false,
    isEndAssignmentModalOpen: false,
    isManageAssignmentModalOpen: false,
    isEditAssignmentModalOpen: false,
    showEditSaveConfirmationModal: false,
    editSaveActionUrl: '',
    editAssignmentRentAmount: '',
    editPaymentCycle: 'monthly',
    editStartDate: '',
    showApprovePaymentModal: false,
    showRejectPaymentModal: false,
    showChangeRoomConfirmationModal: false,
    changeRoomTargetName: '',
    changeRoomTargetId: '',
    changeRoomTransferDate: '',
    changeRoomActionUrl: '',
    changeRoomPassword: '',
    approvePaymentActionUrl: '',
    rejectPaymentActionUrl: '',
    verifyPaymentTenantName: '',
    verifyPaymentAmount: '',
    rejectionReason: '',
    currentAssignment: null,
    selectedTenantId: null,
    isRoomLocked: false,
    lockedRoomId: '',
    assignStartDate: '{{ date('Y-m-d') }}',
    assignBillingCycle: 'monthly',
    formatNumber(value) {
        const amount = Math.round(Number(value) || 0);
        return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },
    formatRupiah(value) {
        return 'Rp ' + this.formatNumber(value);
    },
    formatDateInput(value) {
        if (!value) return '';
        const match = String(value).match(/^(\d{4}-\d{2}-\d{2})/);
        return match ? match[1] : '';
    },
    get calculatedEndDate() {
        if (!this.assignStartDate) return '';

        const [year, month, day] = this.assignStartDate.split('-').map(Number);
        let targetYear = year;
        let targetMonth = month;

        if (this.assignBillingCycle === 'yearly') {
            targetYear += 1;
        } else {
            targetMonth += 1;
            if (targetMonth > 12) {
                targetMonth = 1;
                targetYear += 1;
            }
        }

        const lastDay = new Date(targetYear, targetMonth, 0).getDate();
        const targetDay = Math.min(day, lastDay);

        return `${targetYear}-${String(targetMonth).padStart(2, '0')}-${String(targetDay).padStart(2, '0')}`;
    },
    get editCalculatedEndDate() {
        if (!this.editStartDate) return '';

        const [year, month, day] = this.editStartDate.split('-').map(Number);
        let targetYear = year;
        let targetMonth = month;

        if (this.editPaymentCycle === 'yearly') {
            targetYear += 1;
        } else {
            targetMonth += 1;
            if (targetMonth > 12) {
                targetMonth = 1;
                targetYear += 1;
            }
        }

        const lastDay = new Date(targetYear, targetMonth, 0).getDate();
        const targetDay = Math.min(day, lastDay);

        return `${targetYear}-${String(targetMonth).padStart(2, '0')}-${String(targetDay).padStart(2, '0')}`;
    },
    openEditAssignmentModal() {
        this.editAssignmentRentAmount = this.formatNumber(this.currentAssignment?.rent_amount_snapshot || 0).replaceAll('.', '');
        this.editPaymentCycle = this.currentAssignment?.payment_cycle || 'monthly';
        this.editStartDate = this.formatDateInput(this.currentAssignment?.start_date);
        this.isManageAssignmentModalOpen = false;
        this.isEditAssignmentModalOpen = true;
    },
    openEditSaveConfirmationModal(form) {
        if (!form.reportValidity()) return;
        this.editSaveActionUrl = form.action;
        this.isEditAssignmentModalOpen = false;
        this.showEditSaveConfirmationModal = true;
    },
    openApprovePaymentModal(payment) {
        this.approvePaymentActionUrl = '/admin/payments/' + payment.id + '/approve';
        this.verifyPaymentTenantName = this.currentAssignment?.tenant_profile?.user?.name || 'Penyewa';
        this.verifyPaymentAmount = this.formatRupiah(payment.amount);
        this.showApprovePaymentModal = true;
    },
    openRejectPaymentModal(payment) {
        this.rejectPaymentActionUrl = '/admin/payments/' + payment.id + '/reject';
        this.verifyPaymentTenantName = this.currentAssignment?.tenant_profile?.user?.name || 'Penyewa';
        this.verifyPaymentAmount = this.formatRupiah(payment.amount);
        this.rejectionReason = '';
        this.showRejectPaymentModal = true;
    },
    openChangeRoomConfirmationModal(form) {
        if (!form.reportValidity()) return;
        const roomSelect = form.elements.namedItem('new_room_id');
        const transferDateInput = form.elements.namedItem('transfer_date');
        const selectedRoom = roomSelect?.selectedOptions?.[0];
        this.changeRoomTargetName = selectedRoom?.textContent?.replace(/\s+/g, ' ').trim() || '';
        this.changeRoomTargetId = roomSelect?.value || '';
        this.changeRoomTransferDate = transferDateInput?.value || '';
        this.changeRoomActionUrl = '/admin/assignments/' + this.currentAssignment.id + '/change-room';
        this.changeRoomPassword = '';
        this.isChangeRoomModalOpen = false;
        this.showChangeRoomConfirmationModal = true;
    }
}" class="space-y-6 w-full max-w-full min-w-0">

    <x-common.page-breadcrumb pageTitle="Status Penyewa" />

    @include('admin.tenants.location-overview')

    @if ($statusTab === 'visualization')
        <x-smart-kos.room-visualization
            :roomsByFloor="$roomsByFloor"
            :selectedLocation="$selectedLocation"
            role="admin"
        />
    @elseif ($statusTab === 'assigned')
        @include('admin.tenants.assigned-tenants')
    @else
        @include('admin.tenants.unassigned-tenants')
    @endif

    @include('admin.tenants.status-modals')
</div>
@endsection
