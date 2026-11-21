<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'category_id'  => ['required', 'exists:expense_categories,id'],
            'location_id'  => ['nullable', 'exists:locations,id'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'receipt'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'title.required'        => 'Judul pengeluaran wajib diisi.',
            'category_id.required'  => 'Kategori pengeluaran wajib dipilih.',
            'category_id.exists'    => 'Kategori pengeluaran tidak valid.',
            'location_id.exists'    => 'Lokasi kos yang dipilih tidak valid.',
            'amount.required'       => 'Nominal pengeluaran wajib diisi.',
            'amount.numeric'        => 'Nominal pengeluaran harus berupa angka.',
            'amount.min'            => 'Nominal pengeluaran minimal Rp 1.',
            'expense_date.required' => 'Tanggal pengeluaran wajib diisi.',
            'expense_date.date'     => 'Format tanggal pengeluaran tidak valid.',
            'receipt.mimes'         => 'Bukti nota/kwitansi harus berformat JPG, JPEG, PNG, atau PDF.',
            'receipt.max'           => 'Ukuran bukti nota/kwitansi maksimal 5 MB.',
        ];
    }
}
