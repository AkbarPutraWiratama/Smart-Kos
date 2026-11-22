@if (session('success'))
    <div class="flex items-center justify-between rounded-xl border border-success-200 bg-success-50 p-4 text-success-800 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="rounded-xl border border-error-200 bg-error-50 p-4 text-error-800 dark:border-error-500/20 dark:bg-error-500/15 dark:text-error-400">
        <div class="flex items-center gap-2 text-sm font-semibold">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Terdapat kesalahan pada input pengumuman:</span>
        </div>
        <ul class="mt-2 list-inside list-disc space-y-1 text-xs">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
