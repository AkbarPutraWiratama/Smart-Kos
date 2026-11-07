@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Pengumuman Staff" />

<div class="space-y-6">
    <!-- Success Alert -->
    @if (session('success'))
        <div class="flex items-center justify-between rounded-xl border border-success-200 bg-success-50 p-4 text-success-800 dark:border-success-500/20 dark:bg-success-500/15 dark:text-success-400">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Header Actions Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800 md:text-xl dark:text-white/90">
                    Daftar Pengumuman Diterbitkan
                </h2>
                <p class="mt-1 text-xs text-gray-500 md:text-sm dark:text-gray-400">
                    Kelola dan pantau seluruh pengumuman yang telah Anda publikasikan untuk penghuni kos
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('staff.dashboard') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                >
                    &larr; Dashboard
                </a>
                <a
                    href="{{ route('staff.posts.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 active:scale-95 transition"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Buat Pengumuman Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-start font-semibold">Pengumuman</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Target Penerima</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Tanggal Terbit</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Status</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                    @forelse ($posts as $post)
                        @php
                            $primaryTarget = $post->targets->first();
                            $targetLabel = 'Semua Lokasi';
                            $targetBadge = 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400';

                            if ($primaryTarget?->target_type === 'location' && $primaryTarget->location) {
                                $targetLabel = 'Lokasi: ' . $primaryTarget->location->name;
                                $targetBadge = 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400';
                            } elseif ($primaryTarget?->target_type === 'room' && $primaryTarget->room) {
                                $targetLabel = 'Kamar: ' . $primaryTarget->room->room_number . ' (' . ($primaryTarget->room->location->name ?? '') . ')';
                                $targetBadge = 'bg-theme-purple-500/10 text-theme-purple-500 dark:bg-theme-purple-500/20';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ $post->title }}
                                </div>
                                <div class="mt-1 line-clamp-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $post->content }}
                                </div>
                                @if ($post->image_path)
                                    <div class="mt-1.5 flex items-center gap-1 text-[11px] text-brand-600 dark:text-brand-400">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Memiliki lampiran gambar</span>
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $targetBadge }}">
                                    {{ $targetLabel }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-medium text-gray-800 dark:text-gray-200">
                                {{ $post->published_at ? $post->published_at->translatedFormat('d M Y, H:i') : $post->created_at->translatedFormat('d M Y, H:i') }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('staff.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        Anda belum pernah menerbitkan pengumuman.
                                    </p>
                                    <a
                                        href="{{ route('staff.posts.create') }}"
                                        class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600"
                                    >
                                        Mulai Buat Pengumuman
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

