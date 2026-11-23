{{-- Form Pengiriman Aduan Fasilitas --}}
<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Kirim Aduan Baru</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Laporkan kerusakan kamar atau fasilitas kos untuk ditindaklanjuti oleh petugas.
            </p>
        </div>
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400" title="Batas pengiriman 1 kali setiap 10 menit">
            <svg class="h-3.5 w-3.5 text-warning-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            Limit 1x / 10 Mnt
        </span>
    </div>

    {{-- Rate Limit Warning Banner --}}
    @if ($rateLimited)
        <div class="mb-5 rounded-xl border border-warning-200 bg-warning-50 p-4 text-xs text-warning-800 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 shrink-0 text-warning-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div>
                    <p class="font-semibold">Batas Pengiriman Aktif</p>
                    <p class="mt-0.5">
                        Anda baru saja mengirimkan aduan. Untuk mencegah duplikasi, pengiriman aduan dibatasi 1 kali setiap 10 menit.
                        Silakan tunggu sekitar <strong class="underline">{{ $remainingMinutes }} menit lagi</strong> sebelum mengirim aduan baru.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('penyewa.complaints.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- Judul Aduan --}}
        <div>
            <label for="title" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                Judul Aduan <span class="text-error-500">*</span>
            </label>
            <input
                type="text"
                name="title"
                id="title"
                value="{{ old('title') }}"
                placeholder="Contoh: Keran Air Kamar Mandi Bocor / AC Tidak Dingin"
                required
                maxlength="255"
                @disabled($rateLimited)
                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 @error('title') border-error-500 @enderror"
            >
            @error('title')
                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Deskripsi Keluhan --}}
        <div>
            <label for="description" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                Deskripsi Kerusakan / Masalah <span class="text-error-500">*</span>
            </label>
            <textarea
                name="description"
                id="description"
                rows="4"
                placeholder="Jelaskan secara detail masalah yang dialami, misalnya sejak kapan terjadi, bagian mana yang rusak, atau hal lain yang perlu diketahui petugas..."
                required
                @disabled($rateLimited)
                class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-800 outline-none transition focus:border-brand-500 focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 @error('description') border-error-500 @enderror"
            >{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Upload Foto Bukti (Opsional) --}}
        <div>
            <label for="image" class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300">
                Foto Bukti Kerusakan <span class="text-gray-400 font-normal">(Opsional, Maks. 4MB)</span>
            </label>
            <div class="relative">
                <input
                    type="file"
                    name="image"
                    id="image"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    @disabled($rateLimited)
                    class="block w-full text-xs text-gray-500 file:me-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200 disabled:cursor-not-allowed dark:file:bg-gray-800 dark:file:text-gray-300 dark:hover:file:bg-gray-700"
                >
            </div>
            <p class="mt-1 text-[11px] text-gray-400">Format yang didukung: JPG, JPEG, PNG, WEBP.</p>
            @error('image')
                <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tombol Kirim --}}
        <div class="pt-2">
            <button
                type="submit"
                @disabled($rateLimited)
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                <span>{{ $rateLimited ? 'Tunggu Cooldown Selesai' : 'Kirim Aduan Sekarang' }}</span>
            </button>
        </div>
    </form>
</div>
