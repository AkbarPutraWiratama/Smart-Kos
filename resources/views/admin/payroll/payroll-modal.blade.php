<div x-show="showPayrollModal" x-cloak
    class="fixed inset-0 z-999999 flex items-center justify-center bg-black/50 p-4"
    @keydown.escape.window="showPayrollModal = false">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.stop
        x-data="{
            gross: '',
            deduction: 0,
            get net() {
                const g = parseFloat(this.gross) || 0;
                const d = parseFloat(this.deduction) || 0;
                return Math.max(0, g - d);
            }
        }">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Catat Pembayaran Gaji Staf</h3>
            <button type="button" @click="showPayrollModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.payroll.store') }}" class="mt-4 space-y-4">
            @csrf

            {{-- Staff selection --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Staf Penerima Gaji <span class="text-error-500">*</span></label>
                <select name="staff_id" required
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Pilih staf</option>
                    @foreach ($staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email ?? $staff->username }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Gross Amount --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Gaji Pokok / Kotor (Rp) <span class="text-error-500">*</span></label>
                <input type="number" name="gross_amount" x-model="gross" required min="0" step="1000" placeholder="Contoh: 2500000"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>

            {{-- Cash Advance Deduction --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Potongan Kasbon (Rp)</label>
                <input type="number" name="cash_advance_deduction" x-model="deduction" min="0" step="1000" placeholder="0"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <p class="mt-1 text-[11px] text-gray-400">Nominal kasbon yang dipotong dari gaji bulan ini.</p>
            </div>

            {{-- Net Preview Card --}}
            <div class="rounded-xl border border-brand-100 bg-brand-50/50 p-3.5 dark:border-brand-500/20 dark:bg-brand-500/5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Gaji Bersih (Ditransfer):</span>
                    <span class="text-base font-bold tabular-nums text-brand-600 dark:text-brand-400"
                          x-text="'Rp ' + (net).toLocaleString('id-ID')">Rp 0</span>
                </div>
            </div>

            {{-- Payment Date --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Tanggal Pembayaran <span class="text-error-500">*</span></label>
                <input type="date" name="payment_date" required value="{{ now()->toDateString() }}"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>

            {{-- Notes --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Catatan (Opsional)</label>
                <input type="text" name="notes" placeholder="Contoh: Gaji bulan November + bonus lembur"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>

            <div class="mt-5 flex justify-end gap-3 pt-2">
                <button type="button" @click="showPayrollModal = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                    Simpan Gaji
                </button>
            </div>
        </form>
    </div>
</div>
