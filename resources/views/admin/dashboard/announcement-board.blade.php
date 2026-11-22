<section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-bold text-gray-800 dark:text-white/90">Papan Pengumuman &amp; Informasi</h2>
            <p class="text-xs text-gray-400">Klik pengumuman untuk mengedit — {{ $activeAnnouncementsCount }} aktif</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.posts.index') }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800">Riwayat</a>
            <button type="button" @click="isCreateModalOpen = true" class="rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">Buat Pengumuman</button>
        </div>
    </div>
    @include('components.smart-kos.announcement-list-editable', [
        'announcements' => $announcements,
        'locations' => $locations,
        'allRooms' => $allRooms,
        'updateRouteName' => 'admin.posts.update',
        'destroyRouteName' => 'admin.posts.destroy',
    ])
</section>
