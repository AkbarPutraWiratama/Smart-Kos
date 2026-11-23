<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'staff')),
            ],
            'amount' => ['required', 'numeric', 'min:1'],
            'advance_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'Pilih staf penerima kasbon.',
            'staff_id.exists' => 'Staf yang dipilih tidak valid atau bukan staf kos.',
            'amount.required' => 'Nominal kasbon wajib diisi.',
            'amount.numeric' => 'Nominal kasbon harus berupa angka.',
            'amount.min' => 'Nominal kasbon minimal Rp 1.',
            'advance_date.required' => 'Tanggal kasbon wajib diisi.',
            'advance_date.date' => 'Format tanggal kasbon tidak valid.',
        ];
    }
}
