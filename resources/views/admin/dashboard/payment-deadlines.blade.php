<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 p-5 dark:border-gray-800">
        <div>
            <h2 class="text-base font-bold text-gray-800 dark:text-white/90">Tenggat Pembayaran Penyewa</h2>
            <p class="text-xs text-gray-400">Menampilkan 5 penyewa prioritas jatuh tempo dari seluruh unit aktif.</p>
        </div>
        <a href="{{ route('admin.tenants.index', ['tab' => 'assigned']) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
            <span>Semua Penyewa ({{ $assignments->count() }})</span>
            <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </a>
    </header>

    {{-- Mobile Card View --}}
    <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
        @forelse ($assignments->take(5) as $assignment)
            @php
                $room = $assignment->room;
                $tenantUser = $assignment->tenantProfile?->user;
                $dueDate = $assignment->getPaymentDueDate();
                $days = $assignment->getDaysUntilDue();
                $isPaid = $assignment->isPaidForCurrentPeriod();
                $hasPending = $assignment->hasPendingPayment();
                $status = $assignment->getPaymentStatus();
                $statusColor = $isPaid
                    ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'
                    : ($hasPending
                        ? 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400'
                        : ($days <= 0
                            ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400'
                            : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400'));
            @endphp
            <div class="p-4 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 dark:text-white/90 text-sm">{{ $tenantUser->name ?? 'Penyewa' }}</p>
                        <p class="text-xs text-gray-400">Kamar {{ $room->room_number }} · {{ $room->location->name }}</p>
                    </div>
                    <span class="shrink-0 inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusColor }}">{{ $isPaid ? 'Lunas' : ($hasPending ? 'Verifikasi' : $status) }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>JT: {{ $dueDate->translatedFormat('d M Y') }}</span>
                    <span class="{{ $days <= 0 ? 'text-error-600 dark:text-error-400' : ($days <= 7 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-400') }}">
                        @if ($isPaid) Lunas @elseif ($hasPending) Menunggu verifikasi @elseif ($days < 0) Terlambat {{ abs($days) }} hari @elseif ($days === 0) Jatuh tempo hari ini @else H-{{ $days }}@endif
                    </span>
                </div>
                <div class="flex items-center justify-between pt-1 text-xs border-t border-gray-50 dark:border-white/[0.03]">
                    <span class="font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format((float) $assignment->rent_amount_snapshot, 0, ',', '.') }}<span class="text-[11px] font-normal text-gray-400">/bulan</span></span>
                    <a href="{{ route('admin.tenants.index', ['tab' => 'assigned', 'location_id' => $room->location_id]) }}" class="inline-flex items-center gap-1 font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        <span>Kelola</span>
                        <svg class="h-3 w-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada penyewa aktif tercatat. <a href="{{ route('admin.tenants.index') }}" class="font-semibold text-brand-600 dark:text-brand-400">Tambah penyewa &rarr;</a></div>
        @endforelse
    </div>

    {{-- Desktop Table View --}}
    <div class="hidden sm:block overflow-x-auto">
        <table class="w-full text-start text-xs">
            <thead class="border-b border-gray-100 bg-gray-50/75 text-[11px] uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3.5 text-start font-semibold">Penyewa</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Kamar &amp; Lokasi</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Nominal Sewa</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Jatuh Tempo</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Status</th>
                    <th class="px-4 py-3.5 text-end font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                @forelse ($assignments->take(5) as $assignment)
                    @php
                        $room = $assignment->room;
                        $tenantUser = $assignment->tenantProfile?->user;
                        $dueDate = $assignment->getPaymentDueDate();
                        $days = $assignment->getDaysUntilDue();
                        $isPaid = $assignment->isPaidForCurrentPeriod();
                        $hasPending = $assignment->hasPendingPayment();
                        $status = $assignment->getPaymentStatus();
                        $statusColor = $isPaid
                            ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'
                            : ($hasPending
                                ? 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400'
                                : ($days <= 0
                                    ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400'
                                    : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400'));
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                        <td class="px-4 py-3.5">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $tenantUser->name ?? 'Penyewa' }}</p>
                            <p class="text-[11px] text-gray-400">{{ '@' . ($tenantUser->username ?? '-') }} @if ($tenantUser?->phone)&bull; {{ $tenantUser->phone }}@endif</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-medium text-gray-800 dark:text-white/90">Kamar {{ $room->room_number }} <span class="text-[11px] text-gray-400">(Lt. {{ $room->floor_name }})</span></p>
                            <p class="text-[11px] text-gray-400">{{ $room->location->name }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format((float) $assignment->rent_amount_snapshot, 0, ',', '.') }}<span class="block text-[11px] font-normal text-gray-400">/bulan</span></td>
                        <td class="whitespace-nowrap px-4 py-3.5">
                            <p class="font-medium">{{ $dueDate->translatedFormat('d M Y') }}</p>
                            <p class="text-[11px] {{ $days <= 0 ? 'text-error-600 dark:text-error-400' : ($days <= 7 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-400') }}">
                                @if ($isPaid) Periode lunas @elseif ($hasPending) Menunggu verifikasi @elseif ($days < 0) Terlambat {{ abs($days) }} hari @elseif ($days === 0) Jatuh tempo hari ini @else H-{{ $days }}@endif
                            </p>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusColor }}">{{ $isPaid ? 'Lunas' : ($hasPending ? 'Menunggu Verifikasi' : $status) }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-end whitespace-nowrap">
                            <a href="{{ route('admin.tenants.index', ['tab' => 'assigned', 'location_id' => $room->location_id]) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition">
                                <span>Kelola</span>
                                <svg class="h-3 w-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada penyewa aktif tercatat. <a href="{{ route('admin.tenants.index') }}" class="font-semibold text-brand-600 dark:text-brand-400">Tambah penyewa &rarr;</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($assignments->count() > 5)
        <div class="border-t border-gray-100 p-3.5 text-center dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01]">
            <a href="{{ route('admin.tenants.index', ['tab' => 'assigned']) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                <span>Lihat {{ $assignments->count() - 5 }} penyewa aktif lainnya di Manajemen Penyewa</span>
                <svg class="h-3.5 w-3.5 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    @endif
</section>
