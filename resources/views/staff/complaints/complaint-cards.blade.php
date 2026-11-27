{{-- Complaint Cards Grid for Staff --}}
<div>
    @if ($complaints->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-white py-16 text-center shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Tidak Ada Aduan Ditemukan</p>
            <p class="mt-1 text-xs text-gray-400 max-w-sm">
                Belum ada aduan fasilitas yang cocok dengan filter yang Anda tentukan.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($complaints as $complaint)
                @php
                    $tenant = $complaint->tenantProfile?->user;
                    $room = $complaint->tenantProfile?->roomAssignments()->where('status', 'active')->first()?->room
                        ?? $complaint->tenantProfile?->roomAssignments()->first()?->room;
                    $location = $room?->location;
                    $actionPayload = $complaint->actions->map(function ($a) {
                        return [
                            'staff'  => $a->staff?->name ?? 'Petugas Kos',
                            'desc'   => $a->action_description,
                            'status' => $a->status,
                            'date'   => $a->created_at ? $a->created_at->translatedFormat('d M Y, H:i') : '',
                            'img'    => $a->image_path ? \Illuminate\Support\Facades\Storage::url($a->image_path) : '',
                        ];
                    })->values();
                @endphp

                <article class="relative flex flex-col justify-between rounded-2xl border bg-white p-4 sm:p-5 shadow-theme-xs transition hover:shadow-theme-sm dark:bg-white/[0.03] {{ $complaint->highlighted ? 'border-error-400 ring-2 ring-error-400/20 shadow-theme-sm dark:border-error-500/50 dark:ring-error-500/20' : 'border-gray-200 dark:border-gray-800' }}">

                    @if ($complaint->highlighted)
                        <div class="absolute -top-px inset-x-0 h-1.5 rounded-t-2xl bg-gradient-to-r from-error-500 via-warning-500 to-error-500"></div>
                    @endif

                    <div>
                        {{-- Top Badges: Highlight + Status --}}
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                @if ($complaint->highlighted)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-error-100 px-2.5 py-0.5 text-[11px] font-bold text-error-700 shadow-theme-xs dark:bg-error-500/20 dark:text-error-300">
                                        <svg class="h-3 w-3 fill-current text-error-600 dark:text-error-400" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        Prioritas Admin
                                    </span>
                                @else
                                    <span class="text-[11px] text-gray-400">
                                        {{ $complaint->submitted_at ? $complaint->submitted_at->translatedFormat('d M Y, H:i') : $complaint->created_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                @endif
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
                                        Belum
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Room & Location Info --}}
                        <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1 font-semibold text-gray-800 dark:text-white/90">
                                <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Kamar #{{ $room?->room_number ?? '—' }}
                            </span>
                            <span>&bull;</span>
                            <span class="truncate">{{ $location?->name ?? 'Lokasi Kos' }}</span>
                        </div>

                        {{-- Pelapor --}}
                        <p class="mt-1 text-xs text-gray-400">
                            Pelapor: <strong class="font-medium text-gray-700 dark:text-gray-300">{{ $tenant?->name ?? 'Penyewa' }}</strong>
                        </p>

                        {{-- Judul Aduan --}}
                        <h4 class="mt-3 text-sm font-bold text-gray-800 dark:text-white/90 line-clamp-2">
                            {{ $complaint->title }}
                        </h4>

                        {{-- Deskripsi --}}
                        <p class="mt-1.5 text-xs text-gray-600 dark:text-gray-300 line-clamp-3">
                            {{ $complaint->description }}
                        </p>

                        {{-- Attachment Photo with Visual Preview & Lightbox --}}
                        @if ($complaint->image_path)
                            <div class="mt-3.5" x-data="{ imgLightbox: false }">
                                <div class="group relative cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-theme-xs transition hover:border-brand-500 dark:border-gray-700 dark:bg-gray-800"
                                     @click="imgLightbox = true">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($complaint->image_path) }}" alt="Foto Aduan"
                                         class="h-28 w-full object-cover transition duration-300 group-hover:scale-105">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-black/60 px-2.5 py-1 text-xs font-semibold text-white">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="11" cy="11" r="8"/>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                                <line x1="11" y1="8" x2="11" y2="14"/>
                                                <line x1="8" y1="11" x2="14" y2="11"/>
                                            </svg>
                                            Perbesar Foto
                                        </span>
                                    </div>
                                </div>

                                {{-- Lightbox Modal (No blur, clean pop up animation) --}}
                                <div x-show="imgLightbox"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="fixed inset-0 z-9999 flex items-center justify-center bg-black/80 p-4"
                                     @click="imgLightbox = false"
                                     @keydown.escape.window="imgLightbox = false"
                                     x-cloak>
                                    <div class="relative max-h-[90vh] max-w-3xl overflow-hidden rounded-2xl bg-black/90 p-2" @click.stop>
                                        <button type="button" @click="imgLightbox = false"
                                                class="absolute end-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/90">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </button>
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($complaint->image_path) }}" alt="Foto Aduan"
                                             class="max-h-[85vh] w-auto max-w-full rounded-xl object-contain mx-auto">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Card Footer --}}
                    <div class="mt-5 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] text-gray-400">
                                {{ $complaint->actions->count() }} tindakan tercatat
                            </span>

                            {{-- Click to open Popup modal --}}
                            <button
                                type="button"
                                @click="$dispatch('open-complaint-modal', {
                                    id: {{ $complaint->id }},
                                    title: @js($complaint->title),
                                    description: @js($complaint->description),
                                    tenantName: @js($tenant?->name ?? 'Penyewa'),
                                    tenantPhone: @js($complaint->tenantProfile?->phone ?? '-'),
                                    roomNumber: @js($room?->room_number ?? '-'),
                                    locationName: @js($location?->name ?? '-'),
                                    status: @js($complaint->status),
                                    submittedAt: @js($complaint->submitted_at ? $complaint->submitted_at->translatedFormat('d F Y, H:i') : $complaint->created_at->translatedFormat('d F Y, H:i')),
                                    imagePath: @js($complaint->image_path ? \Illuminate\Support\Facades\Storage::url($complaint->image_path) : ''),
                                    actions: @js($actionPayload),
                                    postUrl: '{{ route('staff.complaints.actions.store', $complaint) }}'
                                })"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Tindak Lanjuti
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($complaints->hasPages())
            <div class="mt-6">
                {{ $complaints->links() }}
            </div>
        @endif
    @endif
</div>
