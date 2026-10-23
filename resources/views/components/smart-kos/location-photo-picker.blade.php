@props([
    'photos' => [],
    'legacyPath' => null,
    'inputId' => 'location_photos',
    'inputName' => 'photos',
    'label' => 'Foto lokasi',
    'badge' => null,
    'helperText' => 'Pilih beberapa foto sekaligus, simpan, lalu ulangi untuk menambahkan foto lainnya tanpa menghapus foto sebelumnya. Maksimal 10 foto per sesi, masing-masing 4 MB.',
    'slider' => false,
    'location' => null,
])

@php
    $storedImages = collect();
    if ($legacyPath) {
        $storedImages->push([
            'id' => 'legacy',
            'isLegacy' => true,
            'url' => asset('storage/' . $legacyPath),
            'type' => 'location',
            'badge' => 'Foto Lokasi',
            'deleteUrl' => $location ? route('admin.locations.legacy-photo.destroy', $location) : null,
        ]);
    }
    foreach ($photos as $photo) {
        $photoType = $photo->type ?? 'location';
        $storedImages->push([
            'id' => (string) $photo->id,
            'isLegacy' => false,
            'url' => asset('storage/' . $photo->path),
            'type' => $photoType,
            'badge' => $photoType === 'room' ? 'Foto Kamar' : 'Foto Lokasi',
            'deleteUrl' => $location ? route('admin.locations.photos.destroy', [$location, $photo]) : null,
        ]);
    }
@endphp

<div x-data="{
    selectedFiles: [],
    previews: [],
    updatePreviews(event) {
        this.selectedFiles = [...this.selectedFiles, ...Array.from(event.target.files)];
        this.previews = this.selectedFiles.map((file) => URL.createObjectURL(file));
        this.syncInput(event.target);
    },
    syncInput(input) {
        const transfer = new DataTransfer();
        this.selectedFiles.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    }
}">
    <div class="mb-2 flex items-center justify-between">
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
        @if ($badge)
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge === 'Foto Kamar' ? 'bg-theme-purple-500/10 text-theme-purple-500 border border-theme-purple-500/20' : 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 border border-brand-500/20' }}">
                {{ $badge }}
            </span>
        @endif
    </div>
    <input id="{{ $inputId }}" name="{{ $inputName }}[]" type="file" accept="image/*" multiple @change="updatePreviews($event)" class="block w-full rounded-lg border border-gray-300 bg-transparent text-sm text-gray-600 file:me-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium dark:border-gray-700 dark:text-gray-300 dark:file:bg-gray-800" />
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $helperText }}</p>

    @error($inputName)
        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
    @enderror
    @error($inputName . '.*')
        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
    @enderror

    @if ($storedImages->isNotEmpty())
        <div class="mt-5" x-data="{
            storedImages: @js($storedImages->values()),
            selectedToDelete: [],
            isConfirmModalOpen: false,
            confirmActionUrl: '',
            confirmIsBulk: false,
            confirmMessage: '',
            toggleSelect(id) {
                if (this.selectedToDelete.includes(id)) {
                    this.selectedToDelete = this.selectedToDelete.filter(item => item !== id);
                } else {
                    this.selectedToDelete.push(id);
                }
            },
            selectAll() {
                if (this.selectedToDelete.length === this.storedImages.length) {
                    this.selectedToDelete = [];
                } else {
                    this.selectedToDelete = this.storedImages.map(img => img.id);
                }
            },
            openSingleDelete(deleteUrl) {
                this.confirmIsBulk = false;
                this.confirmActionUrl = deleteUrl;
                this.confirmMessage = 'Apakah Anda yakin ingin menghapus foto ini? Tindakan ini tidak dapat dibatalkan.';
                this.isConfirmModalOpen = true;
            },
            openBulkDelete() {
                if (this.selectedToDelete.length === 0) return;
                this.confirmIsBulk = true;
                this.confirmActionUrl = '{{ $location ? route('admin.locations.photos.bulk-destroy', $location) : '' }}';
                this.confirmMessage = `Apakah Anda yakin ingin menghapus ${this.selectedToDelete.length} foto yang dipilih? Tindakan ini tidak dapat dibatalkan.`;
                this.isConfirmModalOpen = true;
            }
        }">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Foto tersimpan ({{ $storedImages->count() }})</p>
                    <span class="text-xs text-gray-400 dark:text-gray-500">&bull;</span>
                    <button type="button" @click="selectAll()" class="text-xs font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400">
                        <span x-text="selectedToDelete.length === storedImages.length ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                    </button>
                </div>
                <template x-if="selectedToDelete.length > 0">
                    <button type="button" @click="openBulkDelete()" class="inline-flex items-center gap-1.5 rounded-lg bg-error-500 px-3 py-1.5 text-xs font-medium text-white shadow-theme-xs hover:bg-error-600 transition">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                        </svg>
                        <span>Hapus <span x-text="selectedToDelete.length"></span> Foto Terpilih</span>
                    </button>
                </template>
            </div>

            <!-- Photos Grid with Checkbox and Section Badge in Top Corners -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                <template x-for="image in storedImages" :key="image.id">
                    <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100 transition hover:shadow-theme-xs dark:border-gray-800 dark:bg-gray-800"
                        :class="selectedToDelete.includes(image.id) ? 'ring-2 ring-error-500 border-error-500' : ''">
                        <img :src="image.url" alt="Foto lokasi" class="h-32 w-full object-cover transition duration-200 group-hover:scale-105" />

                        <!-- Photo Section Tag Badge in Top Left Corner -->
                        <div class="absolute top-2 start-2 z-10 pointer-events-none">
                            <span class="rounded-md px-2 py-0.5 text-[10px] font-semibold tracking-wide shadow-sm backdrop-blur-md"
                                :class="image.type === 'room' ? 'bg-purple-600/90 text-white' : 'bg-brand-600/90 text-white'"
                                x-text="image.badge">
                            </span>
                        </div>

                        <!-- Selection Checkbox in Top Right Corner -->
                        <div class="absolute top-2 end-2 z-10">
                            <label class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-md bg-white/90 shadow-sm backdrop-blur-sm transition hover:bg-white dark:bg-gray-900/90 dark:hover:bg-gray-900">
                                <input type="checkbox"
                                    :value="image.id"
                                    :checked="selectedToDelete.includes(image.id)"
                                    @change="toggleSelect(image.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800" />
                            </label>
                        </div>

                        <!-- Single Quick Delete Button in Bottom Corner -->
                        <template x-if="image.deleteUrl">
                            <div class="absolute bottom-2 end-2 opacity-0 transition group-hover:opacity-100">
                                <button type="button" @click="openSingleDelete(image.deleteUrl)" title="Hapus foto ini" class="flex h-7 w-7 items-center justify-center rounded-lg bg-black/70 text-white hover:bg-error-600 transition shadow-sm">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Confirm Deletion Popup Modal (Confirm or Cancel - No Admin Password Required) -->
            <template x-teleport="body">
                <div x-show="isConfirmModalOpen" x-cloak @keydown.escape.window="isConfirmModalOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto bg-gray-900/60 p-5">
                    <div @click="isConfirmModalOpen = false" class="fixed inset-0"></div>
                    <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Konfirmasi Hapus Foto</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Peringatan tindakan permanen</p>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-300" x-text="confirmMessage"></p>

                        <!-- Single photo delete form -->
                        <template x-if="!confirmIsBulk">
                            <form method="POST" :action="confirmActionUrl" class="mt-6 flex justify-end gap-3">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE" />
                                <button type="button" @click="isConfirmModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                    Batal
                                </button>
                                <button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 shadow-theme-xs transition">
                                    Ya, Hapus
                                </button>
                            </form>
                        </template>

                        <!-- Bulk photo delete form -->
                        <template x-if="confirmIsBulk">
                            <form method="POST" :action="confirmActionUrl" class="mt-6">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE" />
                                <template x-for="selectedId in selectedToDelete" :key="selectedId">
                                    <div>
                                        <template x-if="selectedId === 'legacy'">
                                            <input type="hidden" name="delete_legacy" value="1" />
                                        </template>
                                        <template x-if="selectedId !== 'legacy'">
                                            <input type="hidden" name="photo_ids[]" :value="selectedId" />
                                        </template>
                                    </div>
                                </template>
                                <div class="flex justify-end gap-3">
                                    <button type="button" @click="isConfirmModalOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                        Batal
                                    </button>
                                    <button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600 shadow-theme-xs transition">
                                        Ya, Hapus Terpilih
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    @endif

    <div x-show="previews.length" x-cloak class="mt-4">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Preview foto baru</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <template x-for="preview in previews" :key="preview">
                <div class="relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <img :src="preview" alt="Preview foto baru" class="h-24 w-full object-cover" />
                    @if ($badge || $label)
                        <span class="absolute top-1 start-1 rounded px-1.5 py-0.5 text-[10px] font-semibold text-white shadow-sm backdrop-blur-sm {{ ($badge ?? $label) === 'Foto Kamar' ? 'bg-purple-600/90' : 'bg-brand-600/90' }}">
                            {{ $badge ?? $label }}
                        </span>
                    @endif
                </div>
            </template>
        </div>
    </div>
</div>
