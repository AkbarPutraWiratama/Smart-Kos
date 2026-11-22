<section class="grid grid-cols-1 gap-4 xl:grid-cols-2" aria-label="Riwayat pembayaran terbaru">
    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <header class="flex items-center justify-between border-b border-gray-100 p-5 dark:border-gray-800"><div><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Pembayaran Virtual Account</h2><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">5 catatan terbaru dari Midtrans</p></div><a href="{{ route('admin.finance.index') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400">Semua riwayat &rarr;</a></header>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($recentVaPayments as $payment)
                @php($assignment = $payment->roomAssignment)
                <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                    <div class="min-w-0"><p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $assignment?->tenantProfile?->user?->name ?? 'Penyewa' }}</p><p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $assignment?->room?->location?->name ?? 'Lokasi tidak tersedia' }} · Kamar {{ $assignment?->room?->room_number ?? '—' }} · {{ $payment->created_at?->format('d M Y') }}</p></div>
                    <div class="shrink-0 text-end"><p class="text-sm font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p><span class="text-[11px] {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel }}</span></div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pembayaran Virtual Account.</p>
            @endforelse
        </div>
    </article>

    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <header class="flex items-center justify-between border-b border-gray-100 p-5 dark:border-gray-800"><div><h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Transfer Manual</h2><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">5 pengajuan terbaru</p></div><a href="{{ route('admin.finance.index') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400">Semua riwayat &rarr;</a></header>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($recentManualPayments as $payment)
                @php($assignment = $payment->roomAssignment)
                <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                    <div class="min-w-0"><p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $assignment?->tenantProfile?->user?->name ?? $payment->submittedBy?->name ?? 'Penyewa' }}</p><p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $assignment?->room?->location?->name ?? 'Lokasi tidak tersedia' }} · Kamar {{ $assignment?->room?->room_number ?? '—' }} · {{ $payment->submitted_at?->format('d M Y') }}</p></div>
                    <div class="shrink-0 text-end"><p class="text-sm font-semibold text-gray-800 dark:text-white/90">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p><span class="text-[11px] {{ $payment->statusBadgeClass() }}">{{ $payment->statusLabel }}</span></div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada transfer manual.</p>
            @endforelse
        </div>
    </article>
</section>
