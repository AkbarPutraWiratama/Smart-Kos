<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Table Header Info --}}
    <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Rekap Keuangan per Penyewa</h3>
            <p class="mt-0.5 text-xs text-gray-400">{{ $tenantRecap->count() }} penyewa ditemukan — diurutkan berdasarkan total pembayaran tertinggi</p>
        </div>
    </div>

    @if ($tenantRecap->isEmpty())
        <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada data penyewa yang cocok dengan filter.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" aria-label="Rekap keuangan per penyewa">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Penyewa</th>
                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kamar Aktif</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Pembayaran</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Piutang Tertunggak</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Transaksi</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Net Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($tenantRecap as $tenant)
                        <tr class="transition-colors hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">
                            {{-- Penyewa Info --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                        {{ mb_strtoupper(mb_substr($tenant['tenant_name'], 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $tenant['tenant_name'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $tenant['tenant_email'] }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Kamar Aktif --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 text-xs {{ $tenant['has_active'] ? 'text-gray-700 dark:text-gray-200' : 'text-gray-400' }}">
                                    @if ($tenant['has_active'])
                                        <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                    @endif
                                    {{ $tenant['active_room'] }}
                                </span>
                            </td>

                            {{-- Total Pembayaran --}}
                            <td class="px-5 py-3.5 text-end">
                                <span class="font-semibold tabular-nums text-gray-800 dark:text-white/90">
                                    Rp {{ number_format($tenant['total_payments'], 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- Piutang Tertunggak --}}
                            <td class="px-5 py-3.5 text-end">
                                @if ($tenant['total_outstanding'] > 0)
                                    <div>
                                        <span class="font-semibold tabular-nums text-error-600 dark:text-error-400">
                                            Rp {{ number_format($tenant['total_outstanding'], 0, ',', '.') }}
                                        </span>
                                        <p class="text-xs text-error-400">{{ $tenant['days_overdue'] }} hari lewat</p>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Jumlah Transaksi --}}
                            <td class="px-5 py-3.5 text-end">
                                <span class="text-gray-600 dark:text-gray-300">{{ number_format($tenant['transaction_count'], 0, ',', '.') }}</span>
                            </td>

                            {{-- Net Amount --}}
                            <td class="px-5 py-3.5 text-end">
                                <span class="font-bold tabular-nums {{ $tenant['net_amount'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                                    {{ $tenant['net_amount'] < 0 ? '-' : '' }}Rp {{ number_format(abs($tenant['net_amount']), 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
