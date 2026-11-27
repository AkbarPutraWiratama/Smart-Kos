{{-- Popup Action Modal for Admin Complaint Handling --}}
<div
    x-data="{
        isOpen: false,
        activeComplaint: {},
        newStatus: 'in_progress',
        actionImageUrl: null,
        actionFileName: '',
        actionFileSize: '',
        openModal(data) {
            this.activeComplaint = data;
            this.newStatus = data.status || 'in_progress';
            this.clearActionImage();
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            this.isOpen = false;
            this.clearActionImage();
            document.body.classList.remove('overflow-hidden');
        },
        previewActionImage(event) {
            const file = event.target.files[0];
            if (file) {
                this.actionFileName = file.name;
                this.actionFileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.actionImageUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        clearActionImage() {
            this.actionImageUrl = null;
            this.actionFileName = '';
            this.actionFileSize = '';
            const input = document.getElementById('admin-modal-image');
            if (input) input.value = '';
        }
    }"
    @open-complaint-modal.window="openModal($event.detail)"
    @keydown.escape.window="closeModal()"
    x-cloak
>
    {{-- Modal Backdrop (No blur) --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-999998 bg-gray-900/60"
        @click="closeModal()"
    ></div>

    {{-- Modal Dialog with clean pop up animation --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-4 sm:p-6"
    >
        <div
            @click.stop
            class="relative my-auto flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            {{-- Modal Header (Pinned) --}}
            <div class="shrink-0 flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                        Tindak Lanjuti Aduan (Admin)
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

            {{-- Form Wrapper ensuring Pinned Footer & Scrollable Body --}}
            <form :action="activeComplaint.postUrl" method="POST" enctype="multipart/form-data" class="flex flex-1 min-h-0 flex-col overflow-hidden">
                @csrf

                {{-- Modal Body (Scrollable) --}}
                <div class="flex-1 min-h-0 overflow-y-auto p-6 space-y-6 custom-scrollbar">

                    {{-- Detail Aduan --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-white/90" x-text="activeComplaint.title"></h4>
                            <span class="text-[11px] text-gray-400" x-text="activeComplaint.submittedAt"></span>
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-gray-600 dark:text-gray-300 whitespace-pre-line" x-text="activeComplaint.description"></p>

                        {{-- Lampiran Foto Aduan --}}
                        <template x-if="activeComplaint.imagePath">
                            <div class="mt-3.5" x-data="{ modalImgLightbox: false }">
                                <p class="mb-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">Foto Bukti Kerusakan dari Pelapor:</p>
                                <div class="group relative inline-block cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-theme-xs transition hover:border-brand-500 dark:border-gray-700 dark:bg-gray-800"
                                     @click="modalImgLightbox = true">
                                    <img :src="activeComplaint.imagePath" alt="Foto Kerusakan"
                                         class="h-28 w-44 object-cover transition duration-300 group-hover:scale-105">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-black/60 px-2 py-0.5 text-[11px] font-semibold text-white">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="11" cy="11" r="8"/>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                                <line x1="11" y1="8" x2="11" y2="14"/>
                                                <line x1="8" y1="11" x2="14" y2="11"/>
                                            </svg>
                                            Perbesar
                                        </span>
                                    </div>
                                </div>

                                {{-- Lightbox (No blur) --}}
                                <div x-show="modalImgLightbox"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="fixed inset-0 z-[9999999] flex items-center justify-center bg-black/85 p-4"
                                     @click="modalImgLightbox = false"
                                     @keydown.escape.window="modalImgLightbox = false"
                                     x-cloak>
                                    <div class="relative max-h-[90vh] max-w-3xl overflow-hidden rounded-2xl bg-black/90 p-2" @click.stop>
                                        <button type="button" @click="modalImgLightbox = false"
                                                class="absolute end-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/90">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </button>
                                        <img :src="activeComplaint.imagePath" alt="Foto Kerusakan"
                                             class="max-h-[85vh] w-auto max-w-full rounded-xl object-contain mx-auto">
                                    </div>
                                </div>
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
                                            <div class="mt-2" x-data="{ actionItemLightbox: false }">
                                                <div class="group relative inline-block cursor-pointer overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
                                                     @click="actionItemLightbox = true">
                                                    <img :src="action.img" alt="Foto Dokumentasi" class="h-14 w-20 object-cover transition group-hover:scale-105">
                                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100">
                                                        <span class="rounded bg-black/60 px-1 py-0.5 text-[9px] font-bold text-white">Lihat</span>
                                                    </div>
                                                </div>
                                                <div x-show="actionItemLightbox"
                                                     x-transition:enter="transition ease-out duration-200"
                                                     x-transition:enter-start="opacity-0 scale-95"
                                                     x-transition:enter-end="opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-150"
                                                     x-transition:leave-start="opacity-100 scale-100"
                                                     x-transition:leave-end="opacity-0 scale-95"
                                                     class="fixed inset-0 z-[9999999] flex items-center justify-center bg-black/85 p-4"
                                                     @click="actionItemLightbox = false"
                                                     @keydown.escape.window="actionItemLightbox = false"
                                                     x-cloak>
                                                    <div class="relative max-h-[90vh] max-w-3xl overflow-hidden rounded-2xl bg-black/90 p-2" @click.stop>
                                                        <button type="button" @click="actionItemLightbox = false"
                                                                class="absolute end-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/90">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                                            </svg>
                                                        </button>
                                                        <img :src="action.img" alt="Foto Dokumentasi" class="max-h-[85vh] w-auto max-w-full rounded-xl object-contain mx-auto">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Form Input Fields Section --}}
                    <div class="space-y-4 border-t border-gray-200 pt-5 dark:border-gray-800">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                            Catat Tindakan &amp; Perbarui Status
                        </h4>

                        {{-- Pilihan Status --}}
                        <div>
                            <label for="admin-modal-status" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Status Baru Aduan <span class="text-error-500">*</span>
                            </label>
                            <select
                                name="status"
                                id="admin-modal-status"
                                x-model="newStatus"
                                required
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <option value="open">Belum Ditangani</option>
                                <option value="in_progress">Sedang Dalam Perbaikan</option>
                                <option value="resolved">Selesai Ditangani</option>
                            </select>
                        </div>

                        {{-- Deskripsi Tindakan --}}
                        <div>
                            <label for="admin_action_description" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Deskripsi Tindakan / Progres Perbaikan <span class="text-error-500">*</span>
                            </label>
                            <textarea
                                name="action_description"
                                id="admin_action_description"
                                rows="3"
                                placeholder="Contoh: Petugas teknisi telah mengganti spare part dan memeriksa fungsi saluran..."
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            ></textarea>
                        </div>

                        {{-- Upload Foto Tindakan with Live Preview --}}
                        <div>
                            <label for="admin-modal-image" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                Foto Bukti Perbaikan <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input
                                type="file"
                                name="image"
                                id="admin-modal-image"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                @change="previewActionImage($event)"
                                class="block w-full text-xs text-gray-500 file:me-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300 dark:hover:file:bg-gray-700"
                            >

                            {{-- Instant Preview of Selected Image --}}
                            <template x-if="actionImageUrl">
                                <div class="relative mt-2 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-800">
                                    <img :src="actionImageUrl" alt="Preview Tindakan" class="h-14 w-14 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                    <div class="flex-1 min-w-0">
                                        <p class="truncate text-xs font-semibold text-gray-800 dark:text-white/90" x-text="actionFileName"></p>
                                        <p class="text-[11px] text-gray-400" x-text="actionFileSize"></p>
                                        <span class="inline-flex items-center gap-1 text-[10px] text-success-600 dark:text-success-400">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            Siap diunggah
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="clearActionImage()"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-200 hover:text-error-500 dark:hover:bg-gray-700"
                                        title="Hapus foto"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Integrasi Keuangan: Biaya Perbaikan (Opsional) --}}
                        <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-white/[0.02]">
                            <label for="admin-modal-cost" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-200">
                                Biaya Perbaikan / Pembelian Material (Rp) <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <input
                                type="number"
                                name="cost"
                                id="admin-modal-cost"
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
                            x-show="newStatus === 'resolved'"
                            x-transition
                            class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-xs text-warning-900 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-200"
                        >
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="confirm_selesai"
                                    value="1"
                                    :required="newStatus === 'resolved'"
                                    class="mt-0.5 rounded border-warning-400 text-brand-600 focus:ring-brand-500 dark:border-warning-600 dark:bg-gray-800"
                                >
                                <span>
                                    <strong>Konfirmasi Penyelesaian:</strong> Saya menyatakan bahwa penanganan aduan ini telah diselesaikan dengan tuntas dan fasilitas telah kembali berfungsi normal.
                                </span>
                            </label>
                        </div>
                    </div>

                </div>

                {{-- Modal Footer Actions (Pinned at bottom) --}}
                <div class="shrink-0 flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50/90 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/90">
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
