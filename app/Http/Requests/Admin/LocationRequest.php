<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
            'floor_count' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}