<x-common.component-card title="Daftar Piutang & Tunggakan Sewa" desc="Daftar penyewa yang telah melewati tanggal jatuh tempo pembayaran sewa, terurut otomatis dari yang paling lama menunggak.">
    @if ($arrearsList->isEmpty())
        <div class="py-12 text-center text-gray-400 dark:text-gray-500">
            <svg class="mx-auto mb-3 h-12 w-12 text-success-500/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Tidak Ada Tunggakan Sewa Aktif</p>
            <p class="mt-1 text-xs text-gray-400">Semua penyewa aktif telah melunasi tagihan atau belum melewati batas jatuh tempo.</p>
        </div>
    @else
        {{-- Mobile Card View --}}
        <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
            @foreach ($arrearsList as $arrear)
                @php
                    $rawPhone = preg_replace('/[^0-9]/', '', $arrear['tenant_phone']);
                    if (str_starts_with($rawPhone, '0')) {
                        $cleanPhone = '62' . substr($rawPhone, 1);
                    } elseif (str_starts_with($rawPhone, '62')) {
                        $cleanPhone = $rawPhone;
                    } else {
                        $cleanPhone = $rawPhone ? '62' . $rawPhone : '';
                    }
                    $waMessage = rawurlencode("Halo {$arrear['tenant_name']}, kami dari pengelola Smart Kos mengingatkan bahwa pembayaran sewa Kamar {$arrear['room_number']} di {$arrear['location_name']} sebesar Rp " . number_format($arrear['outstanding_amount'], 0, ',', '.') . " telah jatuh tempo pada tanggal " . $arrear['payment_due_date']->translatedFormat('d M Y') . " ({$arrear['days_overdue']} hari yang lalu). Mohon untuk segera melakukan pembayaran. Terima kasih.");
                @endphp
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-white/90 text-sm">{{ $arrear['tenant_name'] }}</p>
                            <p class="text-xs text-gray-400">{{ $arrear['tenant_phone'] ?: ($arrear['tenant_email'] ?: 'Tanpa kontak') }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold
                            {{ $arrear['days_overdue'] >= 14 ? 'bg-error-100 text-error-800 dark:bg-error-950/60 dark:text-error-300' : 'bg-warning-100 text-warning-800 dark:bg-warning-950/60 dark:text-warning-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $arrear['days_overdue'] >= 14 ? 'bg-error-600' : 'bg-warning-500' }}"></span>
                            {{ $arrear['days_overdue'] }} Hari
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $arrear['location_name'] }} · Kamar {{ $arrear['room_number'] }}</span>
                        <span class="font-semibold tabular-nums text-error-600 dark:text-error-400">Rp {{ number_format($arrear['outstanding_amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span>JT: {{ $arrear['payment_due_date']->translatedFormat('d M Y') }}</span>
                        @if ($cleanPhone)
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 rounded-lg border border-success-200 bg-success-50 px-2 py-1 text-xs font-semibold text-success-700 hover:bg-success-100 dark:border-success-800 dark:bg-success-950/30 dark:text-success-400">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                WA
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Desktop Table --}}
        <div class="hidden sm:block overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[850px] text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">

                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Penyewa</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Lokasi &amp; Kamar</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Jatuh Tempo</th>
                        <th class="pb-3 pe-4 text-center text-xs font-semibold uppercase tracking-wide text-gray-400">Keterlambatan</th>
                        <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Tagihan Sewa</th>
                        <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Total Piutang</th>
                        <th class="pb-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Aksi Penagihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($arrearsList as $arrear)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            {{-- Tenant info --}}
                            <td class="py-3.5 pe-4 font-medium text-gray-800 dark:text-white/90">
                                <div class="font-semibold">{{ $arrear['tenant_name'] }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $arrear['tenant_phone'] ?: ($arrear['tenant_email'] ?: 'Tanpa kontak') }}
                                </div>
                            </td>

                            {{-- Location & Room --}}
                            <td class="py-3.5 pe-4 text-gray-700 dark:text-gray-300">
                                <div class="font-medium">{{ $arrear['location_name'] }}</div>
                                <div class="text-xs text-gray-400">Kamar {{ $arrear['room_number'] }}</div>
                            </td>

                            {{-- Due date --}}
                            <td class="py-3.5 pe-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                <div class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $arrear['payment_due_date']->translatedFormat('d M Y') }}
                                </div>
                                <span class="text-[11px] text-gray-400">Siklus: {{ $arrear['payment_cycle'] === 'yearly' ? 'Tahunan' : 'Bulanan' }}</span>
                            </td>

                            {{-- Days Overdue (Sorted most to least) --}}
                            <td class="py-3.5 pe-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $arrear['days_overdue'] >= 14 ? 'bg-error-100 text-error-800 dark:bg-error-950/60 dark:text-error-300' : 'bg-warning-100 text-warning-800 dark:bg-warning-950/60 dark:text-warning-300' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $arrear['days_overdue'] >= 14 ? 'bg-error-600' : 'bg-warning-500' }}"></span>
                                    {{ $arrear['days_overdue'] }} Hari Terlambat
                                </span>
                            </td>

                            {{-- Outstanding Amount --}}
                            <td class="py-3.5 pe-4 text-end font-semibold tabular-nums text-error-600 dark:text-error-400 whitespace-nowrap">
                                Rp {{ number_format($arrear['outstanding_amount'], 0, ',', '.') }}
                            </td>

                            {{-- Total Arrears --}}
                            <td class="py-3.5 pe-4 text-end font-bold tabular-nums text-error-700 dark:text-error-300 whitespace-nowrap">
                                Rp {{ number_format($arrear['outstanding_amount'], 0, ',', '.') }}
                            </td>

                            {{-- Action Buttons --}}
                            <td class="py-3.5 text-end whitespace-nowrap">
                                @php
                                    $rawPhone = preg_replace('/[^0-9]/', '', $arrear['tenant_phone']);
                                    if (str_starts_with($rawPhone, '0')) {
                                        $cleanPhone = '62' . substr($rawPhone, 1);
                                    } elseif (str_starts_with($rawPhone, '62')) {
                                        $cleanPhone = $rawPhone;
                                    } else {
                                        $cleanPhone = $rawPhone ? '62' . $rawPhone : '';
                                    }
                                    $waMessage = rawurlencode("Halo {$arrear['tenant_name']}, kami dari pengelola Smart Kos mengingatkan bahwa pembayaran sewa Kamar {$arrear['room_number']} di {$arrear['location_name']} sebesar Rp " . number_format($arrear['outstanding_amount'], 0, ',', '.') . " telah jatuh tempo pada tanggal " . $arrear['payment_due_date']->translatedFormat('d M Y') . " (" . $arrear['days_overdue'] . " hari yang lalu). Mohon untuk segera melakukan pembayaran. Terima kasih.");
                                @endphp

                                @if ($cleanPhone)
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-success-200 bg-success-50 px-2.5 py-1.5 text-xs font-semibold text-success-700 transition hover:bg-success-100 dark:border-success-800 dark:bg-success-950/30 dark:text-success-400 dark:hover:bg-success-900/40"
                                       title="Kirim pengingat WhatsApp ke {{ $arrear['tenant_name'] }}">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                        Ingatkan WA
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">Tanpa No. WA</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50/70 font-semibold dark:border-gray-700 dark:bg-gray-800/40">
                        <td colspan="4" class="py-3.5 pe-4 text-gray-800 dark:text-white/90">
                            Total Piutang Tertunggak ({{ $totalOverdueTenants }} Penyewa)
                        </td>
                        <td class="py-3.5 pe-4 text-end text-error-600 dark:text-error-400 font-bold">
                            Rp {{ number_format($totalArrearsAmount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 pe-4 text-end text-error-700 dark:text-error-300 font-bold">
                            Rp {{ number_format($totalArrearsAmount, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-common.component-card>
