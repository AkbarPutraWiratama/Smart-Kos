<div x-show="showAdvanceModal" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-999999 flex items-center justify-center bg-black/50 p-4"
    @keydown.escape.window="showAdvanceModal = false">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.stop
        x-show="showAdvanceModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Catat Kasbon Staf Baru</h3>
            <button type="button" @click="showAdvanceModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.cash-advances.store') }}" class="mt-4 space-y-4">
            @csrf

            {{-- Staff selection --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Staf Penerima Kasbon <span class="text-error-500">*</span></label>
                <select name="staff_id" required
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Pilih staf</option>
                    @foreach ($staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email ?? $staff->username }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Amount --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Nominal Kasbon (Rp) <span class="text-error-500">*</span></label>
                <input type="number" name="amount" required min="1" step="1000" placeholder="Contoh: 500000"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>

            {{-- Advance Date --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Tanggal Kasbon <span class="text-error-500">*</span></label>
                <input type="date" name="advance_date" required value="{{ now()->toDateString() }}"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>

            {{-- Notes --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Catatan / Keperluan</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Keperluan darurat keluarga"
                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-700 outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-3">
                <button type="button" @click="showAdvanceModal = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-brand-600">
                    Simpan Kasbon
                </button>
            </div>
        </form>
    </div>
</div>
