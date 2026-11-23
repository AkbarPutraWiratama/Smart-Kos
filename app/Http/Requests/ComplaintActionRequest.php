<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'staff'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'action_description' => ['required', 'string'],
            'status'             => ['required', 'string', 'in:belum,dalam_perbaikan,selesai'],
            'image'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'cost'               => ['nullable', 'numeric', 'min:0'],
        ];

        if ($this->input('status') === 'selesai') {
            $rules['confirm_selesai'] = ['required', 'accepted'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action_description.required' => 'Deskripsi tindakan penanganan wajib diisi.',
            'status.required'             => 'Status penanganan aduan wajib dipilih.',
            'status.in'                   => 'Status penanganan tidak valid.',
            'image.image'                 => 'Berkas harus berupa gambar.',
            'image.mimes'                 => 'Format gambar harus berupa jpeg, png, jpg, atau webp.',
            'image.max'                   => 'Ukuran gambar maksimal 4 MB.',
            'cost.numeric'                => 'Biaya perbaikan harus berupa angka.',
            'cost.min'                    => 'Biaya perbaikan tidak boleh negatif.',
            'confirm_selesai.required_if' => 'Anda harus memberikan konfirmasi untuk menandai aduan telah selesai.',
            'confirm_selesai.accepted'    => 'Konfirmasi penyelesaian aduan harus disetujui.',
        ];
    }
}
