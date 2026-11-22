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
                <span class="h-4 w-4 rounded border-2 border-gray-400 bg-white shadow-sm dark:border-gray-300 dark:bg-gray-900"></span>
                <span class="text-gray-700 dark:text-gray-300">Kosong / Tersedia</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-gray-500 bg-gray-400 shadow-sm dark:border-gray-500 dark:bg-gray-600"></span>
                <span class="text-gray-700 dark:text-gray-300">Terisi</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-warning-700 bg-warning-400 shadow-sm dark:border-warning-300 dark:bg-warning-500"></span>
                <span class="text-gray-700 dark:text-gray-300">Perlu Verifikasi</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-4 w-4 rounded border-2 border-error-700 bg-error-400 shadow-sm dark:border-error-300 dark:bg-error-500"></span>
                <span class="text-gray-700 dark:text-gray-300">Pembayaran Jatuh Tempo</span>
            </div>
        </div>

        <!-- Tombol Isi Kamar -->
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
                    Isi Kamar
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
                            $isPaid = $isOccupied && $assignment->isPaidForCurrentPeriod();
                            $needsVerification = $isOccupied && ! $isPaid && $assignment->has_pending_payment;
                            $isPaymentDue = $isOccupied && ! $isPaid && $assignment->getDaysUntilDue() <= 0;
                            $roomPaymentState = $needsVerification ? 'verification' : ($isPaymentDue ? 'due' : ($isOccupied ? 'occupied' : 'empty'));
                        @endphp
                        <div
                            @if ($role === 'admin')
                                @if ($isOccupied)
                                    @click="currentAssignment = {{ json_encode($assignment) }}; isManageAssignmentModalOpen = true"
                                @else
                                    @click="isAssignModalOpen = true; isRoomLocked = true; lockedRoomId = '{{ $room->id }}'; selectedTenantId = null; $nextTick(() => { const el = document.getElementById('assign_room_id'); if (el) el.value = '{{ $room->id }}'; })"
                                @endif
                            @endif
                            class="relative group rounded-xl border p-3.5 transition-all hover:-translate-y-0.5 hover:shadow-theme-sm active:scale-95 text-start {{ $role === 'admin' ? 'cursor-pointer' : '' }}
                                {{ $roomPaymentState === 'due' ? 'border-error-300 bg-error-100 dark:border-error-500/40 dark:bg-error-500/20' : '' }}
                                {{ $roomPaymentState === 'verification' ? 'border-warning-300 bg-warning-100 dark:border-warning-500/40 dark:bg-warning-500/20' : '' }}
                                {{ $roomPaymentState === 'occupied' ? 'border-gray-300 bg-gray-200 dark:border-gray-700 dark:bg-gray-700' : '' }}
                                {{ $roomPaymentState === 'empty' ? 'border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900' : '' }}
                                hover:border-brand-500 hover:shadow-theme-sm dark:hover:border-brand-400"
                        >
                            <div class="flex items-start justify-between gap-1.5">
                                <span class="font-bold text-base {{ $roomPaymentState === 'due' ? 'text-error-700 dark:text-error-400' : ($roomPaymentState === 'verification' ? 'text-warning-700 dark:text-warning-400' : ($isOccupied ? 'text-gray-700 dark:text-gray-200' : 'text-gray-800 dark:text-white/90')) }}">
                                    {{ $room->room_number }}
                                </span>
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider
                                    {{ $roomPaymentState === 'due' ? 'bg-error-200 text-error-700 dark:bg-error-500/30 dark:text-error-300' : '' }}
                                    {{ $roomPaymentState === 'verification' ? 'bg-warning-200 text-warning-700 dark:bg-warning-500/30 dark:text-warning-300' : '' }}
                                    {{ $roomPaymentState === 'occupied' ? 'bg-gray-300 text-gray-700 dark:bg-gray-600 dark:text-gray-200' : '' }}
                                    {{ $roomPaymentState === 'empty' ? 'border border-gray-200 bg-white text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300' : '' }}">
                                    {{ $roomPaymentState === 'due' ? 'Jatuh Tempo' : ($roomPaymentState === 'verification' ? 'Verifikasi' : ($isOccupied ? 'Terisi' : 'Kosong')) }}
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
                                        <p class="mt-1.5 text-[10px] font-medium text-gray-600 dark:text-gray-300">
                                            Kelola Kamar &rarr;
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
                                        <p class="mt-2 text-[10px] font-medium text-brand-600 dark:text-brand-400">
                                            Isi Kamar &rarr;
                                        </p>
                                    @else
                                        <p class="font-medium text-gray-600 dark:text-gray-300">
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

