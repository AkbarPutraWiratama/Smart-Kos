<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <header class="border-b border-gray-100 p-5 dark:border-gray-800">
        <h2 class="text-base font-bold text-gray-800 dark:text-white/90">Tenggat Pembayaran Penyewa</h2>
        <p class="text-xs text-gray-400">Pantau jatuh tempo dan status pembayaran seluruh unit aktif.</p>
    </header>
    <div class="overflow-x-auto">
        <table class="w-full text-start text-xs">
            <thead class="border-b border-gray-100 bg-gray-50/75 text-[11px] uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3.5 text-start font-semibold">Penyewa</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Kamar &amp; Lokasi</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Nominal Sewa</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Jatuh Tempo</th>
                    <th class="px-4 py-3.5 text-start font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                @forelse ($assignments as $assignment)
                    @php
                        $room = $assignment->room;
                        $tenantUser = $assignment->tenantProfile?->user;
                        $dueDate = $assignment->getPaymentDueDate();
                        $days = $assignment->getDaysUntilDue();
                        $isPaid = $assignment->isPaidForCurrentPeriod();
                        $hasPending = $assignment->hasPendingPayment();
                        $status = $assignment->getPaymentStatus();
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
                            @php($statusColor = $isPaid ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' : ($hasPending ? 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400' : ($days <= 0 ? 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400' : 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400')))
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusColor }}">{{ $isPaid ? 'Lunas' : ($hasPending ? 'Menunggu Verifikasi' : $status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada penyewa aktif tercatat. <a href="{{ route('admin.tenants.index') }}" class="font-semibold text-brand-600 dark:text-brand-400">Tambah penyewa &rarr;</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
