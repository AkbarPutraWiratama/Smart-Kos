<section class="space-y-4">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Riwayat Pembayaran</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buka setiap catatan untuk menelusuri status dan asal pembayaran.</p>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah data per tabel mengikuti pilihan pagination di atas.</p>
    </header>

    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <header class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5v11.25H3.75zM3.75 7.5l2.1-3h12.3l2.1 3M16.5 12h3.75v3H16.5a1.5 1.5 0 010-3z" /></svg></span>
                <div><h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Virtual Account (Midtrans)</h3><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Status transaksi dari Midtrans.</p></div>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ number_format($vaPayments->total(), 0, ',', '.') }} transaksi</span>
        </header>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[760px]">
                <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Waktu</th><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Penyewa &amp; Unit</th><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Order ID</th><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Status</th><th class="px-5 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Jumlah</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($vaPayments as $payment)
                        @php($assignment = $payment->roomAssignment)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-400 sm:px-6">{{ $payment->transaction_time?->format('d M Y H:i') ?? $payment->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $assignment?->tenantProfile?->user?->name ?? 'Penyewa' }}</p><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $assignment?->room?->location?->name ?? 'Lokasi tidak tersedia' }} · Kamar {{ $assignment?->room?->room_number ?? '—' }}</p></td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-400 sm:px-6">{{ $payment->order_id }}</td>
                            <td class="px-5 py-4 sm:px-6"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel }}</span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-end text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90 sm:px-6">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">Belum ada riwayat pembayaran Virtual Account pada cakupan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($vaPayments->hasPages())<footer class="border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">{{ $vaPayments->links() }}</footer>@endif
    </article>

    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <header class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5v11.25H3.75zM3.75 7.5l2.1-3h12.3l2.1 3M7.5 12.25h4.25m-4.25 3h6.5" /></svg></span>
                <div><h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Transfer Manual</h3><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pengajuan, verifikasi, dan status transfer.</p></div>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ number_format($manualPayments->total(), 0, ',', '.') }} transaksi</span>
        </header>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[850px]">
                <thead><tr class="border-b border-gray-100 dark:border-gray-800"><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Diajukan</th><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Penyewa &amp; Unit</th><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Rekening Pengirim</th><th class="px-5 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Status &amp; Verifikator</th><th class="px-5 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Jumlah</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($manualPayments as $payment)
                        @php($assignment = $payment->roomAssignment)
                        <tr class="transition hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-400 sm:px-6">{{ $payment->submitted_at?->format('d M Y H:i') ?? '—' }}@if ($payment->verified_at)<span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Diverifikasi {{ $payment->verified_at->format('d M Y H:i') }}</span>@endif</td>
                            <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $assignment?->tenantProfile?->user?->name ?? $payment->submittedBy?->name ?? 'Penyewa' }}</p><p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $assignment?->room?->location?->name ?? 'Lokasi tidak tersedia' }} · Kamar {{ $assignment?->room?->room_number ?? '—' }}</p></td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300 sm:px-6"><span class="block font-medium">{{ $payment->bank_name }}</span><span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ $payment->bank_account_number }}</span></td>
                            <td class="px-5 py-4 sm:px-6"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel }}</span>@if ($payment->verifiedBy)<span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $payment->verifiedBy->name }}</span>@endif</td>
                            <td class="whitespace-nowrap px-5 py-4 text-end text-sm font-semibold tabular-nums text-gray-800 dark:text-white/90 sm:px-6">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">Belum ada riwayat transfer manual pada cakupan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($manualPayments->hasPages())<footer class="border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">{{ $manualPayments->links() }}</footer>@endif
    </article>
</section>
