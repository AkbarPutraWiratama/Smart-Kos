@props([
    'storeRoute',   // e.g. 'admin.posts.store' or 'staff.posts.store'
    'locations',    // Collection<Location>
])
{{--
    Create Announcement Modal
    Parent x-data must include: isCreateModalOpen, targetType, selectedLocationId, filteredRooms
--}}
<div
    x-show="isCreateModalOpen"
    x-cloak
    class="fixed inset-0 z-99999 flex items-start justify-center overflow-y-auto bg-gray-900/60 p-4 pt-10 backdrop-blur-sm"
    @keydown.escape.window="isCreateModalOpen = false"
>
    <div
        @click.outside="isCreateModalOpen = false"
        class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900 mb-10"
    >
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </span>
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Buat Pengumuman Baru
                </h3>
            </div>
            <button
                type="button"
                @click="isCreateModalOpen = false"
                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route($storeRoute) }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
            @csrf

            {{-- Title --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Judul Pengumuman <span class="text-error-500">*</span>
                </label>
                <input
                    type="text"
                    name="title"
                    required
                    maxlength="255"
                    placeholder="Contoh: Jadwal Pemeliharaan Toren Air"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                />
            </div>

            {{-- Target Type --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Target Penerima <span class="text-error-500">*</span>
                </label>
                <div class="mt-1.5 grid grid-cols-3 gap-2">
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-2.5 text-xs transition"
                           :class="targetType === 'all' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="all" x-model="targetType" class="sr-only">
                        <span>Semua Lokasi</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-2.5 text-xs transition"
                           :class="targetType === 'location' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="location" x-model="targetType" class="sr-only">
                        <span>Satu Lokasi</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-2.5 text-xs transition"
                           :class="targetType === 'room' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="room" x-model="targetType" class="sr-only">
                        <span>Kamar Tertentu</span>
                    </label>
                </div>
            </div>

            {{-- Location Select --}}
            <div x-show="targetType === 'location' || targetType === 'room'" x-cloak>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Pilih Lokasi Kos <span class="text-error-500">*</span>
                </label>
                <select
                    name="location_id"
                    x-model="selectedLocationId"
                    :required="targetType === 'location' || targetType === 'room'"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">-- Pilih Lokasi Kos --</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Room Select --}}
            <div x-show="targetType === 'room'" x-cloak>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Pilih Kamar <span class="text-error-500">*</span>
                </label>
                <select
                    name="room_id"
                    :required="targetType === 'room'"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">-- Pilih Kamar --</option>
                    <template x-for="r in filteredRooms" :key="r.id">
                        <option :value="r.id" x-text="r.label"></option>
                    </template>
                </select>
                <p x-show="selectedLocationId && filteredRooms.length === 0" class="mt-1 text-[11px] text-warning-600">
                    Tidak ada kamar terdaftar pada lokasi yang dipilih.
                </p>
            </div>

            {{-- Content --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Isi Pengumuman <span class="text-error-500">*</span>
                </label>
                <textarea
                    name="content"
                    rows="4"
                    required
                    placeholder="Tuliskan informasi penting pengumuman di sini..."
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                ></textarea>
            </div>

            {{-- Expiry Date --}}
            <div x-data="{ hasExpiry: false }">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Tanggal Kedaluwarsa
                </label>
                <div class="mt-1.5 flex items-center gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                        <input
                            type="checkbox"
                            x-model="hasExpiry"
                            @change="if (!hasExpiry) $el.closest('div[x-data]').querySelector('input[name=expires_at]').value = ''"
                            class="rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                        >
                        <span>Aktifkan tanggal berakhir otomatis</span>
                    </label>
                </div>
                <div x-show="hasExpiry" x-cloak class="mt-2">
                    <input
                        type="datetime-local"
                        name="expires_at"
                        :min="new Date().toISOString().slice(0, 16)"
                        @click="$el.showPicker()"
                        class="w-full cursor-pointer rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                    <p class="mt-1 text-[11px] text-gray-400">
                        Pengumuman akan otomatis masuk riwayat setelah tanggal ini.
                    </p>
                </div>
            </div>

            {{-- Multiple Image Upload --}}
            <div
                x-data="{
                    files: [],
                    previews: [],
                    addFiles(e) {
                        const newFiles = Array.from(e.target.files);
                        newFiles.forEach(f => {
                            this.files.push(f);
                            const reader = new FileReader();
                            reader.onload = ev => this.previews.push({ name: f.name, src: ev.target.result });
                            reader.readAsDataURL(f);
                        });
                        e.target.value = '';
                    },
                    removeFile(index) {
                        this.files.splice(index, 1);
                        this.previews.splice(index, 1);
                    }
                }"
            >
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Lampiran Foto (Opsional, maks. 10)
                </label>

                {{-- Drop Zone --}}
                <div
                    class="mt-1.5 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-4 text-center cursor-pointer hover:border-brand-400 hover:bg-brand-50/30 dark:border-gray-700 dark:bg-white/[0.02] dark:hover:border-brand-500/50 transition"
                    @click="$refs.fileInput.click()"
                    @dragover.prevent
                    @drop.prevent="addFiles({ target: { files: $event.dataTransfer.files } })"
                >
                    <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-1.5 text-xs font-medium text-gray-600 dark:text-gray-400">
                        Klik atau seret foto ke sini
                    </p>
                    <p class="text-[11px] text-gray-400">JPG, PNG, WEBP — maks. 2MB per foto</p>
                </div>

                {{-- Hidden real input — multiple --}}
                <input
                    type="file"
                    name="images[]"
                    multiple
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="sr-only"
                    x-ref="fileInput"
                    @change="addFiles($event)"
                />

                {{-- Preview Grid --}}
                <div x-show="previews.length > 0" class="mt-3 grid grid-cols-4 gap-2">
                    <template x-for="(p, i) in previews" :key="i">
                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 aspect-square">
                            <img :src="p.src" :alt="p.name" class="h-full w-full object-cover" />
                            <button
                                type="button"
                                @click.stop="removeFile(i)"
                                class="absolute inset-0 flex items-center justify-center bg-gray-900/50 opacity-0 group-hover:opacity-100 transition"
                            >
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <p x-show="previews.length > 0" class="mt-1 text-[11px] text-gray-400" x-text="previews.length + ' foto dipilih'"></p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    @click="isCreateModalOpen = false"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 active:scale-95 transition"
                >
                    Publikasikan Pengumuman
                </button>
            </div>
        </form>
    </div>
</div>
