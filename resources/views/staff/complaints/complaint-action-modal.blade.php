{{-- Popup Action Modal for Complaint Handling --}}
<div
    x-data="{
        isOpen: false,
        activeComplaint: {},
        newStatus: 'dalam_perbaikan',
        openModal(data) {
            this.activeComplaint = data;
            this.newStatus = data.status || 'dalam_perbaikan';
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
    @open-complaint-modal.window="openModal($event.detail)"
    @keydown.escape.window="closeModal()"
    x-cloak
>
    {{-- Modal Backdrop --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-999 bg-gray-900/60 backdrop-blur-sm"
        @click="closeModal()"
    ></div>

    {{-- Modal Dialog --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="fixed inset-0 z-9999 flex items-center justify-center p-4 sm:p-6"
    >
        <div
            @click.stop
            class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                        Tindak Lanjuti Aduan
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="activeComplaint.locationName"></span> &bull; Kamar #<span x-text="activeComplaint.roomNumber"></span> &bull; Pelapor: <span x-text="activeComplaint.tenantName"></span>
                    </p>
                </div>
                <button
                    type="button"
                    @click="closeModal()"
                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">

                {{-- Detail Aduan --}}
                <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white/90" x-text="activeComplaint.title"></h4>
                        <span class="text-[11px] text-gray-400" x-text="activeComplaint.submittedAt"></span>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-gray-600 dark:text-gray-300 whitespace-pre-line" x-text="activeComplaint.description"></p>

                    {{-- Lampiran Foto Aduan --}}
                    <template x-if="activeComplaint.imagePath">
                        <div class="mt-3">
                            <a :href="activeComplaint.imagePath" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                Buka Foto Bukti Kerusakan
                            </a>
                        </div>
                    </template>
                </div>

                {{-- Riwayat Tindakan Sebelumnya --}}
                <template x-if="activeComplaint.actions && activeComplaint.actions.length > 0">
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Riwayat Tindakan Penanganan (<span x-text="activeComplaint.actions.length"></span>)
                        </h4>
                        <div class="space-y-2.5">
                            <template x-for="(action, index) in activeComplaint.actions" :key="index">
                                <div class="rounded-xl border border-gray-200 bg-white p-3 text-xs dark:border-gray-800 dark:bg-gray-800/40">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-800 dark:text-white/90" x-text="action.staff"></span>
                                        <span class="text-[11px] text-gray-400" x-text="action.date"></span>
                                    </div>
                                    <p class="mt-1 text-gray-600 dark:text-gray-300" x-text="action.desc"></p>
                                    <template x-if="action.img">
                                        <div class="mt-1.5">
                                            <a :href="action.img" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-brand-600 hover:underline dark:text-brand-400">
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                                Foto Dokumentasi
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Form Catat Tindakan Baru --}}
                <form :action="activeComplaint.postUrl" method="POST" enctype="multipart/form-data" class="space-y-4 border-t border-gray-200 pt-5 dark:border-gray-800">
                    @csrf

                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                        Catat Tindakan &amp; Perbarui Status
                    </h4>

                    {{-- Pilihan Status --}}
                    <div>
                        <label for="modal-status" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Status Baru Aduan <span class="text-error-500">*</span>
                        </label>
                        <select
                            name="status"
                            id="modal-status"
                            x-model="newStatus"
                            required
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="belum">Belum Ditangani</option>
                            <option value="dalam_perbaikan">Sedang Dalam Perbaikan</option>
                            <option value="selesai">Selesai Ditangani</option>
                        </select>
                    </div>

                    {{-- Deskripsi Tindakan --}}
                    <div>
                        <label for="action_description" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Deskripsi Tindakan / Progres Perbaikan <span class="text-error-500">*</span>
                        </label>
                        <textarea
                            name="action_description"
                            id="action_description"
                            rows="3"
                            placeholder="Contoh: Sudah diperiksa petugas, pipa saluran air telah diganti dengan yang baru..."
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        ></textarea>
                    </div>

                    {{-- Upload Foto Tindakan --}}
                    <div>
                        <label for="modal-image" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Foto Bukti Perbaikan <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <input
                            type="file"
                            name="image"
                            id="modal-image"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="block w-full text-xs text-gray-500 file:me-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300 dark:hover:file:bg-gray-700"
                        >
                    </div>

                    {{-- Integrasi Keuangan: Biaya Perbaikan (Opsional) --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <label for="modal-cost" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                            Biaya Perbaikan / Pembelian Material (Rp) <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <input
                            type="number"
                            name="cost"
                            id="modal-cost"
                            min="0"
                            step="1000"
                            placeholder="0"
                            class="h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        >
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            Jika diisi, nominal ini akan <strong>langsung tercatat sebagai Beban Pemeliharaan (Expense)</strong> di modul Keuangan dan masuk ke Buku Kas kos.
                        </p>
                    </div>

                    {{-- Konfirmasi Wajib jika Status diubah ke Selesai --}}
                    <div
                        x-show="newStatus === 'selesai'"
                        x-transition
                        class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-xs text-warning-900 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-200"
                    >
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                name="confirm_selesai"
                                value="1"
                                :required="newStatus === 'selesai'"
                                class="mt-0.5 rounded border-warning-400 text-brand-600 focus:ring-brand-500 dark:border-warning-600 dark:bg-gray-800"
                            >
                            <span>
                                <strong>Konfirmasi Penyelesaian:</strong> Saya menyatakan bahwa penanganan aduan ini telah diselesaikan dengan tuntas dan fasilitas telah kembali berfungsi normal.
                            </span>
                        </label>
                    </div>

                    {{-- Modal Footer Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-3">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                        >
                            Simpan Tindakan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
