<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                $user
                    ? Rule::unique('users', 'username')->ignore($user->id)
                    : Rule::unique('users', 'username'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                $user
                    ? Rule::unique('users', 'email')->ignore($user->id)
                    : Rule::unique('users', 'email'),
            ],
            'password' => $user
                ? ['nullable', 'string']           // optional on update
                : ['required', 'string', 'min:8'],  // required on create
            // Tenant profile fields
            'phone'          => ['nullable', 'string', 'max:30'],
            'address'        => ['nullable', 'string', 'max:500'],
            'registered_at'  => ['nullable', 'date'],
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

