@extends('layouts.app')

@section('content')
<div x-data="{
    isDeactivateModalOpen: false,
    isReactivateModalOpen: false,
    isResetPasswordModalOpen: false,
    isDeleteModalOpen: false
}" class="space-y-6">

    <x-common.page-breadcrumb
        pageTitle="Kelola Akun: {{ $user->name }}"
        :breadcrumbs="[
            ['label' => 'Pelanggan & Staff', 'url' => route('admin.users.index')],
        ]"
    />

    <!-- Status & Info Banner -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-base font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">{{ $user->name }}</h3>
                        @if ($user->status === 'active')
                            <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-theme-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-500">
                                Aktif
                            </span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                Nonaktif (sejak {{ $user->inactive_at ? $user->inactive_at->format('d M Y') : '-' }})
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Username: <span class="font-mono">{{ $user->username }}</span> &bull; Role: Penyewa
                        @if ($user->tenantProfile?->roomAssignments->first())
                            &bull; Kamar: <strong class="text-gray-700 dark:text-gray-300">{{ $user->tenantProfile->roomAssignments->first()->room->location->name ?? 'Kos' }} #{{ $user->tenantProfile->roomAssignments->first()->room->room_number }}</strong>
                        @endif
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

                @if ($user->status === 'active')
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

    <!-- Lifecycle & Deletion Policy Banner (When Inactive) -->
    @if ($user->status === 'inactive')
        <div class="rounded-2xl border {{ $user->isEligibleForDeletion(7) ? 'border-error-200 bg-error-50/40 dark:border-error-500/20 dark:bg-error-500/5' : 'border-warning-200 bg-warning-50/40 dark:border-warning-500/20 dark:bg-warning-500/5' }} p-4">
            <div class="flex items-start gap-3">
                <div class="shrink-0 pt-0.5">
                    @if ($user->isEligibleForDeletion(7))
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-error-100 text-error-600 dark:bg-error-500/20 dark:text-error-400 font-bold text-xs">!</span>
                    @else
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400 font-bold text-xs">⏳</span>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                        Status Siklus Akun (Account Lifecycle — 7 Hari)
                    </h4>
                    <p class="mt-0.5 text-xs text-gray-600 dark:text-gray-300">
                        @if ($user->isEligibleForDeletion(7))
                            Akun telah nonaktif selama {{ $user->inactive_at ? $user->inactive_at->diffInDays(now()) : 7 }} hari dan telah memenuhi masa tenggang 7+ hari. Akun berstatus <strong>eligible for deletion</strong> (dapat dihapus permanen jika tidak memiliki riwayat finansial/kamar aktif).
                        @else
                            Akun sedang dalam masa nonaktif (sejak {{ $user->inactive_at ? $user->inactive_at->format('d M Y') : '-' }}). Tersisa <strong>{{ $user->daysUntilDeletionEligible(7) }} hari lagi</strong> sebelum akun memenuhi masa tenggang 7 hari.
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
        title="Edit Data Akun & Profil Penyewa"
        desc="Perbarui informasi identitas, kontak, dan alamat penyewa."
    >
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section: Kredensial Akun -->
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Informasi Akun
                </h4>
                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Lengkap <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
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
                            value="{{ old('username', $user->username) }}"
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
                            value="{{ old('email', $user->email) }}"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                        @error('email')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Section: Data Profil Penyewa -->
            <div class="border-t border-gray-100 pt-6 dark:border-gray-800">
                <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Informasi Profil (Tenant Profile)
                </h4>
                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nomor WhatsApp / HP
                        </label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone', $user->tenantProfile?->phone) }}"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                        @error('phone')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="registered_at" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tanggal Pendaftaran
                        </label>
                        <input
                            id="registered_at"
                            name="registered_at"
                            type="date"
                            value="{{ old('registered_at', optional($user->tenantProfile?->registered_at)->format('Y-m-d')) }}"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                        @error('registered_at')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alamat Asal / Catatan
                        </label>
                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        >{{ old('address', $user->tenantProfile?->address) }}</textarea>
                        @error('address')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a
                    href="{{ route('admin.users.index') }}"
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

    <!-- Modal Konfirmasi Nonaktifkan Akun -->
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
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Nonaktifkan Akun Penyewa</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini memerlukan verifikasi admin</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Penyewa <strong>{{ $user->name }}</strong> tidak akan dapat masuk ke sistem setelah akun dinonaktifkan. Data riwayat keuangan dan kamar tetap tersimpan utuh.
            </p>
            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="modal_deactivate_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Admin Konfirmasi
                    </label>
                    <input
                        id="modal_deactivate_password"
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

    <!-- Modal Konfirmasi Aktifkan Kembali Akun -->
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
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Aktifkan Kembali Akun</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini memerlukan verifikasi admin</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Penyewa <strong>{{ $user->name }}</strong> akan dapat kembali login ke sistem menggunakan kredensial aktifnya.
            </p>
            <form method="POST" action="{{ route('admin.users.reactivate', $user) }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="modal_reactivate_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Admin Konfirmasi
                    </label>
                    <input
                        id="modal_reactivate_password"
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

    <!-- Modal Reset Password Penyewa -->
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
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Reset Password Penyewa</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tentukan kata sandi baru untuk penyewa</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Atur kata sandi baru untuk <strong>{{ $user->name }}</strong>. Masukkan password admin Anda sebagai konfirmasi keamanan.
            </p>
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="new_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Baru Penyewa
                    </label>
                    <input
                        id="new_password"
                        name="new_password"
                        type="password"
                        required
                        placeholder="Minimal 8 karakter"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                </div>
                <div>
                    <label for="new_password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Ulangi Password Baru Penyewa
                    </label>
                    <input
                        id="new_password_confirmation"
                        name="new_password_confirmation"
                        type="password"
                        required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                    />
                </div>
                <div class="border-t border-gray-100 pt-3 dark:border-gray-800">
                    <label for="modal_reset_admin_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password Admin Konfirmasi
                    </label>
                    <input
                        id="modal_reset_admin_password"
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

    <!-- Modal Konfirmasi Hapus Akun Permanen (Phase 14 Lifecycle) -->
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
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Hapus Akun Penyewa Permanen</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tindakan ini memerlukan verifikasi admin</p>
                </div>
            </div>

            @if (! $user->isEligibleForDeletion(7))
                <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">
                    <strong>Belum memenuhi masa tenggang!</strong>
                    <p class="mt-1">Akun harus berstatus nonaktif minimal 7 hari sebelum memenuhi syarat penghapusan (tersisa {{ $user->daysUntilDeletionEligible(7) }} hari lagi).</p>
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
            @elseif ($user->hasFinancialOrOperationalHistory())
                <div class="rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
                    <strong>Akun tidak dapat dihapus!</strong>
                    <p class="mt-1">Penyewa ini memiliki riwayat penugasan kamar atau transaksi finansial di database. Menurut prinsip keutuhan data Smart Kos, histori finansial dan riwayat sewa tidak boleh dimusnahkan.</p>
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
                    Akun <strong>{{ $user->name }}</strong> telah melewati 7 hari masa nonaktif dan tidak memiliki keterikatan catatan finansial. Apakah Anda yakin ingin menghapus akun ini secara permanen?
                </p>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label for="modal_delete_admin_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password Admin Konfirmasi
                        </label>
                        <input
                            id="modal_delete_admin_password"
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

