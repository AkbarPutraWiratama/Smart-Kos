<x-common.component-card title="Riwayat Penggajian Staf" desc="Daftar pembayaran gaji staf setelah perhitungan potongan kasbon.">
    @if ($payrollRecords->isEmpty())
        <div class="py-10 text-center text-gray-400 dark:text-gray-500">
            <svg class="mx-auto mb-3 h-10 w-10 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm">Belum ada catatan penggajian untuk periode ini.</p>
        </div>
    @else
        {{-- Mobile Card View --}}
        <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
            @foreach ($payrollRecords as $payroll)
                <div class="p-4 space-y-1.5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-white/90 text-sm">{{ $payroll->staff?->name ?? 'Staf' }}</p>
                            <p class="text-xs text-gray-400">{{ $payroll->staff?->email ?? $payroll->staff?->username ?? '' }}</p>
                        </div>
                        <span class="shrink-0 font-semibold tabular-nums text-error-600 dark:text-error-400 text-sm">Rp {{ number_format((float) $payroll->net_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $payroll->payment_date?->translatedFormat('d M Y') ?? '—' }}</span>
                        @if ($payroll->cash_advance_deduction > 0)
                            <span class="text-blue-light-600 dark:text-blue-light-400">Potongan: Rp {{ number_format((float) $payroll->cash_advance_deduction, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    @if ($payroll->notes)
                        <p class="text-xs text-gray-400 italic line-clamp-1">{{ $payroll->notes }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Desktop Table --}}
        <div class="hidden sm:block overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[750px] text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Tanggal</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Nama Staf</th>
                        <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Gaji Kotor</th>
                        <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Potongan Kasbon</th>
                        <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Gaji Bersih</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Catatan</th>
                        <th class="pb-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($payrollRecords as $payroll)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="py-3 pe-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $payroll->payment_date?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="py-3 pe-4 font-medium text-gray-800 dark:text-white/90">
                                <div>{{ $payroll->staff?->name ?? 'Staf' }}</div>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $payroll->staff?->email ?? $payroll->staff?->username ?? '' }}</span>
                            </td>
                            <td class="py-3 pe-4 text-end tabular-nums text-gray-700 dark:text-gray-300">
                                Rp {{ number_format((float) $payroll->gross_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 pe-4 text-end tabular-nums text-blue-light-600 dark:text-blue-light-400">
                                @if ($payroll->cash_advance_deduction > 0)
                                    − Rp {{ number_format((float) $payroll->cash_advance_deduction, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="py-3 pe-4 text-end font-semibold tabular-nums text-error-600 dark:text-error-400">
                                Rp {{ number_format((float) $payroll->net_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 pe-4 text-gray-500 dark:text-gray-400">
                                <span class="line-clamp-1 text-xs">{{ $payroll->notes ?? '—' }}</span>
                            </td>
                            <td class="py-3 text-end">
                                <div class="flex items-center justify-end" x-data="{ confirmDelete: false }">
                                    <button type="button" @click="confirmDelete = true"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 text-gray-400 transition hover:border-error-300 hover:text-error-500 dark:border-gray-700"
                                        title="Hapus catatan">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>

                                    {{-- Confirm delete modal --}}
                                    <div x-show="confirmDelete" x-cloak
                                        class="fixed inset-0 z-999999 flex items-center justify-center bg-black/50 p-4"
                                        @keydown.escape.window="confirmDelete = false">
                                        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.stop>
                                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Catatan Gaji?</h3>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Catatan gaji <strong>{{ $payroll->staff?->name }}</strong> (Rp {{ number_format($payroll->net_amount, 0, ',', '.') }}) akan dihapus permanen beserta catatan keuangannya.
                                            </p>
                                            <div class="mt-5 flex justify-end gap-3">
                                                <button type="button" @click="confirmDelete = false"
                                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                                                    Batal
                                                </button>
                                                <form method="POST" action="{{ route('admin.payroll.destroy', $payroll) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg bg-error-500 px-4 py-2 text-sm font-semibold text-white hover:bg-error-600 transition">
                                                        Ya, Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($payrollRecords->hasPages())
            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                {{ $payrollRecords->links() }}
            </div>
        @endif
    @endif
</x-common.component-card>
