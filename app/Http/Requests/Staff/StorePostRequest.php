<?php

namespace App\Http\Requests\Staff;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isStaff() && $this->user()->isActive();
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
            'content'     => ['required', 'string'],
            'target_type' => ['required', 'in:all,location,room'],
            'location_id' => ['nullable', 'required_if:target_type,location,room', 'exists:locations,id'],
            'room_id'     => ['nullable', 'required_if:target_type,room', 'exists:rooms,id'],
            'expires_at'  => ['nullable', 'date', 'after:now'],
            'images'      => ['nullable', 'array', 'max:10'],
            'images.*'    => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            // Legacy single image kept for backward compat
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $targetType = $this->input('target_type');
            $locationId = $this->input('location_id');
            $roomId = $this->input('room_id');

            if ($targetType === 'room' && $roomId && $locationId) {
                $room = Room::find($roomId);
                if ($room && (int) $room->location_id !== (int) $locationId) {
                    $validator->errors()->add('room_id', 'Kamar yang dipilih tidak berada di lokasi kos yang ditentukan.');
                }
            }
        });
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'          => 'Judul pengumuman wajib diisi.',
            'title.max'               => 'Judul pengumuman maksimal 255 karakter.',
            'content.required'        => 'Isi pengumuman wajib diisi.',
            'target_type.required'    => 'Target penerima pengumuman wajib dipilih.',
            'target_type.in'          => 'Target penerima pengumuman tidak valid.',
            'location_id.required_if' => 'Lokasi kos wajib dipilih untuk target lokasi atau kamar.',
            'location_id.exists'      => 'Lokasi kos yang dipilih tidak ditemukan.',
            'room_id.required_if'     => 'Kamar wajib dipilih untuk target kamar tertentu.',
            'room_id.exists'          => 'Kamar yang dipilih tidak ditemukan.',
            'expires_at.after'        => 'Tanggal kedaluwarsa harus di masa yang akan datang.',
            'expires_at.date'         => 'Format tanggal kedaluwarsa tidak valid.',
            'images.max'              => 'Maksimal 10 foto yang dapat diunggah sekaligus.',
            'images.*.image'          => 'Setiap lampiran harus berupa gambar.',
            'images.*.mimes'          => 'Format gambar yang didukung adalah JPEG, PNG, JPG, atau WEBP.',
            'images.*.max'            => 'Ukuran setiap gambar maksimal adalah 2MB.',
        ];
    }
}
