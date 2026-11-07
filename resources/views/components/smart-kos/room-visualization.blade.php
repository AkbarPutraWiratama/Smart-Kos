@props([
    'roomsByFloor',
    'selectedLocation' => null,
    'role' => 'admin',
])

<div class="space-y-6">
    <!-- Header, Legend & Action Button -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="min-w-0">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                Denah Visualisasi Kamar: {{ $selectedLocation->name ?? 'Semua Lokasi' }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Tata letak denah kamar per lantai. Pilih kamar untuk melihat rincian{{ $role === 'admin' ? ' atau menempatkan penyewa' : '' }}.
            </p>
        </div>

        <!-- Color Coded Legend (Agak ke tengah) -->
        <div class="flex flex-wrap items-center gap-4 text-xs font-medium lg:mx-auto">
            <div class="flex items-center gap-2">
                <span class="h-3.5 w-3.5 rounded bg-success-500 shadow-sm"></span>
                <span class="text-gray-700 dark:text-gray-300">Kosong / Tersedia</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-3.5 w-3.5 rounded bg-error-500 shadow-sm"></span>
                <span class="text-gray-700 dark:text-gray-300">Terisi</span>
            </div>
        </div>

        <!-- Tombol Tugaskan Kamar (Di sebelah kanan keterangan warna) -->
        @if ($role === 'admin')
            <div class="flex items-center justify-start lg:justify-end shrink-0">
                <button
                    type="button"
                    @click="isAssignModalOpen = true; isRoomLocked = false; lockedRoomId = ''; selectedTenantId = null"
                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-4 text-xs sm:text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 active:scale-95 transition"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    + Tugaskan Kamar
                </button>
            </div>
        @endif
    </div>

    <!-- Floor-by-Floor Grid Display -->
    <div class="space-y-6">
        @forelse ($roomsByFloor->sortByDesc(fn($_, $floor) => $floor) as $floor => $floorRooms)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 font-bold text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $floor }}
                        </span>
                        <h4 class="font-bold text-sm text-gray-800 dark:text-white/90">
                            Lantai {{ $floor }}
                        </h4>
                    </div>
                    <span class="text-xs text-gray-400">
                        {{ $floorRooms->count() }} Kamar &bull; {{ $floorRooms->where(fn($r) => $r->isOccupied())->count() }} Terisi
                    </span>
                </div>

                <!-- Room Cards Grid -->
                <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5">
                    @foreach ($floorRooms as $room)
                        @php
                            $assignment = $room->activeAssignment;
                            $isOccupied = (bool) $assignment;
                        @endphp
                        <div
                            @if ($role === 'admin')
                                @if ($isOccupied)
                                    @click="currentAssignment = {{ json_encode($assignment) }}; isManageAssignmentModalOpen = true"
                                @else
                                    @click="isAssignModalOpen = true; isRoomLocked = true; lockedRoomId = '{{ $room->id }}'; selectedTenantId = null; $nextTick(() => { const el = document.getElementById('assign_room_id'); if (el) el.value = '{{ $room->id }}'; })"
                                @endif
                            @endif
                            class="group rounded-xl border p-3.5 transition-all hover:-translate-y-0.5 hover:shadow-theme-sm active:scale-95 text-start {{ $role === 'admin' ? 'cursor-pointer' : '' }} {{ $isOccupied ? 'border-error-200 bg-error-50/40 hover:border-error-500 dark:border-error-500/20 dark:bg-error-500/5' : 'border-success-200 bg-success-50/40 hover:border-success-500 dark:border-success-500/20 dark:bg-success-500/5' }}"
                        >
                            <div class="flex items-start justify-between gap-1.5">
                                <span class="font-bold text-base {{ $isOccupied ? 'text-error-700 dark:text-error-400' : 'text-success-700 dark:text-success-400' }}">
                                    {{ $room->room_number }}
                                </span>
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $isOccupied ? 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400' : 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' }}">
                                    {{ $isOccupied ? 'Terisi' : 'Kosong' }}
                                </span>
                            </div>

                            <div class="mt-2 text-xs">
                                @if ($isOccupied)
                                    <p class="truncate font-semibold text-gray-800 dark:text-white/90">
                                        {{ $assignment->tenantProfile?->user?->name ?? 'Penyewa' }}
                                    </p>
                                    <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $assignment->tenantProfile?->phone ?? 'Tanpa HP' }}
                                    </p>
                                    @if ($role === 'admin')
                                        <p class="mt-1.5 text-[10px] font-medium text-error-600 dark:text-error-400">
                                            Kelola hunian &rarr;
                                        </p>
                                    @else
                                        <p class="mt-1.5 text-[10px] font-medium text-gray-400">
                                            Penghuni Aktif
                                        </p>
                                    @endif
                                @else
                                    @if ($role === 'admin')
                                        <p class="font-medium text-gray-600 dark:text-gray-400">
                                            Rp {{ number_format($room->rent_amount, 0, ',', '.') }}
                                        </p>
                                        <p class="mt-2 text-[10px] font-medium text-success-600 dark:text-success-400">
                                            + Tugaskan &rarr;
                                        </p>
                                    @else
                                        <p class="font-medium text-success-600 dark:text-success-400">
                                            Kamar Tersedia
                                        </p>
                                        <p class="mt-2 text-[10px] text-gray-400">
                                            Siap dihuni
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                Belum ada data kamar pada lokasi kos ini.
            </div>
        @endforelse
    </div>
</div>

