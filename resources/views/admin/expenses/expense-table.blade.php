<x-common.component-card title="Daftar Pengeluaran" desc="Semua pengeluaran operasional dan pemeliharaan tercatat di sini.">
    @if ($expenses->isEmpty())
        <div class="py-10 text-center text-gray-400 dark:text-gray-500">
            <svg class="mx-auto mb-3 h-10 w-10 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2 2 4-4M7 2h10a2 2 0 012 2v16a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z"/>
            </svg>
            <p class="text-sm">Belum ada pengeluaran tercatat.</p>
            <a href="{{ route('admin.expenses.create') }}" class="mt-3 inline-block text-sm font-medium text-brand-500 hover:underline">Catat sekarang &rarr;</a>
        </div>
    @else
        @php
            $typeColors = [
                'operational' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-400',
                'maintenance'  => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
                'other'        => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            ];
        @endphp

        {{-- Mobile Card View (Zero horizontal scrolling) --}}
        <div class="divide-y divide-gray-100 sm:hidden dark:divide-white/[0.05]">
            @foreach ($expenses as $expense)
                <div class="p-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-white/90 text-sm break-words">{{ $expense->title }}</p>
                            @if ($expense->description)
                                <p class="text-xs text-gray-400 dark:text-gray-500 line-clamp-2 mt-0.5">{{ $expense->description }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 font-bold tabular-nums text-error-600 dark:text-error-400 text-sm">
                            Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400 pt-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $typeColors[$expense->category?->type ?? 'other'] ?? '' }}">
                                {{ $expense->category?->name ?? '—' }}
                            </span>
                            <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                            <span class="text-gray-500 dark:text-gray-400">
                                {{ $expense->location ? $expense->location->name : 'Semua lokasi' }}
                            </span>
                        </div>
                        <span class="text-[11px] text-gray-400">
                            {{ $expense->expense_date->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-1 border-t border-gray-100 dark:border-gray-800" x-data="{ confirmDelete: false }">
                        @if ($expense->receipt_path)
                            <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>Struk</span>
                            </a>
                        @endif
                        <a href="{{ route('admin.expenses.edit', $expense) }}"
                            class="inline-flex items-center gap-1 text-xs text-gray-600 hover:text-gray-800 dark:text-gray-300">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit</span>
                        </a>
                        <button type="button" @click="confirmDelete = true"
                            class="inline-flex items-center gap-1 text-xs text-error-600 hover:text-error-700 dark:text-error-400">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus</span>
                        </button>

                        {{-- Confirm delete modal --}}
                        <div x-show="confirmDelete" x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 z-999999 flex items-center justify-center bg-black/50 p-4"
                            @keydown.escape.window="confirmDelete = false">
                            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.stop
                                x-show="confirmDelete"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Pengeluaran?</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Pengeluaran <strong>{{ $expense->title }}</strong> (Rp {{ number_format($expense->amount, 0, ',', '.') }}) akan dihapus permanen beserta catatan keuangannya.
                                </p>
                                <div class="mt-5 flex justify-end gap-3">
                                    <button type="button" @click="confirmDelete = false"
                                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                                        Batal
                                    </button>
                                    <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}">
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
                </div>
            @endforeach
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden sm:block overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[750px] text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400 whitespace-nowrap">Tanggal</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400">Judul &amp; Deskripsi</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400 whitespace-nowrap">Kategori</th>
                        <th class="pb-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-400 whitespace-nowrap">Lokasi</th>
                        <th class="pb-3 pe-4 text-end text-xs font-semibold uppercase tracking-wide text-gray-400 whitespace-nowrap">Jumlah</th>
                        <th class="pb-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-400 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($expenses as $expense)
                        <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                            <td class="py-3 pe-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $expense->expense_date->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3 pe-4 font-medium text-gray-800 dark:text-white/90">
                                <div>{{ $expense->title }}</div>
                                @if ($expense->description)
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500 break-words">{{ $expense->description }}</p>
                                @endif
                            </td>
                            <td class="py-3 pe-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $typeColors[$expense->category?->type ?? 'other'] ?? '' }}">
                                    {{ $expense->category?->name ?? '&mdash;' }}
                                </span>
                            </td>
                            <td class="py-3 pe-4 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                @if ($expense->location)
                                    {{ $expense->location->name }}
                                @else
                                    <span class="italic text-gray-300 dark:text-gray-600">Semua lokasi</span>
                                @endif
                            </td>
                            <td class="py-3 pe-4 text-end font-semibold tabular-nums text-error-600 dark:text-error-400 whitespace-nowrap">
                                Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 text-end whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1" x-data="{ confirmDelete: false }">
                                    @if ($expense->receipt_path)
                                        <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 text-gray-400 transition hover:border-brand-300 hover:text-brand-500 dark:border-gray-700 dark:hover:border-brand-700"
                                            title="Lihat struk">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.expenses.edit', $expense) }}"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 text-gray-400 transition hover:border-brand-300 hover:text-brand-500 dark:border-gray-700"
                                        title="Edit">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button type="button" @click="confirmDelete = true"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 text-gray-400 transition hover:border-error-300 hover:text-error-500 dark:border-gray-700"
                                        title="Hapus">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>

                                    {{-- Confirm delete modal --}}
                                    <div x-show="confirmDelete" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="fixed inset-0 z-999999 flex items-center justify-center bg-black/50 p-4"
                                        @keydown.escape.window="confirmDelete = false">
                                        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.stop
                                            x-show="confirmDelete"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95">
                                            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Pengeluaran?</h3>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Pengeluaran <strong>{{ $expense->title }}</strong> (Rp {{ number_format($expense->amount, 0, ',', '.') }}) akan dihapus permanen beserta catatan keuangannya.
                                            </p>
                                            <div class="mt-5 flex justify-end gap-3">
                                                <button type="button" @click="confirmDelete = false"
                                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                                                    Batal
                                                </button>
                                                <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}">
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

        @if ($expenses->hasPages())
            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                {{ $expenses->links() }}
            </div>
        @endif
    @endif
</x-common.component-card>
