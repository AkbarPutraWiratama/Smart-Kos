<x-common.component-card title="Daftar Kasbon Staf" desc="Pencatatan uang muka (kasbon) staf dan status pelunasannya.">
    @if ($cashAdvances->isEmpty())
        <div class="py-10 text-center text-gray-400 dark:text-gray-500">
            <svg class="mx-auto mb-3 h-10 w-10 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm">Belum ada catatan kasbon untuk filter ini.</p>
        </div>
    @else
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[700px] text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Tanggal</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Nama Staf</th>
                        <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Nominal Kasbon</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Status</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Catatan</th>
                        <th class="pb-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($cashAdvances as $advance)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="py-3 pe-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $advance->advance_date?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="py-3 pe-4 font-medium text-gray-800 dark:text-white/90">
                                <div>{{ $advance->staff?->name ?? 'Staf' }}</div>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $advance->staff?->email ?? $advance->staff?->username ?? '' }}</span>
                            </td>
                            <td class="py-3 pe-4 text-end font-semibold tabular-nums text-warning-600 dark:text-warning-400">
                                Rp {{ number_format((float) $advance->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 pe-4">
                                <form method="POST" action="{{ route('admin.cash-advances.update-status', $advance) }}" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                        class="h-7 rounded-md border border-gray-200 bg-white px-2 text-xs font-medium outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800
                                            {{ $advance->status === 'outstanding' ? 'text-warning-600 dark:text-warning-400' : '' }}
                                            {{ $advance->status === 'partially_repaid' ? 'text-blue-light-600 dark:text-blue-light-400' : '' }}
                                            {{ $advance->status === 'repaid' ? 'text-success-600 dark:text-success-400' : '' }}
                                            {{ $advance->status === 'cancelled' ? 'text-gray-400 dark:text-gray-500 line-through' : '' }}">
                                        <option value="outstanding" @selected($advance->status === 'outstanding')>Belum Lunas (Outstanding)</option>
                                        <option value="partially_repaid" @selected($advance->status === 'partially_repaid')>Lunas Sebagian</option>
                                        <option value="repaid" @selected($advance->status === 'repaid')>Lunas (Repaid)</option>
                                        <option value="cancelled" @selected($advance->status === 'cancelled')>Dibatalkan</option>
                                    </select>
                                </form>
                            </td>
                            <td class="py-3 pe-4 text-gray-500 dark:text-gray-400">
                                <span class="line-clamp-1 text-xs">{{ $advance->notes ?? '—' }}</span>
                            </td>
                            <td class="py-3 text-end">
                                <div class="flex items-center justify-end" x-data="{ confirmDelete: false }">
                                    <button type="button" @click="confirmDelete = true"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 text-gray-400 transition hover:border-error-300 hover:text-error-500 dark:border-gray-700"
                                        title="Hapus kasbon">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>

                                    {{-- Confirm delete modal --}}
                                    <div x-show="confirmDelete" x-cloak
                                        class="fixed inset-0 z-999999 flex items-center justify-center bg-black/50 p-4"
                                        @keydown.escape.window="confirmDelete = false">
                                        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.stop>
                                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Catatan Kasbon?</h3>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Kasbon <strong>{{ $advance->staff?->name }}</strong> (Rp {{ number_format($advance->amount, 0, ',', '.') }}) akan dihapus permanen beserta catatan keuangannya.
                                            </p>
                                            <div class="mt-5 flex justify-end gap-3">
                                                <button type="button" @click="confirmDelete = false"
                                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                                                    Batal
                                                </button>
                                                <form method="POST" action="{{ route('admin.cash-advances.destroy', $advance) }}">
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

        @if ($cashAdvances->hasPages())
            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                {{ $cashAdvances->links() }}
            </div>
        @endif
    @endif
</x-common.component-card>
