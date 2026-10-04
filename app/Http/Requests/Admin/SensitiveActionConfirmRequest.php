<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class SensitiveActionConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            if ($this->filled('password') && ! Hash::check($this->password, $this->user()->password)) {
                $v->errors()->add('password', 'Kata sandi konfirmasi tidak sesuai.');
            }
        });
    }
}
