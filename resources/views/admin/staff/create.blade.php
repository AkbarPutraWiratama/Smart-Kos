@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb
        pageTitle="Tambah Staff"
        :breadcrumbs="[
            ['label' => 'Pelanggan & Staff', 'url' => route('admin.staff.index')],
        ]"
    />

    <x-common.component-card
        title="Buat Akun Staff Baru"
        desc="Lengkapi data akun login untuk staff operasional kos. Akun akan langsung aktif setelah disimpan."
    >
        <form method="POST" action="{{ route('admin.staff.store') }}" class="space-y-6">
            @csrf

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Kredensial Akun Staff
                </h4>
                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Lengkap Staff <span class="text-error-500">*</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            placeholder="Contoh: Andi Wijaya"
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
                            placeholder="Contoh: andi_staff"
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
                            placeholder="andi@example.com"
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

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a
                    href="{{ route('admin.staff.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 transition"
                >
                    Simpan Akun Staff
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>
@endsection

