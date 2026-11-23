<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

    {{-- Table Header Info --}}
    <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Rekap Keuangan per Kamar</h3>
            <p class="mt-0.5 text-xs text-gray-400">{{ $roomRecap->count() }} kamar ditemukan — beban operasional dialokasikan secara proporsional per kamar dari tiap lokasi</p>
        </div>
    </div>

    @if ($roomRecap->isEmpty())
        <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada kamar yang cocok dengan filter.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" aria-label="Rekap keuangan per kamar">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">No. Kamar</th>
                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi / Lantai</th>
                        <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status &amp; Penyewa</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Pembayaran</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Piutang</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Beban Alokasi</th>
                        <th scope="col" class="px-5 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Net Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($roomRecap as $room)
                        <tr class="transition-colors hover:bg-gray-50/60 dark:hover:bg-white/[0.02]">
                            {{-- No. Kamar --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-white/[0.04] dark:text-gray-200">
                                    #{{ $room['room_number'] }}
                                </span>
                            </td>

                            {{-- Lokasi / Lantai --}}
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $room['location_name'] }}</p>
                                <p class="text-xs text-gray-400">Lantai {{ $room['floor_name'] }}</p>
                            </td>

                            {{-- Status & Penyewa --}}
                            <td class="px-5 py-3.5">
                                @if ($room['is_occupied'])
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                        Terisi
                                    </span>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $room['current_tenant'] }}</p>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                        Kosong
                                    </span>
                                @endif
                            </td>

                            {{-- Total Pembayaran --}}
                            <td class="px-5 py-3.5 text-end">
                                <span class="font-semibold tabular-nums text-gray-800 dark:text-white/90">
                                    Rp {{ number_format($room['total_payments'], 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- Piutang --}}
                            <td class="px-5 py-3.5 text-end">
                                @if ($room['total_outstanding'] > 0)
                                    <div>
                                        <span class="font-semibold tabular-nums text-error-600 dark:text-error-400">
                                            Rp {{ number_format($room['total_outstanding'], 0, ',', '.') }}
                                        </span>
                                        <p class="text-xs text-error-400">{{ $room['days_overdue'] }} hari</p>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Beban Alokasi --}}
                            <td class="px-5 py-3.5 text-end">
                                <span class="tabular-nums text-warning-600 dark:text-warning-400">
                                    Rp {{ number_format($room['total_expenses'], 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- Net Amount --}}
                            <td class="px-5 py-3.5 text-end">
                                <span class="font-bold tabular-nums {{ $room['net_amount'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                                    {{ $room['net_amount'] < 0 ? '-' : '' }}Rp {{ number_format(abs($room['net_amount']), 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
