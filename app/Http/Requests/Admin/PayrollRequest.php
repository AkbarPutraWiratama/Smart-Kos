<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayrollRequest extends FormRequest
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
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'cash_advance_deduction' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:gross_amount',
            ],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'Pilih staf yang akan digaji.',
            'staff_id.exists' => 'Staf yang dipilih tidak valid atau bukan staf kos.',
            'gross_amount.required' => 'Nominal gaji kotor wajib diisi.',
            'gross_amount.numeric' => 'Gaji kotor harus berupa angka.',
            'gross_amount.min' => 'Gaji kotor minimal Rp 0.',
            'cash_advance_deduction.numeric' => 'Potongan kasbon harus berupa angka.',
            'cash_advance_deduction.min' => 'Potongan kasbon minimal Rp 0.',
            'cash_advance_deduction.lte' => 'Potongan kasbon tidak boleh melebihi gaji kotor.',
            'payment_date.required' => 'Tanggal pembayaran gaji wajib diisi.',
            'payment_date.date' => 'Format tanggal pembayaran tidak valid.',
        ];
    }
}
