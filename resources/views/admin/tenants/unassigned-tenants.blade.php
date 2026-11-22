<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
    <div class="px-6 mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daftar Penyewa Belum Ditempatkan</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Daftar akun penyewa aktif yang belum ditempatkan pada kamar kos.</p>
    </div>

    <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
        @forelse ($unassignedTenants as $profile)
            <div @click="isAssignModalOpen = true; isRoomLocked = false; lockedRoomId = ''; selectedTenantId = {{ $profile->id }}" class="p-4 space-y-2 cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                <p class="font-semibold text-gray-800 text-theme-sm dark:text-white/90">{{ $profile->user?->name ?? '-' }}</p>
                <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ '@' . ($profile->user?->username ?? '-') }} &bull; {{ $profile->phone ?? 'Tanpa HP' }}</p>
                <div class="flex justify-between text-[11px] text-gray-400 pt-1">
                    <span>Belum Ditempatkan</span>
                    <span class="text-brand-500 font-medium">Tempatkan &rarr;</span>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">Seluruh penyewa aktif telah ditempatkan pada kamar kos.</div>
        @endforelse
    </div>

    <div class="hidden sm:block max-w-full overflow-x-auto">
        <table class="w-full">
            <thead class="border-t border-y border-gray-100 bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Penyewa</th>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Nama Pengguna</th>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Nomor HP</th>
                    <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Terdaftar</th>
                    <th class="px-6 py-3.5 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                @forelse ($unassignedTenants as $profile)
                    <tr @click="isAssignModalOpen = true; isRoomLocked = false; lockedRoomId = ''; selectedTenantId = {{ $profile->id }}" class="group cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $profile->user?->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $profile->user?->email ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4 font-mono text-theme-sm text-gray-700 dark:text-gray-300">{{ $profile->user?->username ?? '-' }}</td>
                        <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-300">{{ $profile->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $profile->registered_at ? $profile->registered_at->format('d M Y') : '-' }}</td>
                        <td class="px-6 py-4 text-end"><span class="text-xs font-medium text-brand-500">Tempatkan &rarr;</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Seluruh penyewa aktif telah ditempatkan pada kamar kos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($unassignedTenants->hasPages())
        <div class="border-t border-gray-100 px-6 py-4 dark:border-white/[0.05]">{{ $unassignedTenants->links() }}</div>
    @endif
</div>
