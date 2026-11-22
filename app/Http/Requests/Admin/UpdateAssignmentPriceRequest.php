<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'rent_amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'payment_cycle' => ['sometimes', 'in:monthly,yearly'],
            'start_date' => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'rent_amount.required' => 'Harga sewa wajib diisi.',
            'rent_amount.numeric' => 'Harga sewa harus berupa angka.',
            'rent_amount.gt' => 'Harga sewa harus lebih besar dari nol.',
            'rent_amount.max' => 'Harga sewa melebihi batas yang diperbolehkan.',
        ];
    }
}
