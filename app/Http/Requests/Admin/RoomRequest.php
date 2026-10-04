<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'floor_name' => [
                'required',
                'integer',
                Rule::in(range(1, (int) $this->route('location')->floor_count)),
            ],
            'room_number' => ['required', 'integer', 'min:1', 'max:999999'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('password') && ! Hash::check($this->password, $this->user()->password)) {
                $validator->errors()->add('password', 'Kata sandi konfirmasi tidak sesuai.');
            }
        });
    }
}
