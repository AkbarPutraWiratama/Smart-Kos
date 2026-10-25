@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Pelanggan & Staff" />

    <!-- Navigation Tabs between Penyewa & Staff -->
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex gap-6" aria-label="Tabs">
            <a
                href="{{ route('admin.users.index') }}"
                class="border-b-2 border-brand-500 py-3 text-sm font-semibold text-brand-500 dark:border-brand-400 dark:text-brand-400 transition"
                aria-current="page"
            >
                Akun Penyewa
            </a>
            <a
                href="{{ route('admin.staff.index') }}"
                class="border-b-2 border-transparent py-3 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
            >
                Akun Staff Kos
            </a>
        </nav>
    </div>

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Akun Penyewa</p>
            <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $metrics['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-success-600 dark:text-success-400">Penyewa Aktif</p>
            <p class="mt-2 text-2xl font-bold text-success-600 dark:text-success-400">{{ $metrics['active'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nonaktif</p>
            <p class="mt-2 text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $metrics['inactive'] }}</p>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-white/[0.05] dark:bg-white/[0.03]">
        <!-- Table Header & Actions -->
        <div class="flex flex-col gap-4 px-6 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Daftar Akun Penyewa
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola akun penyewa kos, status aktif/nonaktif, dan kredensial akses.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search & Filter Form -->
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama, username, email..."
                            class="h-10 w-48 sm:w-64 rounded-lg border border-gray-300 bg-transparent px-3.5 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                    </div>
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    >
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Aktif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    </select>
                    @if (request('search') || request('status'))
                        <a href="{{ route('admin.users.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-3 text-xs text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                            Reset
                        </a>
                    @endif
                </form>

                <a
                    href="{{ route('admin.users.create') }}"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
                >
                    + Tambah Penyewa
                </a>
            </div>
        </div>

        <!-- Mobile Card View (Tap to Configure) -->
        <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
            @forelse ($tenants as $tenant)
                <a
                    href="{{ route('admin.users.edit', $tenant) }}"
                    class="block p-4 transition hover:bg-gray-50 active:bg-gray-100 dark:hover:bg-white/[0.03] dark:active:bg-white/[0.05]"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 font-semibold text-sm text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                {{ strtoupper(substr($tenant->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-800 text-theme-sm dark:text-white/90">
                                    {{ $tenant->name }}
                                </p>
                                <p class="truncate text-gray-500 text-theme-xs dark:text-gray-400">
                                    {{ '@' . $tenant->username }} &bull; {{ $tenant->tenantProfile?->phone ?? 'Tanpa HP' }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            @if ($tenant->status === 'active')
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 dark:bg-green-500/15 dark:text-green-500">
                                    Aktif
                                </span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    Nonaktif
                                </span>
                            @endif
                            <svg class="h-4 w-4 text-gray-400 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    @php
                        $activeAssignment = $tenant->tenantProfile?->roomAssignments->first();
                    @endphp
                    <div class="mt-2.5 flex items-center justify-between text-theme-xs text-gray-500 dark:text-gray-400">
                        @if ($activeAssignment && $activeAssignment->room)
                            <span class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 font-medium">
                                🏠 {{ $activeAssignment->room->location->name ?? 'Kos' }} — Kamar {{ $activeAssignment->room->room_number }}
                            </span>
                        @else
                            <span class="italic text-gray-400">Belum ada kamar</span>
                        @endif
                        <span class="text-[11px] text-gray-400">Ketuk untuk kelola &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada data akun penyewa yang ditemukan.
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (Click Row to Configure) -->
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
                            Kamar Aktif
                        </th>
                        <th class="px-6 py-3.5 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                            Status
                        </th>
                        <th class="px-6 py-3.5 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400">
                            <span class="sr-only">Navigasi</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    @forelse ($tenants as $tenant)
                        <tr
                            onclick="window.location='{{ route('admin.users.edit', $tenant) }}'"
                            class="group cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/[0.03]"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 font-medium text-sm text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 text-theme-sm group-hover:text-brand-500 dark:text-white/90 dark:group-hover:text-brand-400 transition">
                                            {{ $tenant->name }}
                                        </p>
                                        <p class="text-gray-500 text-theme-xs dark:text-gray-400">
                                            {{ $tenant->email }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-theme-sm text-gray-700 dark:text-gray-300">
                                    {{ $tenant->username }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-theme-sm text-gray-600 dark:text-gray-400">
                                    {{ $tenant->tenantProfile?->phone ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $activeAssignment = $tenant->tenantProfile?->roomAssignments->first();
                                @endphp
                                @if ($activeAssignment && $activeAssignment->room)
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2 py-1 text-theme-xs font-medium text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                                        {{ $activeAssignment->room->location->name ?? 'Kos' }} — Kamar {{ $activeAssignment->room->room_number }}
                                    </span>
                                @else
                                    <span class="text-theme-xs text-gray-400 dark:text-gray-500 italic">
                                        Belum ada kamar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($tenant->status === 'active')
                                    <span class="inline-block rounded-full bg-green-50 px-2.5 py-0.5 text-theme-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-500">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block rounded-full bg-gray-100 px-2.5 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-end">
                                <span class="inline-flex items-center text-gray-400 group-hover:text-brand-500 dark:text-gray-500 dark:group-hover:text-brand-400 transition">
                                    <svg class="h-5 w-5 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada data akun penyewa yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tenants->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 dark:border-white/[0.05]">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
