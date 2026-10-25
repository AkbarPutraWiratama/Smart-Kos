@extends('layouts.app')

@section('content')
<div x-data="{
    isDeactivateModalOpen: false,
    isReactivateModalOpen: false,
    isResetPasswordModalOpen: false,
    isDeleteModalOpen: false
}" class="space-y-6">

    <x-common.page-breadcrumb
        pageTitle="Kelola Staff: {{ $staff->name }}"
        :breadcrumbs="[
            ['label' => 'Pelanggan & Staff', 'url' => route('admin.staff.index')],
        ]"
    />

    <!-- Status & Info Banner -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-theme-purple-500/10 text-base font-semibold text-theme-purple-500 dark:bg-theme-purple-500/20 dark:text-theme-purple-400">
                    {{ strtoupper(substr($staff->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $staff->name }}</h3>
                        @if ($staff->status === 'active')
                            <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-theme-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-500">
                                Aktif
                            </span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                Nonaktif (sejak {{ $staff->inactive_at ? $staff->inactive_at->format('d M Y') : '-' }})
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Username: <span class="font-mono">{{ $staff->username }}</span> &bull; Role: Staff Operasional &bull; Email: {{ $staff->email }}
                    </p>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="isResetPasswordModalOpen = true"
                    class="rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.05] transition"
                >
                    Reset Password
                </button>

                @if ($staff->status === 'active')
                    <button
                        type="button"
                        @click="isDeactivateModalOpen = true"
                        class="rounded-lg bg-warning-500/10 px-3.5 py-2 text-theme-xs font-medium text-warning-700 hover:bg-warning-500/20 dark:bg-warning-500/20 dark:text-warning-400 transition"
                    >
                        Nonaktifkan Akun
                    </button>
                @else
                    <button
                        type="button"
                        @click="isReactivateModalOpen = true"
                        class="rounded-lg bg-green-50 px-3.5 py-2 text-theme-xs font-medium text-green-700 hover:bg-green-100 dark:bg-green-500/15 dark:text-green-400 transition"
                    >
                        Aktifkan Kembali
                    </button>

                    <button
                        type="button"
                        @click="isDeleteModalOpen = true"
                        class="rounded-lg bg-error-500/10 px-3.5 py-2 text-theme-xs font-medium text-error-700 hover:bg-error-500/20 dark:bg-error-500/20 dark:text-error-400 transition"
                    >
                        Hapus Akun
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Preservation Notice -->
    <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 text-sm text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-400">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
            </svg>
            <div>
                <strong class="font-semibold">Prinsip Keutuhan Data Finansial</strong>
                <p class="mt-0.5">Penonaktifan akun staff tidak akan menghapus riwayat operasional, pencatatan kas, verifikasi transfer, maupun histori keuangan yang pernah dicatat oleh staff ini.</p>
            </div>
        </div>
    </div>

    <!-- Lifecycle & Deletion Policy Banner (When Inactive) -->
    @if ($staff->status === 'inactive')
        <div class="rounded-2xl border {{ $staff->isEligibleForDeletion(7) ? 'border-error-200 bg-error-50/40 dark:border-error-500/20 dark:bg-error-500/5' : 'border-warning-200 bg-warning-50/40 dark:border-warning-500/20 dark:bg-warning-500/5' }} p-4">
            <div class="flex items-start gap-3">
                <div class="shrink-0 pt-0.5">
                    @if ($staff->isEligibleForDeletion(7))
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-error-100 text-error-600 dark:bg-error-500/20 dark:text-error-400 font-bold text-xs">!</span>
                    @else
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400 font-bold text-xs">⏳</span>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                        Status Siklus Akun Staff (Account Lifecycle — 7 Hari)
                    </h4>
                    <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-300">
                        @if ($staff->isEligibleForDeletion(7))
                            Akun telah nonaktif selama {{ $staff->inactive_at ? $staff->inactive_at->diffInDays(now()) : 7 }} hari dan telah memenuhi masa tenggang 7+ hari. Akun berstatus <strong>eligible for deletion</strong> (dapat dihapus permanen jika tidak memiliki catatan histori transaksi/keuangan).
                        @else
                            Akun staff sedang dalam masa nonaktif (sejak {{ $staff->inactive_at ? $staff->inactive_at->format('d M Y') : '-' }}). Tersisa <strong>{{ $staff->daysUntilDeletionEligible(7) }} hari lagi</strong> sebelum akun memenuhi masa tenggang 7 hari.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    @error('deletion')
        <div class="rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
            {{ $message }}
        </div>
    @enderror

    <!-- Edit Information Form -->
    <x-common.component-card
        title="Edit Data Akun Staff"
        desc="Perbarui informasi identitas dan kredensial login staff."
    >
        <form method="POST" action="{{ route('admin.staff.update', $staff) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Lengkap Staff <span class="text-error-500">*</span>
                    </label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $staff->name) }}"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                    @error('name')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Username <span class="text-error-500">*</span>
                    </label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username', $staff->username) }}"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                    @error('username')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email <span class="text-error-500">*</span>
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $staff->email) }}"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                    @error('email')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a
                    href="{{ route('admin.staff.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                >
                    Kembali
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-common.component-card>

    <!-- Modal Konfirmasi Nonaktifkan Akun Staff -->
    <div
        x-show="isDeactivateModalOpen"
        x-cloak
        @keydown.escape.window="isDeactivateModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isDeactivateModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Nonaktifkan Akun Staff</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini memerlukan verifikasi admin</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Staff <strong>{{ $staff->name }}</strong> tidak akan dapat masuk ke sistem setelah akun dinonaktifkan. Seluruh riwayat transaksi, verifikasi, dan histori keuangan yang pernah dicatat oleh staff ini tetap tersimpan utuh.
            </p>
            <form method="POST" action="{{ route('admin.staff.deactivate', $staff) }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="modal_deactivate_staff_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Admin Konfirmasi
                    </label>
                    <input
                        id="modal_deactivate_staff_password"
                        name="password"
                        type="password"
                        required
                        autofocus
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="isDeactivateModalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-warning-600 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-warning-700 transition"
                    >
                        Ya, Nonaktifkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Aktifkan Kembali Akun Staff -->
    <div
        x-show="isReactivateModalOpen"
        x-cloak
        @keydown.escape.window="isReactivateModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isReactivateModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Aktifkan Kembali Akun Staff</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini memerlukan verifikasi admin</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Staff <strong>{{ $staff->name }}</strong> akan dapat kembali login ke sistem operasional staff menggunakan kredensial aktifnya.
            </p>
            <form method="POST" action="{{ route('admin.staff.reactivate', $staff) }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="modal_reactivate_staff_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Admin Konfirmasi
                    </label>
                    <input
                        id="modal_reactivate_staff_password"
                        name="password"
                        type="password"
                        required
                        autofocus
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="isReactivateModalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-green-700 transition"
                    >
                        Ya, Aktifkan Kembali
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reset Password Staff -->
    <div
        x-show="isResetPasswordModalOpen"
        x-cloak
        @keydown.escape.window="isResetPasswordModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isResetPasswordModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Reset Password Staff</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tentukan kata sandi baru untuk akun staff</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Atur kata sandi baru untuk staff <strong>{{ $staff->name }}</strong>. Masukkan password admin Anda sebagai konfirmasi keamanan.
            </p>
            <form method="POST" action="{{ route('admin.staff.reset-password', $staff) }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="new_staff_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Baru Staff
                    </label>
                    <input
                        id="new_staff_password"
                        name="new_password"
                        type="password"
                        required
                        placeholder="Minimal 8 karakter"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                </div>
                <div>
                    <label for="new_staff_password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Ulangi Password Baru Staff
                    </label>
                    <input
                        id="new_staff_password_confirmation"
                        name="new_password_confirmation"
                        type="password"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                </div>
                <div class="border-t border-gray-100 pt-3 dark:border-gray-800">
                    <label for="modal_reset_staff_admin_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Admin Konfirmasi
                    </label>
                    <input
                        id="modal_reset_staff_admin_password"
                        name="password"
                        type="password"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="isResetPasswordModalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
                    >
                        Konfirmasi & Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Akun Staff Permanen (Phase 14 Lifecycle) -->
    <div
        x-show="isDeleteModalOpen"
        x-cloak
        @keydown.escape.window="isDeleteModalOpen = false"
        class="fixed inset-0 z-999999 flex items-center justify-center overflow-y-auto p-5"
    >
        <div @click="isDeleteModalOpen = false" class="fixed inset-0 bg-gray-900/60"></div>
        <div @click.stop class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Akun Staff Permanen</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini memerlukan verifikasi admin</p>
                </div>
            </div>

            @if (! $staff->isEligibleForDeletion(7))
                <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">
                    <strong>Belum memenuhi masa tenggang!</strong>
                    <p class="mt-1">Akun harus berstatus nonaktif minimal 7 hari sebelum memenuhi syarat penghapusan (tersisa {{ $staff->daysUntilDeletionEligible(7) }} hari lagi).</p>
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        @click="isDeleteModalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                    >
                        Tutup
                    </button>
                </div>
            @elseif ($staff->hasFinancialOrOperationalHistory())
                <div class="rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
                    <strong>Akun tidak dapat dihapus!</strong>
                    <p class="mt-1">Staff ini memiliki riwayat transaksi finansial, penggajian, kasbon, atau pencatatan pengeluaran kos di database. Menurut prinsip keutuhan data Smart Kos, histori finansial tidak boleh dimusnahkan.</p>
                </div>
                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        @click="isDeleteModalOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                    >
                        Tutup
                    </button>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Akun <strong>{{ $staff->name }}</strong> telah melewati 7 hari masa nonaktif dan tidak memiliki keterikatan catatan finansial. Apakah Anda yakin ingin menghapus akun ini secara permanen?
                </p>
                <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label for="modal_delete_staff_admin_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password Admin Konfirmasi
                        </label>
                        <input
                            id="modal_delete_staff_admin_password"
                            name="password"
                            type="password"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-error-500 dark:border-gray-700 dark:text-white/90"
                        />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="isDeleteModalOpen = false"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-error-600 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-error-700 transition"
                        >
                            Ya, Hapus Permanen
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

</div>
@endsection

