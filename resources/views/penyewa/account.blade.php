@extends('layouts.app')

@section('content')
<div class="space-y-6 w-full max-w-full min-w-0">
    <x-common.page-breadcrumb pageTitle="Pengaturan Akun Penyewa" />

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            class="flex items-center justify-between rounded-xl border border-success-200 bg-success-50 p-4 text-sm text-success-800 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400"
        >
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success-500 text-white">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                    </svg>
                </span>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            <button type="button" @click="show = false" class="text-success-600 hover:text-success-800 dark:text-success-400">
                &times;
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            class="flex items-start justify-between rounded-xl border border-error-200 bg-error-50 p-4 text-sm text-error-800 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-400"
        >
            <div class="flex items-start gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-error-500 text-white mt-0.5">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </span>
                <div>
                    <p class="font-semibold">Terdapat kesalahan dalam pengisian formulir:</p>
                    <ul class="mt-1 list-disc ps-5 space-y-0.5 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" @click="show = false" class="text-error-600 hover:text-error-800 dark:text-error-400">
                &times;
            </button>
        </div>
    @endif

    {{-- Ringkasan Identitas & Status Hunian (Read-Only) --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-50 font-bold text-xl text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 shadow-theme-xs">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-lg font-bold text-gray-800 dark:text-white/90">
                            {{ $user->name }}
                        </h2>
                        <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-400">
                            Akun Aktif
                        </span>
                        <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                            Penyewa Kos
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ '@' . $user->username }} &bull; Terdaftar sejak {{ $tenantProfile?->registered_at ? $tenantProfile->registered_at->format('d M Y') : $user->created_at->format('d M Y') }}
                    </p>
                </div>
            </div>

            {{-- Status Kamar Saat Ini --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 dark:border-gray-800 dark:bg-gray-800/40 text-xs sm:text-end">
                <p class="text-gray-500 dark:text-gray-400">Penempatan Kamar Saat Ini:</p>
                @if ($activeAssignment)
                    <p class="font-bold text-sm text-gray-800 dark:text-white/90 mt-0.5">
                        Kamar {{ $activeAssignment->room?->room_number }} &bull; Lantai {{ $activeAssignment->room?->floor_name }}
                    </p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $activeAssignment->room?->location?->name }} &bull; Rp {{ number_format($activeAssignment->rent_amount_snapshot, 0, ',', '.') }}/bulan
                    </p>
                @else
                    <span class="inline-block mt-1 rounded-md bg-warning-50 px-2 py-0.5 font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                        Belum Ditempatkan ke Kamar
                    </span>
                @endif
            </div>
        </div>

        <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800 text-[11px] text-gray-400 flex items-center gap-1.5">
            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
            </svg>
            <span>Informasi peran akun, status keaktifan, kamar, dan tarif sewa dikelola secara eksklusif oleh Pengelola (Admin) Kos.</span>
        </div>
    </div>

    {{-- Grid 2 Kolom Formulir --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- FORMULIR 1: Data Diri & Kontak --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 border-b border-gray-100 pb-4 dark:border-gray-800">
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Informasi Data Diri & Kontak
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Perbarui nama lengkap, kontak handphone, email, dan alamat domisili Anda.
                </p>
            </div>

            <form method="POST" action="{{ route('penyewa.account.profile') }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Nama Lengkap --}}
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
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 @error('name') border-error-500 @enderror"
                        placeholder="Masukkan nama lengkap Anda"
                    />
                    @error('name')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Username (Read-Only) --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Pengguna (Username)
                    </label>
                    <input
                        type="text"
                        value="{{ $user->username }}"
                        disabled
                        class="h-11 w-full rounded-lg border border-gray-200 bg-gray-100 px-4 text-sm text-gray-500 cursor-not-allowed dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400"
                    />
                    <p class="mt-1 text-[11px] text-gray-400">Username akun bersifat permanen dan tidak dapat diubah.</p>
                </div>

                {{-- Nomor Handphone / WhatsApp --}}
                <div>
                    <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nomor Handphone / WhatsApp
                    </label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone', $user->phone ?? $tenantProfile?->phone) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 @error('phone') border-error-500 @enderror"
                        placeholder="Contoh: 081234567890"
                    />
                    @error('phone')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[11px] text-gray-400">Digunakan untuk konfirmasi pembayaran dan kontak darurat pihak kos.</p>
                </div>

                {{-- Alamat Email --}}
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Alamat Email (Opsional)
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user->email) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 @error('email') border-error-500 @enderror"
                        placeholder="alamat.email@contoh.com"
                    />
                    @error('email')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat Domisili Asal --}}
                <div>
                    <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Alamat Domisili Asal (KTP)
                    </label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-transparent p-3.5 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 @error('address') border-error-500 @enderror"
                        placeholder="Alamat asal tempat tinggal sesuai identitas KTP"
                    >{{ old('address', $tenantProfile?->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-6 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 active:scale-95 transition"
                    >
                        Simpan Perubahan Data Diri
                    </button>
                </div>
            </form>
        </div>

        {{-- FORMULIR 2: Keamanan & Kata Sandi --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 border-b border-gray-100 pb-4 dark:border-gray-800">
                <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                    Keamanan & Kata Sandi
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('penyewa.account.password') }}"
                class="space-y-4"
                x-data="{ showCurrent: false, showNew: false, showConfirm: false }"
            >
                @csrf
                @method('PUT')

                {{-- Kata Sandi Saat Ini --}}
                <div>
                    <label for="current_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kata Sandi Saat Ini <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="current_password"
                            name="current_password"
                            :type="showCurrent ? 'text' : 'password'"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent ps-4 pe-11 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 @error('current_password') border-error-500 @enderror"
                            placeholder="Masukkan kata sandi saat ini"
                        />
                        <button
                            type="button"
                            @click="showCurrent = !showCurrent"
                            class="absolute end-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showCurrent" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            <svg x-show="showCurrent" x-cloak class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.375l1.091 1.091a4 4 0 00-5.557-5.557z" clip-rule="evenodd" />
                                <path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 014.168 6.608L6.803 9.243a4 4 0 004.945 4.945l-1 1.002z" />
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kata Sandi Baru --}}
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Kata Sandi Baru <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            :type="showNew ? 'text' : 'password'"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent ps-4 pe-11 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 @error('password') border-error-500 @enderror"
                            placeholder="Minimal 8 karakter"
                        />
                        <button
                            type="button"
                            @click="showNew = !showNew"
                            class="absolute end-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showNew" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            <svg x-show="showNew" x-cloak class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.375l1.091 1.091a4 4 0 00-5.557-5.557z" clip-rule="evenodd" />
                                <path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 014.168 6.608L6.803 9.243a4 4 0 004.945 4.945l-1 1.002z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Kata Sandi Baru --}}
                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Ulangi Kata Sandi Baru <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent ps-4 pe-11 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                            placeholder="Ulangi kata sandi baru"
                        />
                        <button
                            type="button"
                            @click="showConfirm = !showConfirm"
                            class="absolute end-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none"
                            tabindex="-1"
                        >
                            <svg x-show="!showConfirm" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            <svg x-show="showConfirm" x-cloak class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.375l1.091 1.091a4 4 0 00-5.557-5.557z" clip-rule="evenodd" />
                                <path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 014.168 6.608L6.803 9.243a4 4 0 004.945 4.945l-1 1.002z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-6 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 active:scale-95 transition"
                    >
                        Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
