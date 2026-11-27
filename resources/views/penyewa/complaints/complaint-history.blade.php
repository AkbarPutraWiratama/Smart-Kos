{{-- Riwayat Aduan Fasilitas Saya --}}
<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="mb-5 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Riwayat Aduan Saya</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Pantau progres penanganan aduan dan catatan perbaikan dari petugas kos.
            </p>
        </div>
        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            {{ $complaints instanceof \Illuminate\Pagination\LengthAwarePaginator ? $complaints->total() : $complaints->count() }} Total
        </span>
    </div>

    @if ($complaints->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Belum Ada Aduan</p>
            <p class="mt-1 text-xs text-gray-400 max-w-sm">
                Jika Anda mengalami kendala fasilitas atau kerusakan kamar, silakan gunakan formulir di sebelah kiri untuk melaporkannya.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($complaints as $complaint)
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 transition hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.01] dark:hover:border-gray-700">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white/90 break-words">
                                    {{ $complaint->title }}
                                </h4>
                                @if ($complaint->highlighted)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-[10px] font-semibold text-error-600 dark:bg-error-500/15 dark:text-error-400">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        Prioritas Admin
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-[11px] text-gray-400">
                                Dilaporkan: {{ $complaint->submitted_at ? $complaint->submitted_at->translatedFormat('d F Y, H:i') : $complaint->created_at->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>

                        {{-- Status Badge (Compact & Modern) --}}
                        <div>
                            @if ($complaint->status === 'resolved')
                                <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-[11px] font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                    Selesai
                                </span>
                            @elseif ($complaint->status === 'in_progress')
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-light-50 px-2 py-0.5 text-[11px] font-semibold text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-light-500 animate-pulse"></span>
                                    Dalam Perbaikan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-[11px] font-semibold text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                    Menunggu
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Deskripsi Aduan --}}
                    <p class="mt-3 text-xs leading-relaxed text-gray-600 dark:text-gray-300">
                        {{ $complaint->description }}
                    </p>

                    {{-- Foto Lampiran Aduan dengan Preview Visual & Lightbox --}}
                    @if ($complaint->image_path)
                        <div class="mt-3.5" x-data="{ lightboxOpen: false }">
                            <div class="flex items-center gap-3">
                                <div class="group relative cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-theme-xs transition hover:border-brand-500 dark:border-gray-700 dark:bg-gray-800"
                                     @click="lightboxOpen = true">
                                    <img src="{{ Storage::url($complaint->image_path) }}" alt="Foto Bukti Aduan"
                                         class="h-16 w-24 object-cover transition duration-300 group-hover:scale-105">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                        <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"/>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                            <line x1="11" y1="8" x2="11" y2="14"/>
                                            <line x1="8" y1="11" x2="14" y2="11"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="text-xs">
                                    <p class="font-semibold text-gray-700 dark:text-gray-300">Foto Bukti Kerusakan</p>
                                    <button type="button" @click="lightboxOpen = true" class="mt-0.5 inline-flex items-center gap-1 text-[11px] font-medium text-brand-600 hover:underline dark:text-brand-400">
                                        <span>Lihat ukuran penuh</span>
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                            <polyline points="15 3 21 3 21 9"/>
                                            <line x1="10" y1="14" x2="21" y2="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Lightbox Modal (No blur, clean pop up animation) --}}
                            <div x-show="lightboxOpen"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="fixed inset-0 z-9999 flex items-center justify-center bg-black/80 p-4"
                                 @click="lightboxOpen = false"
                                 @keydown.escape.window="lightboxOpen = false"
                                 x-cloak>
                                <div class="relative max-h-[90vh] max-w-3xl overflow-hidden rounded-2xl bg-black/90 p-2" @click.stop>
                                    <button type="button" @click="lightboxOpen = false"
                                            class="absolute end-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/90">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                    <img src="{{ Storage::url($complaint->image_path) }}" alt="Foto Bukti Aduan"
                                         class="max-h-[85vh] w-auto max-w-full rounded-xl object-contain mx-auto">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Catatan / Tindakan Petugas (Complaint Actions) --}}
                    @if ($complaint->actions && $complaint->actions->isNotEmpty())
                        <div class="mt-4 border-t border-gray-200/60 pt-3 dark:border-gray-800">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Tindakan Petugas ({{ $complaint->actions->count() }}):
                            </p>
                            <div class="mt-2 space-y-2">
                                @foreach ($complaint->actions as $action)
                                    <div class="rounded-lg border border-gray-200 bg-white p-2.5 text-xs dark:border-gray-700 dark:bg-gray-900">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-semibold text-gray-700 dark:text-gray-200">
                                                {{ $action->staff?->name ?? 'Petugas Kos' }}
                                            </span>
                                            <span class="text-[10px] text-gray-400">
                                                {{ $action->created_at ? $action->created_at->translatedFormat('d M Y, H:i') : '' }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-gray-600 dark:text-gray-300">
                                            {{ $action->action_description }}
                                        </p>
                                        @if ($action->image_path)
                                            <a href="{{ Storage::url($action->image_path) }}" target="_blank" rel="noopener noreferrer"
                                               class="mt-1 inline-flex items-center gap-1 text-[11px] text-brand-600 hover:underline dark:text-brand-400">
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                                Foto Tindakan
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination Links --}}
        @if ($complaints instanceof \Illuminate\Pagination\LengthAwarePaginator && $complaints->hasPages())
            <div class="mt-5">
                {{ $complaints->links() }}
            </div>
        @endif
    @endif
</div>
