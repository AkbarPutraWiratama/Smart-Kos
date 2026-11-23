<section class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-xl font-bold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-bold text-gray-800 md:text-xl dark:text-white/90">Halo, {{ $user->name }}</h2>
                    <span class="rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-400">Administrator</span>
                    <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">Akun Aktif</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">{{ '@' . $user->username }} &bull; {{ $user->email ?? 'Tanpa Email' }} @if ($user->phone)&bull; {{ $user->phone }}@endif</p>
            </div>
        </div>
        <nav class="grid grid-cols-1 gap-2 xs:grid-cols-3 sm:flex sm:flex-wrap sm:items-center" aria-label="Tautan cepat dashboard">
            <a href="{{ route('admin.posts.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-center text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Riwayat Pengumuman</a>
            <a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-center text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Manajemen Penyewa</a>
            <a href="{{ route('admin.finance.index') }}" class="inline-flex items-center justify-center rounded-lg border border-brand-200 bg-brand-50/50 px-3 py-2 text-center text-xs font-semibold text-brand-700 shadow-theme-xs hover:bg-brand-100/60 dark:border-brand-800/40 dark:bg-brand-500/10 dark:text-brand-400 dark:hover:bg-brand-500/20">Laporan Keuangan</a>
        </nav>
    </div>
</section>
