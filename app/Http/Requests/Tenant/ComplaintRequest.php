<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'penyewa';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'       => 'Judul aduan wajib diisi.',
            'title.max'            => 'Judul aduan maksimal 255 karakter.',
            'description.required' => 'Deskripsi aduan wajib diisi.',
            'image.image'          => 'Berkas harus berupa gambar.',
            'image.mimes'          => 'Format gambar yang diperbolehkan adalah jpeg, png, jpg, atau webp.',
            'image.max'            => 'Ukuran gambar maksimal 4 MB.',
        ];
    }
}
