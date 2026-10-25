@extends('layouts.app')

@section('content')
<div x-data="{
    isAssignModalOpen: false,
    isChangeRoomModalOpen: false,
    isEndAssignmentModalOpen: false,
    isManageAssignmentModalOpen: false,
    currentAssignment: null,
    selectedTenantId: null
}" class="space-y-6">

    <x-common.page-breadcrumb pageTitle="Status Penyewa" />

    <!-- Filter by Location Bar -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center gap-3">
            <label for="location_select" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                Pilih Lokasi Kos:
            </label>
            <form method="GET" action="{{ route('admin.tenants.index') }}" class="inline-block">
                <input type="hidden" name="tab" value="{{ $statusTab }}" />
                <select
                    id="location_select"
                    name="location_id"
                    onchange="this.form.submit()"
                    class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm font-medium text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                >
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((string)$selectedLocationId === (string)$loc->id)>
                            {{ $loc->name }} ({{ $loc->rooms->count() }} Kamar)
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div>
            @if ($availableRooms->isNotEmpty() && $allUnassignedProfiles->isNotEmpty())
                <button
                    type="button"
                    @click="isAssignModalOpen = true; selectedTenantId = null"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
                >
                    + Tugaskan Kamar
                </button>
            @endif
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Kamar Aktif</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $metrics['total_rooms'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-success-600 dark:text-success-400">Kamar Terisi (Okupansi)</p>
            <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">
                {{ $metrics['occupied_rooms'] }} <span class="text-xs font-normal text-gray-400">({{ $metrics['occupancy_rate'] }}%)</span>
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-blue-600 dark:text-blue-400">Kamar Kosong</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $metrics['empty_rooms'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-warning-600 dark:text-warning-400">Penyewa Belum Ditugaskan</p>
            <p class="mt-2 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $metrics['unassigned_count'] }}</p>
        </div>
    </div>

    <!-- Navigation Tabs (Visualisasi Denah Kamar vs Penyewa di Kamar vs Belum Ditugaskan) -->
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a
                href="{{ route('admin.tenants.index', ['location_id' => $selectedLocationId, 'tab' => 'visualization']) }}"
                class="border-b-2 py-3 text-sm font-semibold transition {{ $statusTab === 'visualization' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                Visualisasi Denah Kamar
            </a>
            <a
                href="{{ route('admin.tenants.index', ['location_id' => $selectedLocationId, 'tab' => 'assigned']) }}"
                class="border-b-2 py-3 text-sm font-semibold transition {{ $statusTab === 'assigned' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                Penyewa di Kamar ({{ $assignedTenants->total() }})
            </a>
            <a
                href="{{ route('admin.tenants.index', ['location_id' => $selectedLocationId, 'tab' => 'unassigned']) }}"
                class="border-b-2 py-3 text-sm font-semibold transition {{ $statusTab === 'unassigned' ? 'border-brand-500 text-brand-500 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                Penyewa Belum Ada Kamar ({{ $unassignedTenants->total() }})
            </a>
        </nav>
    </div>

    @if ($statusTab === 'visualization')
        <!-- TAB 0: ROOM STATUS VISUALIZATION (PHASE 16) -->
        <x-smart-kos.room-visualization
            :roomsByFloor="$roomsByFloor"
            :selectedLocation="$selectedLocation"
            role="admin"
        />
    @elseif ($statusTab === 'assigned')
        <!-- TAB 1: ASSIGNED TENANTS -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
            <div class="px-6 mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Daftar Kamar Terisi & Penyewa Aktif
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Status kamar yang sedang dihuni di lokasi terpilih. Anda dapat memindahkan penyewa atau menyelesaikan sewa.
                </p>
            </div>

            <!-- Mobile View: Tap to Manage / Action Drawer -->
            <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
                @forelse ($assignedTenants as $assignment)
                    <div
                        @click="currentAssignment = {{ json_encode($assignment) }}; isManageAssignmentModalOpen = true"
                        class="p-4 space-y-2.5 cursor-pointer transition hover:bg-gray-50 active:bg-gray-100 dark:hover:bg-white/[0.03] dark:active:bg-white/[0.05]"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 font-bold text-sm text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                    {{ $assignment->room->room_number }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-800 text-theme-sm dark:text-white/90">
                                        {{ $assignment->tenantProfile?->user?->name ?? 'Penyewa' }}
                                    </p>
                                    <p class="truncate text-gray-500 text-theme-xs dark:text-gray-400">
                                        {{ $assignment->room->location->name ?? 'Kos' }} &bull; Lantai {{ $assignment->room->floor_name }}
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-1.5">
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 dark:bg-green-500/15 dark:text-green-500">
                                    Ditempati
                                </span>
                                <svg class="h-4 w-4 text-gray-400 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 pt-2 dark:border-gray-800">
                            <span>Sewa: Rp {{ number_format($assignment->rent_amount_snapshot, 0, ',', '.') }}/bln</span>
                            <span class="text-brand-500 font-medium">Ketuk untuk kelola &rarr;</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        Tidak ada kamar yang sedang terisi di lokasi ini.
                    </div>
                @endforelse
            </div>

            <!-- Desktop View Table: Click Row to Manage -->
            <div class="hidden sm:block max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="border-t border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Kamar
                            </th>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Penyewa
                            </th>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Kontak
                            </th>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Tarif Sewa
                            </th>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Tanggal Mulai
                            </th>
                            <th class="px-6 py-3.5 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse ($assignedTenants as $assignment)
                            <tr
                                @click="currentAssignment = {{ json_encode($assignment) }}; isManageAssignmentModalOpen = true"
                                class="group cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 font-bold text-sm text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                                            {{ $assignment->room->room_number }}
                                        </span>
                                        <div>
                                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                Kamar {{ $assignment->room->room_number }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Lantai {{ $assignment->room->floor_name }} &bull; {{ $assignment->room->location->name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800 text-theme-sm group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400 transition">
                                        {{ $assignment->tenantProfile?->user?->name ?? '-' }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ '@' . ($assignment->tenantProfile?->user?->username ?? '-') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-300">
                                    {{ $assignment->tenantProfile?->phone ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                    Rp {{ number_format($assignment->rent_amount_snapshot, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                    {{ $assignment->start_date ? $assignment->start_date->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <span class="inline-flex items-center text-xs font-medium text-brand-500 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 transition">
                                        Kelola Sewa &rarr;
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada kamar yang sedang terisi di lokasi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignedTenants->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-white/[0.05]">
                    {{ $assignedTenants->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- TAB 2: UNASSIGNED TENANTS -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
            <div class="px-6 mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Daftar Penyewa Belum Ditugaskan
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Akun penyewa aktif yang belum terdaftar di kamar manapun. Pilih kamar kosong untuk menempatkan mereka.
                </p>
            </div>

            <!-- Mobile View: Tap Card to Assign -->
            <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
                @forelse ($unassignedTenants as $profile)
                    <div
                        @click="isAssignModalOpen = true; selectedTenantId = {{ $profile->id }}"
                        class="p-4 space-y-2 cursor-pointer transition hover:bg-gray-50 active:bg-gray-100 dark:hover:bg-white/[0.03] dark:active:bg-white/[0.05]"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-warning-50 font-bold text-sm text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                    {{ strtoupper(substr($profile->user?->name ?? 'P', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-800 text-theme-sm dark:text-white/90">
                                        {{ $profile->user?->name ?? '-' }}
                                    </p>
                                    <p class="truncate text-gray-500 text-theme-xs dark:text-gray-400">
                                        {{ '@' . ($profile->user?->username ?? '-') }} &bull; {{ $profile->phone ?? 'Tanpa HP' }}
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-1.5">
                                <span class="rounded-full bg-warning-50 px-2 py-0.5 text-[11px] font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                    Belum ada kamar
                                </span>
                                <svg class="h-4 w-4 text-gray-400 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-500 pt-1">
                            <span>Terdaftar: {{ $profile->registered_at ? $profile->registered_at->format('d M Y') : '-' }}</span>
                            <span class="text-brand-500 font-medium">Ketuk untuk tugaskan &rarr;</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        Semua penyewa aktif telah memiliki kamar.
                    </div>
                @endforelse
            </div>

            <!-- Desktop View Table: Click Row to Assign -->
            <div class="hidden sm:block max-w-full overflow-x-auto">
                <table class="w-full">
                    <thead class="border-t border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Penyewa
                            </th>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Username
                            </th>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Nomor HP
                            </th>
                            <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                Terdaftar Sejak
                            </th>
                            <th class="px-6 py-3.5 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                        @forelse ($unassignedTenants as $profile)
                            <tr
                                @click="isAssignModalOpen = true; selectedTenantId = {{ $profile->id }}"
                                class="group cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning-50 font-medium text-sm text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                                            {{ strtoupper(substr($profile->user?->name ?? 'P', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800 text-theme-sm group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400 transition">
                                                {{ $profile->user?->name ?? '-' }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ $profile->user?->email ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-theme-sm text-gray-700 dark:text-gray-300">
                                    {{ $profile->user?->username ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-300">
                                    {{ $profile->phone ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-400">
                                    {{ $profile->registered_at ? $profile->registered_at->format('d M Y') : ($profile->created_at ? $profile->created_at->format('d M Y') : '-') }}
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <span class="inline-flex items-center text-xs font-medium text-brand-500 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 transition">
                                        Tugaskan &rarr;
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Semua penyewa aktif telah memiliki kamar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($unassignedTenants->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-white/[0.05]">
                    {{ $unassignedTenants->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- MODAL 0: KELOLA SEWA (PILIHAN PINDAH KAMAR ATAU SELESAI SEWA) -->
    <div
        x-show="isManageAssignmentModalOpen"
        x-cloak
        @keydown.escape.window="isManageAssignmentModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isManageAssignmentModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <template x-if="currentAssignment">
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-50 font-bold text-base text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                <span x-text="currentAssignment.room?.room_number"></span>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-800 dark:text-white/90" x-text="currentAssignment.tenant_profile?.user?.name"></h3>
                                <p class="text-xs text-gray-400" x-text="'Kamar ' + currentAssignment.room?.room_number + ' • Lantai ' + currentAssignment.room?.floor_name"></p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="isManageAssignmentModalOpen = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            &times;
                        </button>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3.5 text-xs text-gray-600 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-300 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Tarif Sewa:</span>
                            <span class="font-semibold text-gray-800 dark:text-white/90" x-text="'Rp ' + Number(currentAssignment.rent_amount_snapshot).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Tanggal Mulai:</span>
                            <span class="font-medium" x-text="currentAssignment.start_date"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Kontak:</span>
                            <span class="font-mono" x-text="currentAssignment.tenant_profile?.phone || '-'"></span>
                        </div>
                    </div>

                    <p class="mt-4 mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Pilih Tindakan Penugasan
                    </p>

                    <div class="space-y-2.5">
                        <button
                            type="button"
                            @click="isManageAssignmentModalOpen = false; isChangeRoomModalOpen = true"
                            class="w-full flex items-center justify-between p-3.5 rounded-xl border border-gray-200 hover:border-brand-500 hover:bg-brand-50/30 dark:border-gray-700 dark:hover:border-brand-400 dark:hover:bg-brand-500/10 transition group text-start"
                        >
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                                    🔄
                                </span>
                                <div>
                                    <p class="font-medium text-sm text-gray-800 group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400">
                                        Pindah Kamar
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Pindahkan penyewa ke kamar kosong lainnya
                                    </p>
                                </div>
                            </div>
                            <span class="text-gray-400 group-hover:text-brand-500">&rarr;</span>
                        </button>

                        <button
                            type="button"
                            @click="isManageAssignmentModalOpen = false; isEndAssignmentModalOpen = true"
                            class="w-full flex items-center justify-between p-3.5 rounded-xl border border-gray-200 hover:border-warning-500 hover:bg-warning-50/30 dark:border-gray-700 dark:hover:border-warning-400 dark:hover:bg-warning-500/10 transition group text-start"
                        >
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                    🚪
                                </span>
                                <div>
                                    <p class="font-medium text-sm text-gray-800 group-hover:text-warning-700 dark:text-white/90 dark:group-hover:text-warning-400">
                                        Selesai Sewa (Checkout)
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Akhiri masa sewa dan kosongkan kamar ini
                                    </p>
                                </div>
                            </div>
                            <span class="text-gray-400 group-hover:text-warning-600">&rarr;</span>
                        </button>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button
                            type="button"
                            @click="isManageAssignmentModalOpen = false"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL 1: TUGASKAN KAMAR -->
    <div
        x-show="isAssignModalOpen"
        x-cloak
        @keydown.escape.window="isAssignModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isAssignModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-lg rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Tugaskan Kamar ke Penyewa</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih penyewa dan kamar kosong yang tersedia</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.assignments.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="assign_tenant_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Penyewa <span class="text-error-500">*</span>
                    </label>
                    <select
                        id="assign_tenant_id"
                        name="tenant_id"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    >
                        <option value="">-- Pilih Penyewa --</option>
                        @foreach ($allUnassignedProfiles as $up)
                            <option value="{{ $up->id }}" :selected="selectedTenantId == {{ $up->id }}">
                                {{ $up->user?->name }} ({{ '@' . $up->user?->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="assign_room_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Kamar Kosong <span class="text-error-500">*</span>
                    </label>
                    <select
                        id="assign_room_id"
                        name="room_id"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    >
                        <option value="">-- Pilih Kamar --</option>
                        @foreach ($availableRooms as $ar)
                            <option value="{{ $ar->id }}">
                                Kamar {{ $ar->room_number }} (Lantai {{ $ar->floor_name }}) — Rp {{ number_format($ar->rent_amount, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @if ($availableRooms->isEmpty())
                        <p class="mt-1 text-xs text-error-500">Tidak ada kamar kosong yang tersedia di lokasi ini.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="assign_start_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Mulai Sewa <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="assign_start_date"
                            name="start_date"
                            type="date"
                            value="{{ date('Y-m-d') }}"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                    </div>
                    <div>
                        <label for="assign_end_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Selesai (Opsional)
                        </label>
                        <input
                            id="assign_end_date"
                            name="end_date"
                            type="date"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button
                        type="button"
                        @click="isAssignModalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        @disabled($availableRooms->isEmpty())
                        class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition disabled:opacity-50"
                    >
                        Simpan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: PINDAH KAMAR -->
    <div
        x-show="isChangeRoomModalOpen"
        x-cloak
        @keydown.escape.window="isChangeRoomModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isChangeRoomModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Pindah Kamar Penyewa</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pindahkan penyewa ke kamar kosong lainnya</p>
                </div>
            </div>

            <template x-if="currentAssignment">
                <form
                    method="POST"
                    :action="'/admin/assignments/' + currentAssignment.id + '/change-room'"
                    class="space-y-4"
                >
                    @csrf
                    @method('PATCH')

                    <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <p>Penyewa: <strong x-text="currentAssignment.tenant_profile?.user?.name"></strong></p>
                        <p class="mt-0.5">Kamar Saat Ini: <strong x-text="'Kamar ' + currentAssignment.room?.room_number"></strong></p>
                    </div>

                    <div>
                        <label for="new_room_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Pilih Kamar Tujuan <span class="text-error-500">*</span>
                        </label>
                        <select
                            id="new_room_id"
                            name="new_room_id"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        >
                            <option value="">-- Pilih Kamar Kosong --</option>
                            @foreach ($availableRooms as $ar)
                                <option value="{{ $ar->id }}">
                                    Kamar {{ $ar->room_number }} (Lantai {{ $ar->floor_name }}) — Rp {{ number_format($ar->rent_amount, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="transfer_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Pindah <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="transfer_date"
                            name="transfer_date"
                            type="date"
                            value="{{ date('Y-m-d') }}"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button
                            type="button"
                            @click="isChangeRoomModalOpen = false"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-blue-700 transition"
                        >
                            Proses Pindah
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL 3: SELESAI SEWA (CHECKOUT) -->
    <div
        x-show="isEndAssignmentModalOpen"
        x-cloak
        @keydown.escape.window="isEndAssignmentModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isEndAssignmentModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Selesai Sewa / Checkout</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kosongkan kamar dan akhiri penugasan penyewa</p>
                </div>
            </div>

            <template x-if="currentAssignment">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Penugasan untuk penyewa <strong x-text="currentAssignment.tenant_profile?.user?.name"></strong> pada <strong x-text="'Kamar ' + currentAssignment.room?.room_number"></strong> akan diakhiri. Kamar akan kembali berstatus kosong.
                    </p>

                    <form
                        method="POST"
                        :action="'/admin/assignments/' + currentAssignment.id + '/end'"
                        class="mt-5 space-y-4"
                    >
                        @csrf
                        <div>
                            <label for="modal_end_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password Admin Konfirmasi
                            </label>
                            <input
                                id="modal_end_password"
                                name="password"
                                type="password"
                                required
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-warning-500 dark:border-gray-700 dark:text-white/90"
                            />
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                @click="isEndAssignmentModalOpen = false"
                                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-warning-600 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-warning-700 transition"
                            >
                                Ya, Selesaikan Sewa
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
