<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'amount'      => ['required', 'numeric', 'min:1', 'max:999999999'],
            'expense_date'=> ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'receipt'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Judul pengeluaran wajib diisi.',
            'title.max'            => 'Judul maksimal 255 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'amount.required'      => 'Jumlah wajib diisi.',
            'amount.min'           => 'Jumlah minimal Rp 1.',
            'expense_date.required'=> 'Tanggal pengeluaran wajib diisi.',
            'expense_date.date'    => 'Format tanggal tidak valid.',
            'receipt.mimes'        => 'Struk hanya boleh JPG, PNG, atau PDF.',
            'receipt.max'          => 'Ukuran struk maksimal 4 MB.',
        ];
    }
}
