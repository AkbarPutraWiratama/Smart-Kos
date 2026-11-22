<!-- Kelola Kamar -->
<div x-show="isManageAssignmentModalOpen" x-cloak @keydown.escape.window="isManageAssignmentModalOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
    <div @click="isManageAssignmentModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
        <template x-if="currentAssignment">
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 dark:text-white/90" x-text="currentAssignment.tenant_profile?.user?.name"></h3>
                        <p class="text-xs text-gray-400" x-text="'Kamar ' + currentAssignment.room?.room_number"></p>
                    </div>
                    <button type="button" @click="isManageAssignmentModalOpen = false" class="text-gray-400">&times;</button>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 text-xs text-gray-600 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-300 space-y-1">
                    <div class="flex justify-between"><span>Tarif Sewa:</span><span class="font-semibold" x-text="formatRupiah(currentAssignment.rent_amount_snapshot)"></span></div>
                    <div class="flex justify-between"><span>Tarif Berikutnya:</span><span x-text="formatRupiah(currentAssignment.room?.rent_amount || 0)"></span></div>
                    <div class="flex justify-between"><span>Tanggal Mulai:</span><span x-text="currentAssignment.start_date ? new Date(currentAssignment.start_date).toLocaleDateString('id-ID') : '-'"></span></div>
                    <div class="flex justify-between"><span>Tanggal Berakhir:</span><span x-text="currentAssignment.end_date ? new Date(currentAssignment.end_date).toLocaleDateString('id-ID') : 'Masih aktif'"></span></div>
                    <div class="flex justify-between"><span>Rentang Pembayaran:</span><span x-text="currentAssignment.payment_cycle === 'yearly' ? 'Tahunan' : 'Bulanan'"></span></div>
                </div>

                <div class="mt-4 rounded-xl border border-gray-100 p-3.5 dark:border-gray-800">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status Pembayaran</p>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90" x-text="currentAssignment.payment_status || 'Belum Bayar'"></p>
                    <p class="mt-0.5 text-[11px] text-gray-500" x-text="currentAssignment.deadline_label || ''"></p>
                    <div class="mt-3 space-y-2">
                        <template x-for="payment in (currentAssignment.manual_payments || [])" :key="payment.id">
                            <div class="rounded-lg border border-gray-100 px-3 py-2.5 dark:border-gray-800">
                                <div class="flex items-start justify-between gap-3 text-[11px]">
                                    <div><p class="font-semibold">Transfer Manual</p><p class="text-gray-500" x-text="payment.submitted_at ? new Date(payment.submitted_at).toLocaleDateString('id-ID') : '-'"></p></div>
                                    <div class="text-end"><p class="font-semibold" x-text="formatRupiah(payment.amount)"></p><p class="text-gray-500" x-text="payment.status_label || payment.status"></p></div>
                                </div>
                                <template x-if="payment.status === 'pending'">
                                    <div class="mt-2 flex items-center gap-2">
                                        <button type="button" @click="openApprovePaymentModal(payment)" class="h-8 rounded-lg bg-success-500 px-2.5 text-[11px] font-semibold text-white shadow-theme-xs hover:bg-success-600">Setujui</button>
                                        <button type="button" @click="openRejectPaymentModal(payment)" class="h-8 rounded-lg bg-error-500 px-2.5 text-[11px] font-semibold text-white shadow-theme-xs hover:bg-error-600">Tolak</button>
                                        <a x-show="payment.proof_url" :href="payment.proof_url" target="_blank" rel="noopener" class="h-8 ms-auto inline-flex items-center rounded-lg bg-brand-500 px-2.5 text-[11px] font-semibold text-white shadow-theme-xs hover:bg-brand-600">Lihat Bukti</a>
                                    </div>
                                </template>
                                <p x-show="payment.status === 'rejected' && payment.rejection_reason" class="mt-1 text-[11px] text-error-600" x-text="'Alasan: ' + payment.rejection_reason"></p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-4 space-y-2.5">
                    <button type="button" @click="openEditAssignmentModal()" class="w-full flex items-center justify-between p-3.5 rounded-xl border border-gray-200 hover:border-brand-500 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">Edit</span>
                        <span class="text-gray-400">&rarr;</span>
                    </button>
                </div>

                <div class="mt-5 flex justify-end"><button type="button" @click="isManageAssignmentModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Tutup</button></div>
            </div>
        </template>
    </div>
</div>

<!-- Konfirmasi Setujui Pembayaran -->
<div x-show="showApprovePaymentModal" x-cloak @keydown.escape.window="showApprovePaymentModal = false" class="fixed inset-0 z-999999 flex items-center justify-center p-4">
    <div @click="showApprovePaymentModal = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-base font-bold text-gray-800 dark:text-white">Konfirmasi Setujui Pembayaran</h3>
        <form :action="approvePaymentActionUrl" method="POST" class="mt-4 space-y-4">
            @csrf
            <p class="text-xs text-gray-500 dark:text-gray-400">Setujui pembayaran <strong x-text="verifyPaymentAmount"></strong> dari <strong x-text="verifyPaymentTenantName"></strong>?</p>
            <div class="flex justify-end gap-2"><button type="button" @click="showApprovePaymentModal = false" class="rounded-xl border border-gray-200 px-4 py-2 text-xs">Batal</button><button type="submit" class="rounded-xl bg-success-500 px-4 py-2 text-xs font-semibold text-white hover:bg-success-600">Ya, Setujui</button></div>
        </form>
    </div>
</div>

<!-- Konfirmasi Tolak Pembayaran -->
<div x-show="showRejectPaymentModal" x-cloak @keydown.escape.window="showRejectPaymentModal = false" class="fixed inset-0 z-999999 flex items-center justify-center p-4">
    <div @click="showRejectPaymentModal = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-base font-bold text-gray-800 dark:text-white">Konfirmasi Tolak Pembayaran</h3>
        <form :action="rejectPaymentActionUrl" method="POST" class="mt-4 space-y-4">
            @csrf
            <div><label for="status_rejection_reason" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">Alasan Penolakan <span class="text-error-500">*</span></label><textarea id="status_rejection_reason" name="rejection_reason" x-model="rejectionReason" required maxlength="255" rows="3" class="w-full rounded-xl border border-gray-300 p-3 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea></div>
            <div class="flex justify-end gap-2"><button type="button" @click="showRejectPaymentModal = false" class="rounded-xl border border-gray-200 px-4 py-2 text-xs">Batal</button><button type="submit" class="rounded-xl bg-error-500 px-4 py-2 text-xs font-semibold text-white hover:bg-error-600">Konfirmasi Tolak</button></div>
        </form>
    </div>
</div>

<!-- Edit Kamar -->
<div x-show="isEditAssignmentModalOpen" x-cloak @keydown.escape.window="isEditAssignmentModalOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5">
    <div @click="isEditAssignmentModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
        <template x-if="currentAssignment">
            <form method="POST" :action="'/admin/assignments/' + currentAssignment.id + '/price'" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Edit Kamar</h3>
                    <button type="button" @click="isEditAssignmentModalOpen = false" class="text-gray-400">&times;</button>
                </div>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <button type="button" @click="isEditAssignmentModalOpen = false; isChangeRoomModalOpen = true" class="rounded-xl border border-warning-300 bg-warning-500 px-3 py-2.5 text-xs font-medium text-white shadow-theme-xs hover:border-warning-600 hover:bg-warning-600 active:scale-95 transition dark:border-warning-500/40">
                        Pindah Kamar
                    </button>
                    <button type="button" @click="isEditAssignmentModalOpen = false; isEndAssignmentModalOpen = true" class="rounded-xl border border-error-300 bg-error-500 px-3 py-2.5 text-xs font-medium text-white shadow-theme-xs hover:border-error-600 hover:bg-error-600 active:scale-95 transition dark:border-error-500/40">
                        Akhiri Masa Sewa
                    </button>
                </div>

                <div>
                    <label for="assignment_rent_amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Sewa <span class="text-error-500">*</span></label>
                    <input id="assignment_rent_amount" name="rent_amount" type="number" min="1" step="1" x-model="editAssignmentRentAmount" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="edit_payment_cycle" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Rentang Pembayaran <span class="text-error-500">*</span></label>
                    <select id="edit_payment_cycle" name="payment_cycle" x-model="editPaymentCycle" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_start_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai <span class="text-error-500">*</span></label>
                        <input id="edit_start_date" name="start_date" type="date" x-model="editStartDate" @click="$el.showPicker()" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                    <div>
                        <label for="edit_end_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Berakhir</label>
                        <input id="edit_end_date" type="date" :value="editCalculatedEndDate" readonly class="h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400" />
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                    <button type="button" @click="isEditAssignmentModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
                    <button type="button" @click="openEditSaveConfirmationModal($el.closest('form'))" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan Perubahan</button>
                </div>
            </form>

        </template>
    </div>
</div>

<!-- Konfirmasi Simpan Edit Kamar -->
<div x-show="showEditSaveConfirmationModal" x-cloak @keydown.escape.window="showEditSaveConfirmationModal = false" class="fixed inset-0 z-999999 flex items-center justify-center p-4">
    <div @click="showEditSaveConfirmationModal = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
        <template x-if="currentAssignment">
            <form :action="editSaveActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="rent_amount" :value="editAssignmentRentAmount">
                <input type="hidden" name="payment_cycle" :value="editPaymentCycle">
                <input type="hidden" name="start_date" :value="editStartDate">
                <input type="hidden" name="end_date" :value="editCalculatedEndDate">

                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Konfirmasi Simpan Perubahan</h3>
                    <button type="button" @click="showEditSaveConfirmationModal = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">&times;</button>
                </div>

                <div class="rounded-xl border border-brand-200 bg-brand-50/60 p-3.5 text-xs dark:border-brand-500/20 dark:bg-brand-500/10">
                    <p class="font-semibold text-brand-800 dark:text-brand-300">Perubahan yang akan disimpan</p>
                    <div class="mt-2 space-y-1 text-brand-700 dark:text-brand-400">
                        <p>Harga Sewa: <strong x-text="formatRupiah(editAssignmentRentAmount)"></strong></p>
                        <p>Rentang: <strong x-text="editPaymentCycle === 'yearly' ? 'Tahunan' : 'Bulanan'"></strong></p>
                        <p>Tanggal Mulai: <strong x-text="editStartDate ? new Date(editStartDate).toLocaleDateString('id-ID') : '-'"></strong></p>
                        <p>Tanggal Berakhir: <strong x-text="editCalculatedEndDate ? new Date(editCalculatedEndDate).toLocaleDateString('id-ID') : '-'"></strong></p>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="showEditSaveConfirmationModal = false; isEditAssignmentModalOpen = true" class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Kembali</button>
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600">Ya, Simpan Perubahan</button>
                </div>
            </form>
        </template>
    </div>
</div>

<!-- Isi Kamar -->
<div x-show="isAssignModalOpen" x-cloak @keydown.escape.window="isAssignModalOpen = false; isRoomLocked = false; lockedRoomId = ''" class="fixed inset-0 z-999999 flex items-center justify-center p-5">
    <div @click="isAssignModalOpen = false; isRoomLocked = false; lockedRoomId = ''" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Penempatan Kamar Penyewa</h3>
        <p x-show="isRoomLocked" x-cloak class="mb-3 inline-flex rounded-md bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">Kamar dikunci dari Visualisasi</p>
        <form method="POST" action="{{ route('admin.assignments.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="assign_tenant_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Penyewa <span class="text-error-500">*</span></label>
                <select id="assign_tenant_id" name="tenant_id" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">-- Pilih Penyewa --</option>
                    @foreach ($allUnassignedProfiles as $profile)
                        <option value="{{ $profile->id }}" :selected="selectedTenantId == {{ $profile->id }}">{{ $profile->user?->name }} ({{ '@' . $profile->user?->username }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="assign_room_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kamar <span class="text-error-500">*</span></label>
                <select
                    id="assign_room_id"
                    name="room_id"
                    x-model="lockedRoomId"
                    :disabled="isRoomLocked"
                    :class="isRoomLocked ? 'cursor-not-allowed bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' : 'bg-transparent text-gray-800 dark:text-white/90'"
                    required
                    class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700"
                >
                    <option value="">-- Pilih Kamar --</option>
                    @foreach ($availableRooms as $room)
                        <option value="{{ $room->id }}" :selected="lockedRoomId == {{ $room->id }}">Kamar {{ $room->room_number }} — Rp {{ number_format($room->rent_amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="room_id" :value="lockedRoomId" :disabled="!isRoomLocked">
            </div>
            <div>
                <label for="assign_payment_cycle" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Rentang Pembayaran <span class="text-error-500">*</span></label>
                <select id="assign_payment_cycle" name="payment_cycle" x-model="assignBillingCycle" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="assign_start_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai <span class="text-error-500">*</span></label>
                    <input id="assign_start_date" name="start_date" type="date" x-model="assignStartDate" @click="$el.showPicker()" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div>
                    <label for="assign_end_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Berakhir <span class="text-error-500">*</span></label>
                    <input
                        id="assign_end_date"
                        type="date"
                        :value="calculatedEndDate"
                        readonly
                        class="h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                    />
                    <input type="hidden" name="end_date" :value="calculatedEndDate">
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        Otomatis terkunci 1 bulan atau 1 tahun dari tanggal mulai.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3"><button type="button" @click="isAssignModalOpen = false; isRoomLocked = false; lockedRoomId = ''" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm">Batal</button><button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Simpan</button></div>
        </form>
    </div>
</div>

<!-- Pindah Kamar -->
<div x-show="isChangeRoomModalOpen" x-cloak @keydown.escape.window="isChangeRoomModalOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center p-5">
    <div @click="isChangeRoomModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Pindah Kamar Penghuni</h3>
        <form method="POST" :action="'/admin/assignments/' + currentAssignment.id + '/change-room'" class="space-y-4">
            @csrf
            @method('PATCH')
            <select id="new_room_id" name="new_room_id" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">-- Pilih Kamar Kosong --</option>
                @foreach ($availableRooms as $room)
                    <option value="{{ $room->id }}">Kamar {{ $room->room_number }} — Rp {{ number_format($room->rent_amount, 0, ',', '.') }}</option>
                @endforeach
            </select>
            <input name="transfer_date" type="date" value="{{ date('Y-m-d') }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            <div class="flex justify-end gap-3"><button type="button" @click="isChangeRoomModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm">Batal</button><button type="button" @click="openChangeRoomConfirmationModal($el.closest('form'))" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white">Konfirmasi Pindah Kamar</button></div>
        </form>
    </div>
</div>

<!-- Konfirmasi Pindah Kamar -->
<div x-show="showChangeRoomConfirmationModal" x-cloak @keydown.escape.window="showChangeRoomConfirmationModal = false" class="fixed inset-0 z-999999 flex items-center justify-center p-4">
    <div @click="showChangeRoomConfirmationModal = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-base font-bold text-gray-800 dark:text-white">Konfirmasi Pindah Kamar</h3>
        <form :action="changeRoomActionUrl" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="new_room_id" :value="changeRoomTargetId">
            <input type="hidden" name="transfer_date" :value="changeRoomTransferDate">
            <p class="text-xs text-gray-500 dark:text-gray-400">Pindahkan dari <strong x-text="'Kamar ' + currentAssignment.room?.room_number"></strong> ke <strong x-text="changeRoomTargetName"></strong>.</p>
            <p x-show="(currentAssignment.manual_payments || []).length > 0" class="text-xs text-gray-500">Manual payment akan dibawa ke kamar baru.</p>
            <label for="change_room_password" class="block text-xs font-medium">Kata Sandi Konfirmasi Admin <span class="text-error-500">*</span></label>
            <input id="change_room_password" name="password" type="password" required autocomplete="current-password" class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            <div class="flex justify-end gap-2"><button type="button" @click="showChangeRoomConfirmationModal = false; isChangeRoomModalOpen = true" class="rounded-xl border border-gray-200 px-4 py-2 text-xs">Kembali</button><button type="submit" class="rounded-xl bg-warning-600 px-4 py-2 text-xs font-semibold text-white hover:bg-warning-700">Ya, Pindahkan Penyewa</button></div>
        </form>
    </div>
</div>

<!-- Akhiri Sewa -->
<div x-show="isEndAssignmentModalOpen" x-cloak @keydown.escape.window="isEndAssignmentModalOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center p-5">
    <div @click="isEndAssignmentModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Konfirmasi Akhiri Masa Sewa</h3>
        <form method="POST" :action="'/admin/assignments/' + currentAssignment.id + '/end'" class="space-y-4">
            @csrf
            <p class="text-xs text-gray-500 dark:text-gray-400">Aksi ini akan mengakhiri penugasan kamar untuk penyewa saat ini.</p>
            <label for="modal_end_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sandi Konfirmasi Admin <span class="text-error-500">*</span></label>
            <input id="modal_end_password" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            <div class="flex justify-end gap-3"><button type="button" @click="isEndAssignmentModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm">Batal</button><button type="submit" class="rounded-lg bg-warning-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-warning-700">Ya, Selesaikan Sewa</button></div>
        </form>
    </div>
</div>
