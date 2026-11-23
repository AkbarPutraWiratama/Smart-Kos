<section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
    <header class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Rincian Transaksi Periode Terpilih</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rincian transaksi yang sesuai dengan filter laporan.</p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ number_format($financialRecords->total(), 0, ',', '.') }} catatan</span>
    </header>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="w-full min-w-[900px]">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="px-5 py-3 text-start sm:px-6"><span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</span></th>
                    <th class="px-5 py-3 text-start sm:px-6"><span class="text-xs font-medium text-gray-500 dark:text-gray-400">Deskripsi</span></th>
                    <th class="px-5 py-3 text-start sm:px-6"><span class="text-xs font-medium text-gray-500 dark:text-gray-400">Lokasi</span></th>
                    <th class="px-5 py-3 text-start sm:px-6"><span class="text-xs font-medium text-gray-500 dark:text-gray-400">Sumber</span></th>
                    <th class="px-5 py-3 text-start sm:px-6"><span class="text-xs font-medium text-gray-500 dark:text-gray-400">Jenis</span></th>
                    <th class="px-5 py-3 text-end sm:px-6"><span class="text-xs font-medium text-gray-500 dark:text-gray-400">Jumlah</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($financialRecords as $record)
                    <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-400 sm:px-6">
                            <span class="block font-medium text-gray-800 dark:text-white/90">{{ $record->transaction_date?->format('d M Y') ?? '—' }}</span>
                        </td>
                        <td class="min-w-48 px-5 py-4 sm:px-6">
                            <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $record->description }}</span>
                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                @if ($record->recordedBy)
                                    Dicatat oleh {{ $record->recordedBy->name }}
                                @elseif ($record->reference_type === 'va_payment')
                                    Sistem Otomatis (Midtrans VA)
                                @elseif ($record->manualPayment?->verifiedBy)
                                    Diverifikasi oleh {{ $record->manualPayment->verifiedBy->name }}
                                @else
                                    Sistem
                                @endif
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-400 sm:px-6">
                            {{ $record->location?->name ?? $record->manualPayment?->roomAssignment?->room?->location?->name ?? $record->vaPayment?->roomAssignment?->room?->location?->name ?? $record->expense?->location?->name ?? 'Semua Lokasi' }}
                        </td>
                        <td class="min-w-48 px-5 py-4 sm:px-6">
                            @if ($record->reference_type === 'va_payment')
                                <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">Virtual Account Midtrans</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                    {{ $record->vaPayment?->roomAssignment?->tenantProfile?->user?->name ?? 'Penyewa' }} · Kamar {{ $record->vaPayment?->roomAssignment?->room?->room_number ?? $record->vaPayment?->vaAccount?->room?->room_number ?? '—' }}
                                </span>
                            @elseif ($record->reference_type === 'manual_payment')
                                <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">Transfer Manual ({{ $record->manualPayment?->bank_name ?? 'Bank' }})</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                    {{ $record->manualPayment?->roomAssignment?->tenantProfile?->user?->name ?? $record->manualPayment?->submittedBy?->name ?? 'Penyewa' }} · Kamar {{ $record->manualPayment?->roomAssignment?->room?->room_number ?? '—' }}
                                </span>
                            @elseif ($record->reference_type === 'expense')
                                <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">Pengeluaran: {{ $record->expense?->category?->name ?? 'Umum' }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                    {{ $record->expense?->title ?? 'Pengeluaran' }}
                                </span>
                            @elseif ($record->reference_type === 'payroll')
                                <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">Gaji Staf</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                    Staf: {{ $record->payrollRecord?->staff?->name ?? '—' }}
                                </span>
                            @elseif ($record->reference_type === 'cash_advance')
                                <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">Kasbon Staf</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
                                    Staf: {{ $record->cashAdvance?->staff?->name ?? '—' }}
                                </span>
                            @else
                                <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ $record->reference_type ? ucwords(str_replace('_', ' ', $record->reference_type)) . ' #' . $record->reference_id : 'Catatan manual' }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                            @if ($record->type === 'income')
                                <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">Pemasukan</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-1 text-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-400">Pengeluaran</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-end text-sm font-semibold tabular-nums {{ $record->type === 'income' ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }} sm:px-6">
                            {{ $record->type === 'income' ? '+' : '−' }} Rp {{ number_format((float) $record->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center sm:px-6"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg></span><p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada transaksi pada periode ini</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ubah periode atau cakupan untuk melihat transaksi lainnya.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($financialRecords->hasPages())
        <footer class="border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">{{ $financialRecords->links() }}</footer>
    @endif
</section>
