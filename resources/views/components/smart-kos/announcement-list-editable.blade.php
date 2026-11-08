{{--
    Announcement List Section — Shared partial for Staff & Admin dashboards.

    Required variables:
      $announcements   – Collection of published Post models (with targets.location, targets.room, author)
      $locations        – Collection of active Location models (for edit target dropdown)
      $allRooms         – Collection of active Room models with location (for edit target dropdown)
      $updateRouteName  – Named route base for PUT update  (e.g. 'admin.posts.update' / 'staff.posts.update')
      $destroyRouteName – Named route base for DELETE      (e.g. 'admin.posts.destroy' / 'staff.posts.destroy')
--}}

{{-- Alpine x-data is expected from the parent — this partial uses:
       isCreateModalOpen, isEditModalOpen, editingPost,
       editTitle, editContent, editTargetType, editLocationId, editRoomId,
       targetType, selectedLocationId, allRooms, filteredRooms
     All must be declared in the parent x-data block. --}}

{{-- ── Announcements List (rectangular rows, same as tenant) ── --}}
<div class="mt-5 divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($announcements as $post)
        @php
            $primaryTarget = $post->targets->first();
            $targetLabel = 'Semua Lokasi';
            $targetBadgeClass = 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400';

            if ($primaryTarget?->target_type === 'location' && $primaryTarget->location) {
                $targetLabel = $primaryTarget->location->name;
                $targetBadgeClass = 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400';
            } elseif ($primaryTarget?->target_type === 'room' && $primaryTarget->room) {
                $targetLabel = 'Kamar ' . $primaryTarget->room->room_number;
                $targetBadgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400';
            }

            $postDate = $post->published_at
                ? $post->published_at->translatedFormat('d M Y')
                : $post->created_at->translatedFormat('d M Y');

            $postDataJson = json_encode([
                'id'          => $post->id,
                'title'       => $post->title,
                'content'     => $post->content,
                'target_type' => $primaryTarget?->target_type ?? 'all',
                'location_id' => $primaryTarget?->location_id,
                'room_id'     => $primaryTarget?->room_id,
                'author'      => $post->author?->name ?? 'Pengelola',
                'date'        => $postDate,
                'target_label'=> $targetLabel,
                'expires_at'  => $post->expires_at?->format('Y-m-d\TH:i') ?? '',
                'update_url'  => route($updateRouteName, $post),
                'destroy_url' => route($destroyRouteName, $post),
            ]);
        @endphp

        {{-- Clickable row — opens edit modal --}}
        <div
            @click="editingPost = {{ $postDataJson }};
                    editTitle = editingPost.title;
                    editContent = editingPost.content;
                    editTargetType = editingPost.target_type;
                    editLocationId = editingPost.location_id ? String(editingPost.location_id) : '';
                    editRoomId = editingPost.room_id ? String(editingPost.room_id) : '';
                    editExpiresAt = editingPost.expires_at || '';
                    editHasExpiry = !!editingPost.expires_at;
                    isEditModalOpen = true;"
            class="group -mx-2 flex cursor-pointer flex-col gap-2 rounded-xl px-3 py-4 transition hover:bg-gray-50 active:bg-gray-100 dark:hover:bg-white/[0.03] dark:active:bg-white/[0.05]"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 min-w-0">
                    <h4 class="font-bold text-sm text-gray-800 group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400 transition truncate">
                        {{ $post->title }}
                    </h4>
                    <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-semibold {{ $targetBadgeClass }}">
                        {{ $targetLabel }}
                    </span>
                    @if ($post->expires_at)
                        <span class="shrink-0 inline-flex items-center gap-1 rounded px-2 py-0.5 text-[10px] font-semibold bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Berakhir {{ $post->expires_at->translatedFormat('d M Y') }}
                        </span>
                    @endif
                </div>
                <span class="shrink-0 text-xs text-gray-400">{{ $postDate }}</span>
            </div>

            <p class="line-clamp-2 text-xs text-gray-600 dark:text-gray-300">
                {{ $post->content }}
            </p>

            @if ($post->images->count() > 0)
                <div class="flex gap-1.5 mt-0.5">
                    @foreach ($post->images->take(4) as $img)
                        <img
                            src="{{ Storage::url($img->path) }}"
                            alt="foto"
                            class="h-10 w-10 rounded-lg object-cover border border-gray-100 dark:border-gray-800"
                        />
                    @endforeach
                    @if ($post->images->count() > 4)
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            +{{ $post->images->count() - 4 }}
                        </span>
                    @endif
                </div>
            @endif

            <div class="flex items-center justify-between text-[11px] text-gray-400 pt-0.5">
                <span>
                    Oleh: <strong class="font-medium text-gray-600 dark:text-gray-300">{{ $post->author?->name ?? 'Pengelola' }}</strong>
                </span>
                <span class="inline-flex items-center gap-1 text-brand-500 font-medium group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 transition">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Klik untuk edit
                </span>
            </div>
        </div>
    @empty
        <div class="py-10 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                Belum ada pengumuman yang diterbitkan.
            </p>
            <button
                type="button"
                @click="isCreateModalOpen = true"
                class="mt-2 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400"
            >
                Buat pengumuman pertama &rarr;
            </button>
        </div>
    @endforelse
</div>

{{-- ── Edit / View Modal (staff & admin) ── --}}
<div
    x-show="isEditModalOpen"
    x-cloak
    class="fixed inset-0 z-99999 flex items-start justify-center overflow-y-auto bg-gray-900/60 p-4 pt-10 backdrop-blur-sm"
    @keydown.escape.window="isEditModalOpen = false"
>
    {{-- Delete Confirm Sub-Modal --}}
    <div
        x-show="isDeleteConfirmOpen"
        x-cloak
        class="fixed inset-0 z-[999999] flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4"
        @keydown.escape.window="isDeleteConfirmOpen = false"
    >
        <div
            @click.outside="isDeleteConfirmOpen = false"
            class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex flex-col items-center text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </span>
                <h4 class="mt-3 text-sm font-bold text-gray-800 dark:text-white/90">Hapus Pengumuman?</h4>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    Pengumuman akan dipindahkan ke riwayat. Anda masih bisa melihatnya di halaman Riwayat.
                </p>
            </div>
            <div class="mt-5 flex items-center justify-center gap-3">
                <button
                    type="button"
                    @click="isDeleteConfirmOpen = false"
                    class="rounded-xl border border-gray-200 px-5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="isDeleteConfirmOpen = false; isEditModalOpen = false; document.getElementById('delete-post-form-' + editingPost.id)?.submit()"
                    class="rounded-xl bg-error-500 px-5 py-2 text-xs font-semibold text-white hover:bg-error-600 active:scale-95 transition"
                >
                    Ya, Pindahkan ke Riwayat
                </button>
            </div>
        </div>
    </div>

    <div
        @click.outside="isEditModalOpen = false"
        class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900 mb-10"
        x-show="isEditModalOpen"
    >
        {{-- Modal Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Edit Pengumuman</h3>
                    <p class="text-[11px] text-gray-400" x-text="'Oleh: ' + (editingPost?.author ?? '')"></p>
                </div>
            </div>
            <button
                type="button"
                @click="isEditModalOpen = false"
                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Edit Form --}}
        <form
            :action="editingPost?.update_url"
            method="POST"
            enctype="multipart/form-data"
            class="px-6 py-5 space-y-4"
            x-ref="editForm"
        >
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Judul Pengumuman <span class="text-error-500">*</span>
                </label>
                <input
                    type="text"
                    name="title"
                    x-model="editTitle"
                    required
                    maxlength="255"
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
                           :class="editTargetType === 'all' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="all" x-model="editTargetType" class="sr-only">
                        <span>Semua Lokasi</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-2.5 text-xs transition"
                           :class="editTargetType === 'location' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="location" x-model="editTargetType" class="sr-only">
                        <span>Satu Lokasi</span>
                    </label>
                    <label class="flex cursor-pointer items-center justify-center rounded-xl border p-2.5 text-xs transition"
                           :class="editTargetType === 'room' ? 'border-brand-500 bg-brand-50 text-brand-700 font-semibold dark:bg-brand-500/15 dark:text-brand-400' : 'border-gray-200 hover:bg-gray-50 text-gray-600 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <input type="radio" name="target_type" value="room" x-model="editTargetType" class="sr-only">
                        <span>Kamar Tertentu</span>
                    </label>
                </div>
            </div>

            {{-- Location Select --}}
            <div x-show="editTargetType === 'location' || editTargetType === 'room'" x-cloak>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Pilih Lokasi Kos <span class="text-error-500">*</span>
                </label>
                <select
                    name="location_id"
                    x-model="editLocationId"
                    :required="editTargetType === 'location' || editTargetType === 'room'"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">-- Pilih Lokasi Kos --</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Room Select --}}
            <div x-show="editTargetType === 'room'" x-cloak>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Pilih Kamar <span class="text-error-500">*</span>
                </label>
                <select
                    name="room_id"
                    x-model="editRoomId"
                    :required="editTargetType === 'room'"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                >
                    <option value="">-- Pilih Kamar --</option>
                    <template x-for="r in editFilteredRooms" :key="r.id">
                        <option :value="String(r.id)" x-text="r.label"></option>
                    </template>
                </select>
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
                    x-model="editContent"
                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                ></textarea>
            </div>

            {{-- Expiry Date --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                    Tanggal Kedaluwarsa
                </label>
                <div class="mt-1.5 flex items-center gap-3">
                    <label class="flex cursor-pointer items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                        <input
                            type="checkbox"
                            x-model="editHasExpiry"
                            @change="if (!editHasExpiry) editExpiresAt = ''"
                            class="rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                        >
                        <span>Aktifkan tanggal berakhir otomatis</span>
                    </label>
                </div>
                <div x-show="editHasExpiry" x-cloak class="mt-2">
                    <input
                        type="datetime-local"
                        name="expires_at"
                        x-model="editExpiresAt"
                        @click="$el.showPicker()"
                        class="w-full cursor-pointer rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs text-gray-800 shadow-theme-xs focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    />
                    <p class="mt-1 text-[11px] text-gray-400">
                        Pengumuman akan otomatis masuk riwayat setelah tanggal ini.
                    </p>
                </div>
            </div>

            {{-- Add More Images --}}
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
                    Tambah Foto (Opsional)
                </label>
                <div
                    class="mt-1.5 flex items-center gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/50 px-4 py-3 cursor-pointer hover:border-brand-400 dark:border-gray-700 dark:bg-white/[0.02] transition"
                    @click="$refs.editFileInput.click()"
                >
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Klik untuk pilih foto baru</span>
                </div>
                <input
                    type="file"
                    name="images[]"
                    multiple
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="sr-only"
                    x-ref="editFileInput"
                    @change="addFiles($event)"
                />
                <div x-show="previews.length > 0" class="mt-2 grid grid-cols-4 gap-2">
                    <template x-for="(p, i) in previews" :key="i">
                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 aspect-square">
                            <img :src="p.src" :alt="p.name" class="h-full w-full object-cover" />
                            <button
                                type="button"
                                @click.stop="removeFile(i)"
                                class="absolute inset-0 flex items-center justify-center bg-gray-900/50 opacity-0 group-hover:opacity-100 transition"
                            >
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-between gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                {{-- Delete button — opens Alpine confirm modal --}}
                <button
                    type="button"
                    @click="isDeleteConfirmOpen = true"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-error-200 px-4 py-2 text-xs font-semibold text-error-600 hover:bg-error-50 active:scale-95 transition dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="isEditModalOpen = false"
                        class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 active:scale-95 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 active:scale-95 transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Hidden delete forms (one per post) for the confirm-delete action --}}
@foreach ($announcements as $post)
    <form
        id="delete-post-form-{{ $post->id }}"
        action="{{ route($destroyRouteName, $post) }}"
        method="POST"
        class="hidden"
    >
        @csrf
        @method('DELETE')
    </form>
@endforeach
