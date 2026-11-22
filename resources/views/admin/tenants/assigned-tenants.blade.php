<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
    <div class="px-6 mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Kamar Terisi dan Penghuni Aktif</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Daftar kamar yang sedang dihuni pada lokasi kos terpilih.</p>
    </div>

    <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
        @forelse ($assignedTenants as $assignment)
            <div
                @click="currentAssignment = {{ json_encode($assignment) }}; isManageAssignmentModalOpen = true"
                class="p-4 space-y-2.5 cursor-pointer transition hover:bg-gray-50 active:bg-gray-100 dark:hover:bg-white/[0.03] dark:active:bg-white/[0.05]"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 font-bold text-sm text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ $assignment->room->room_number }}</div>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-800 text-theme-sm dark:text-white/90">{{ $assignment->tenantProfile?->user?->name ?? 'Penyewa' }}</p>
                            <p class="truncate text-gray-500 text-theme-xs dark:text-gray-400">{{ $assignment->room->location->name ?? 'Kos' }} &bull; Lantai {{ $assignment->room->floor_name }}</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">Terisi</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 pt-2 dark:border-gray-800">
                    <span>Tarif Sewa: Rp {{ number_format($assignment->rent_amount_snapshot, 0, ',', '.') }}/bulan</span>
                    <span class="text-brand-500 font-medium">Kelola Kamar &rarr;</span>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada kamar yang sedang terisi pada lokasi kos ini.</div>
        @endforelse
    </div>

    <div class="hidden sm:block max-w-full overflow-x-auto">
        <table class="w-full">
            <thead class="border-t border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Kamar</th>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Penyewa</th>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Kontak</th>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tarif Sewa</th>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Mulai</th>
                    <th class="px-6 py-3.5 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse ($assignedTenants as $assignment)
                    <tr @click="currentAssignment = {{ json_encode($assignment) }}; isManageAssignmentModalOpen = true" class="group cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">Kamar {{ $assignment->room->room_number }}</p>
                            <p class="text-xs text-gray-400">Lantai {{ $assignment->room->floor_name }} &bull; {{ $assignment->room->location->name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $assignment->tenantProfile?->user?->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ '@' . ($assignment->tenantProfile?->user?->username ?? '-') }}</p>
                        </td>
                        <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-300">{{ $assignment->tenantProfile?->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-theme-sm font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format($assignment->rent_amount_snapshot, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $assignment->start_date ? $assignment->start_date->format('d M Y') : '-' }}</td>
                        <td class="px-6 py-4 text-end"><span class="text-xs font-medium text-brand-500">Kelola Kamar &rarr;</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada kamar yang sedang terisi pada lokasi kos ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($assignedTenants->hasPages())
        <div class="border-t border-gray-100 px-6 py-4 dark:border-white/[0.05]">{{ $assignedTenants->links() }}</div>
    @endif
</div>
