@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <x-common.page-breadcrumb
        pageTitle="Edit Pengeluaran"
        :breadcrumbs="[
            ['label' => 'Pengeluaran', 'url' => route('admin.expenses.index')],
        ]"
    />

    <x-common.component-card title="Edit pengeluaran" desc="Perbarui detail pengeluaran yang sudah tercatat.">
        <form method="POST" action="{{ route('admin.expenses.update', $expense) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Title --}}
                <div class="md:col-span-2">
                    <label for="title" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Judul pengeluaran</label>
                    <input id="title" name="title" value="{{ old('title', $expense->title) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('title')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                    <select id="category_id" name="category_id" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 dark:bg-gray-900">
                        @foreach ($categories->groupBy('type') as $type => $cats)
                            <optgroup label="{{ ucfirst($type) }}">
                                @foreach ($cats as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $expense->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Location --}}
                <div>
                    <label for="location_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi <span class="text-gray-400">(opsional)</span></label>
                    <select id="location_id" name="location_id"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90 dark:bg-gray-900">
                        <option value="">Semua lokasi / tidak spesifik</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('location_id', $expense->location_id) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label for="amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
                    <input id="amount" name="amount" type="number" min="1" step="1" value="{{ old('amount', (int) $expense->amount) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('amount')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Date --}}
                <div>
                    <label for="expense_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal pengeluaran</label>
                    <input id="expense_date" name="expense_date" type="date" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">
                    @error('expense_date')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan <span class="text-gray-400">(opsional)</span></label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white/90">{{ old('description', $expense->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Receipt --}}
                <div class="md:col-span-2">
                    <label for="receipt" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Struk / Bukti <span class="text-gray-400">(opsional — kosongkan untuk mempertahankan struk lama)</span></label>
                    @if ($expense->receipt_path)
                        <div class="mb-2 flex items-center gap-2">
                            <a href="{{ Storage::url($expense->receipt_path) }}" target="_blank"
                                class="text-xs font-medium text-brand-500 hover:underline">
                                Lihat struk saat ini →
                            </a>
                        </div>
                    @endif
                    <input id="receipt" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf"
                        class="block w-full text-sm text-gray-700 file:me-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:text-gray-400 dark:file:bg-brand-500/10 dark:file:text-brand-400">
                    @error('receipt')<p class="mt-1 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('admin.expenses.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                    Perbarui Pengeluaran
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>
@endsection
