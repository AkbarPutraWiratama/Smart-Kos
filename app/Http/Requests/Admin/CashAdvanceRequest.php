<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashAdvanceRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'advance_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:outstanding,partially_repaid,repaid,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'staff_id' => 'staff peminjam',
            'amount' => 'nominal kasbon',
            'advance_date' => 'tanggal kasbon',
            'status' => 'status kasbon',
            'notes' => 'catatan / keperluan',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'staff_id.required' => 'Pilih staff yang mengajukan/menerima kasbon.',
            'staff_id.exists' => 'Akun staff yang dipilih tidak valid.',
            'amount.required' => 'Nominal kasbon wajib diisi.',
            'amount.min' => 'Nominal kasbon minimal Rp 1.',
            'advance_date.required' => 'Tanggal kasbon wajib ditentukan.',
            'status.required' => 'Status kasbon wajib ditentukan.',
            'status.in' => 'Status kasbon yang dipilih tidak valid.',
        ];
    }
}
