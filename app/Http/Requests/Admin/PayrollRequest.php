<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'staff');
                }),
            ],
            'gross_amount' => ['required', 'numeric', 'min:1'],
            'cash_advance_deduction' => ['nullable', 'numeric', 'min:0', 'lte:gross_amount'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'settle_advances' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'staff_id' => 'staff penerima',
            'gross_amount' => 'gaji kotor',
            'cash_advance_deduction' => 'potongan kasbon',
            'payment_date' => 'tanggal pembayaran',
            'notes' => 'catatan',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'staff_id.required' => 'Pilih staff penerima gaji.',
            'staff_id.exists' => 'Akun staff yang dipilih tidak valid.',
            'gross_amount.required' => 'Nominal gaji kotor wajib diisi.',
            'gross_amount.min' => 'Nominal gaji kotor minimal Rp 1.',
            'cash_advance_deduction.lte' => 'Potongan kasbon tidak boleh melebihi gaji kotor.',
            'payment_date.required' => 'Tanggal pembayaran wajib ditentukan.',
            'payment_date.date' => 'Format tanggal pembayaran tidak valid.',
        ];
    }
}
