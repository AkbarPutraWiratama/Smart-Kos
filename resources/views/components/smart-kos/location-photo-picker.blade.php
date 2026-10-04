@props([
    'photos' => [],
    'legacyPath' => null,
    'inputId' => 'location_photos',
    'slider' => false,
    'location' => null,
])

@php
    $storedImages = collect();
    if ($legacyPath) {
        $storedImages->push([
            'url' => asset('storage/' . $legacyPath),
            'deleteUrl' => $location ? route('admin.locations.legacy-photo.destroy', $location) : null,
        ]);
    }
    foreach ($photos as $photo) {
        $storedImages->push([
            'url' => asset('storage/' . $photo->path),
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
    <label for="{{ $inputId }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto lokasi</label>
    <input id="{{ $inputId }}" name="photos[]" type="file" accept="image/*" multiple @change="updatePreviews($event)" class="block w-full rounded-lg border border-gray-300 bg-transparent text-sm text-gray-600 file:me-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium dark:border-gray-700 dark:text-gray-300 dark:file:bg-gray-800" />
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih beberapa foto sekaligus, simpan, lalu ulangi untuk menambahkan foto lainnya tanpa menghapus foto sebelumnya. Maksimal 10 foto per sesi, masing-masing 4 MB.</p>

    @error('photos')
        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
    @enderror
    @error('photos.*')
        <p class="mt-1 text-sm text-error-500">{{ $message }}</p>
    @enderror

    @if ($storedImages->isNotEmpty())
        <div class="mt-4">
            <p class="mb-2 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Foto tersimpan</p>
            @if ($slider)
                <div x-data="{ currentPhoto: 0, storedImages: @js($storedImages->values()), deleteOpen: false }" class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <img :src="storedImages[currentPhoto].url" alt="Foto lokasi" class="h-64 w-full object-cover sm:h-72" />
                    <button type="button" @click="currentPhoto = (currentPhoto - 1 + storedImages.length) % storedImages.length" aria-label="Foto sebelumnya" class="absolute start-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-xl text-white hover:bg-black/80">&#8249;</button>
                    <button type="button" @click="currentPhoto = (currentPhoto + 1) % storedImages.length" aria-label="Foto berikutnya" class="absolute end-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-xl text-white hover:bg-black/80">&#8250;</button>
                    <span class="absolute bottom-3 start-1/2 -translate-x-1/2 rounded-full bg-black/60 px-2.5 py-1 text-xs text-white" x-text="`${currentPhoto + 1} / ${storedImages.length}`"></span>
                    <template x-if="storedImages[currentPhoto].deleteUrl">
                        <button type="button" @click="deleteOpen = true" class="absolute bottom-3 end-3 rounded-lg bg-error-500/90 px-3 py-1.5 text-xs font-medium text-white hover:bg-error-600">Hapus foto</button>
                    </template>

                    <template x-teleport="body">
                        <div x-show="deleteOpen" x-cloak @keydown.escape.window="deleteOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto bg-gray-900/60 p-5">
                            <div @click="deleteOpen = false" class="fixed inset-0"></div>
                            <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Konfirmasi hapus foto</h2>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Foto ini akan dihapus permanen dari lokasi.</p>
                                <form method="POST" :action="storedImages[currentPhoto].deleteUrl" class="mt-5 space-y-4">
                                    @csrf
                                    <input type="hidden" name="_method" value="DELETE" />
                                    <div>
                                        <label for="delete_photo_password_{{ $inputId }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password admin</label>
                                        <input id="delete_photo_password_{{ $inputId }}" name="password" type="password" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90" />
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" @click="deleteOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Batal</button>
                                        <button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Ya, hapus foto</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($storedImages as $image)
                        <img src="{{ $image['url'] }}" alt="Foto lokasi" class="h-24 w-full rounded-lg object-cover" />
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <div x-show="previews.length" x-cloak class="mt-4">
        <p class="mb-2 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Preview foto baru</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <template x-for="preview in previews" :key="preview">
                <img :src="preview" alt="Preview foto baru" class="h-24 w-full rounded-lg object-cover" />
            </template>
        </div>
    </div>
</div>