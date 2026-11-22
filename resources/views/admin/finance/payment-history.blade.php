<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-sm text-gray-600 dark:text-gray-400">Tampilkan {{ $perPage }} riwayat per halaman pada setiap metode pembayaran.</p>
        <form method="GET" action="{{ route('admin.finance.index') }}" class="flex items-center gap-2">
            <label for="payment-history-per-page" class="text-xs font-medium text-gray-600 dark:text-gray-400">Baris per halaman</label>
            <select id="payment-history-per-page" name="per_page" onchange="this.form.submit()" class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <option value="5" @selected($perPage === 5)>5</option>
                <option value="10" @selected($perPage === 10)>10</option>
            </select>
        </form>
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <header class="border-b border-gray-100 p-5 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Virtual Account (Midtrans)</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Status transaksi dan waktu pembayaran dari Midtrans.</p>
        </header>
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="border-b border-gray-100 bg-gray-50/75 text-[11px] uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                    <tr><th class="px-4 py-3.5 text-start font-semibold">Waktu</th><th class="px-4 py-3.5 text-start font-semibold">Penyewa &amp; Unit</th><th class="px-4 py-3.5 text-start font-semibold">Order ID</th><th class="px-4 py-3.5 text-start font-semibold">Status</th><th class="px-4 py-3.5 text-end font-semibold">Jumlah</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                    @forelse ($vaPayments as $payment)
                        @php($assignment = $payment->roomAssignment)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                            <td class="whitespace-nowrap px-4 py-3.5">{{ $payment->transaction_time?->format('d M Y H:i') ?? $payment->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3.5"><p class="font-medium text-gray-800 dark:text-white/90">{{ $assignment?->tenantProfile?->user?->name ?? 'Penyewa' }}</p><p class="text-[11px] text-gray-400">{{ $assignment?->room?->location?->name ?? 'Lokasi tidak tersedia' }} · Kamar {{ $assignment?->room?->room_number ?? '—' }}</p></td>
                            <td class="whitespace-nowrap px-4 py-3.5">{{ $payment->order_id }}</td>
                            <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel }}</span></td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada riwayat pembayaran Virtual Account.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($vaPayments->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $vaPayments->links() }}</div>@endif
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <header class="border-b border-gray-100 p-5 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Transfer Manual</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Riwayat pengajuan, verifikasi, dan status transfer manual.</p>
        </header>
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="border-b border-gray-100 bg-gray-50/75 text-[11px] uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                    <tr><th class="px-4 py-3.5 text-start font-semibold">Diajukan</th><th class="px-4 py-3.5 text-start font-semibold">Penyewa &amp; Unit</th><th class="px-4 py-3.5 text-start font-semibold">Bank</th><th class="px-4 py-3.5 text-start font-semibold">Status / Verifikator</th><th class="px-4 py-3.5 text-end font-semibold">Jumlah</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 dark:divide-gray-800 dark:text-gray-300">
                    @forelse ($manualPayments as $payment)
                        @php($assignment = $payment->roomAssignment)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                            <td class="whitespace-nowrap px-4 py-3.5">{{ $payment->submitted_at?->format('d M Y H:i') ?? '—' }}@if ($payment->verified_at)<span class="block text-[11px] text-gray-400">Diverifikasi {{ $payment->verified_at->format('d M Y H:i') }}</span>@endif</td>
                            <td class="px-4 py-3.5"><p class="font-medium text-gray-800 dark:text-white/90">{{ $assignment?->tenantProfile?->user?->name ?? $payment->submittedBy?->name ?? 'Penyewa' }}</p><p class="text-[11px] text-gray-400">{{ $assignment?->room?->location?->name ?? 'Lokasi tidak tersedia' }} · Kamar {{ $assignment?->room?->room_number ?? '—' }}</p></td>
                            <td class="px-4 py-3.5">{{ $payment->bank_name }}<span class="block text-[11px] text-gray-400">{{ $payment->bank_account_number }}</span></td>
                            <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel }}</span>@if ($payment->verifiedBy)<span class="mt-1 block text-[11px] text-gray-400">{{ $payment->verifiedBy->name }}</span>@endif</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-end font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada riwayat transfer manual.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($manualPayments->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $manualPayments->links() }}</div>@endif
    </section>
</div>
