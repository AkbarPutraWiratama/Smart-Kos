@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb
        pageTitle="Tambah Penyewa"
        :breadcrumbs="[
            ['label' => 'Pelanggan & Staff', 'url' => route('admin.users.index')],
        ]"
    />

    <x-common.component-card
        title="Buat Akun Penyewa Baru"
        desc="Lengkapi data akun dan profil penyewa. Penugasan kamar dapat dilakukan setelah akun dibuat pada menu Status Penyewa."
    >
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Kredensial Akun -->
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Kredensial Akun (Login)
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
                            value="{{ old('name') }}"
                            required
                            placeholder="Contoh: Budi Santoso"
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
                            value="{{ old('username') }}"
                            required
                            placeholder="Contoh: budi_santoso"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                        @error('username')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="budi@example.com"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                        @error('email')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Kata Sandi Awal <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        />
                        @error('password')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Data Profil Penyewa -->
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
                            value="{{ old('phone') }}"
                            placeholder="08xxxxxxxxxx"
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
                            value="{{ old('registered_at', date('Y-m-d')) }}"
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
                            placeholder="Alamat KTP atau domisili asal..."
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90"
                        >{{ old('address') }}</textarea>
                        @error('address')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a
                    href="{{ route('admin.users.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
                >
                    Simpan Akun Penyewa
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>
@endsection

