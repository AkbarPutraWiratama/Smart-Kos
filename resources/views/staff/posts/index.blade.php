@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Riwayat Pengumuman" />

<div
    x-data="{ showDeleteAllConfirm: false, showDeleteOneConfirm: false, deleteOneFormId: null }"
    class="space-y-6"
>
    {{-- Success Alert --}}
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

    {{-- Header Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white/90">
                        Riwayat Pengumuman Dihapus
                    </h2>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Pengumuman yang telah Anda hapus dari papan. Hapus permanen satu per satu atau sekaligus.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a
                    href="{{ route('staff.dashboard') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                >
                    &larr; Dashboard
                </a>

                @if ($trashedPosts->isNotEmpty())
                    <button
                        type="button"
                        @click="showDeleteAllConfirm = true"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-error-500 px-3.5 py-2.5 text-xs font-semibold text-white shadow-theme-xs hover:bg-error-600 active:scale-95 transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus Semua Riwayat</span>
                    </button>
                    {{-- Hidden destroy-all form --}}
                    <form id="destroy-all-form" action="{{ route('staff.posts.destroy-all') }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Trash List Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead class="bg-gray-50/75 border-b border-gray-100 text-gray-500 dark:bg-white/[0.02] dark:border-gray-800 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-start font-semibold">Pengumuman</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Target</th>
                        <th class="py-3.5 px-4 text-start font-semibold">Dihapus Pada</th>
                        <th class="py-3.5 px-4 text-center font-semibold">Hapus Permanen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                    @forelse ($trashedPosts as $post)
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
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition opacity-75">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-gray-700 dark:text-gray-300">{{ $post->title }}</div>
                                <div class="mt-1 line-clamp-2 text-xs text-gray-400 dark:text-gray-500">{{ $post->content }}</div>
                                <div class="mt-1 text-[11px] text-gray-400">
                                    Oleh: <span class="font-medium text-gray-500 dark:text-gray-400">{{ $post->author->name ?? 'Staff' }}</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $targetBadge }}">
                                    {{ $targetLabel }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-medium text-gray-500 dark:text-gray-400">
                                {{ $post->deleted_at->translatedFormat('d M Y, H:i') }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                {{-- Hidden per-row force-delete form --}}
                                <form
                                    id="force-delete-form-{{ $post->id }}"
                                    action="{{ route('staff.posts.force-destroy', $post->id) }}"
                                    method="POST"
                                    class="hidden"
                                >
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button
                                    type="button"
                                    @click="deleteOneFormId = 'force-delete-form-{{ $post->id }}'; showDeleteOneConfirm = true"
                                    class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus Permanen</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-14 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        Tidak ada riwayat pengumuman yang dihapus.
                                    </p>
                                    <a
                                        href="{{ route('staff.dashboard') }}"
                                        class="mt-3 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400"
                                    >
                                        &larr; Kembali ke Dashboard
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($trashedPosts->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                {{ $trashedPosts->links() }}
            </div>
        @endif
    </div>

    {{-- ── Confirm Delete ONE Modal ── --}}
    <div
        x-show="showDeleteOneConfirm"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
        @keydown.escape.window="showDeleteOneConfirm = false"
    >
        <div
            @click.outside="showDeleteOneConfirm = false"
            class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex flex-col items-center text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </span>
                <h4 class="mt-3 text-sm font-bold text-gray-800 dark:text-white/90">Hapus Permanen?</h4>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    Pengumuman ini akan dihapus secara permanen beserta semua lampirannya. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="mt-5 flex items-center justify-center gap-3">
                <button
                    type="button"
                    @click="showDeleteOneConfirm = false"
                    class="rounded-xl border border-gray-200 px-5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="showDeleteOneConfirm = false; document.getElementById(deleteOneFormId)?.submit()"
                    class="rounded-xl bg-error-500 px-5 py-2 text-xs font-semibold text-white hover:bg-error-600 active:scale-95 transition"
                >
                    Ya, Hapus Permanen
                </button>
            </div>
        </div>
    </div>

    {{-- ── Confirm Delete ALL Modal ── --}}
    <div
        x-show="showDeleteAllConfirm"
        x-cloak
        class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
        @keydown.escape.window="showDeleteAllConfirm = false"
    >
        <div
            @click.outside="showDeleteAllConfirm = false"
            class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xl dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex flex-col items-center text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-error-50 text-error-500 dark:bg-error-500/15">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
                <h4 class="mt-3 text-sm font-bold text-gray-800 dark:text-white/90">Hapus Semua Riwayat?</h4>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    Seluruh riwayat pengumuman yang terhapus akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="mt-5 flex items-center justify-center gap-3">
                <button
                    type="button"
                    @click="showDeleteAllConfirm = false"
                    class="rounded-xl border border-gray-200 px-5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="showDeleteAllConfirm = false; document.getElementById('destroy-all-form')?.submit()"
                    class="rounded-xl bg-error-500 px-5 py-2 text-xs font-semibold text-white hover:bg-error-600 active:scale-95 transition"
                >
                    Ya, Hapus Semua
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
