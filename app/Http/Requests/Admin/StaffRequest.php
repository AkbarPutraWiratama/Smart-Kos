<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        $staff = $this->route('staff');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                $staff
                    ? Rule::unique('users', 'username')->ignore($staff->id)
                    : Rule::unique('users', 'username'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                $staff
                    ? Rule::unique('users', 'email')->ignore($staff->id)
                    : Rule::unique('users', 'email'),
            ],
            'password' => $staff
                ? ['nullable', 'string']           // optional on update
                : ['required', 'string', 'min:8'],  // required on create
        ];
    }

    public function messages(): array
    {
        return [
            'username.alpha_dash' => 'Username hanya boleh mengandung huruf, angka, tanda hubung, dan garis bawah.',
            'password.min'        => 'Password minimal 8 karakter.',
        ];
    }
}

