<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use App\Models\TenantProfile;
use Illuminate\Foundation\Http\FormRequest;

class RoomAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'tenant_id'  => ['required', 'exists:tenant_profiles,id'],
            'room_id'    => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $roomId = $this->input('room_id');
            $tenantId = $this->input('tenant_id');

            if ($roomId) {
                $room = Room::find($roomId);
                if ($room) {
                    // Check if room is active
                    if ($room->status !== 'active') {
                        $v->errors()->add('room_id', 'Kamar ini sedang nonaktif dan tidak dapat ditugaskan.');
                    }

                    // Check if room is already occupied by an active assignment
                    $occupied = $room->roomAssignments()
                        ->where('status', 'active')
                        ->when($this->route('assignment'), function ($q, $assignment) {
                            $q->where('id', '!=', $assignment->id);
                        })
                        ->exists();

                    if ($occupied) {
                        $v->errors()->add('room_id', 'Kamar ini sudah ditempati oleh penyewa aktif lain.');
                    }
                }
            }

            if ($tenantId) {
                $tenant = TenantProfile::find($tenantId);
                if ($tenant) {
                    // Check if tenant's user is active
                    if ($tenant->user && $tenant->user->status !== 'active') {
                        $v->errors()->add('tenant_id', 'Akun penyewa ini sedang berstatus nonaktif.');
                    }

                    // Check if tenant already has an active assignment
                    $hasActive = $tenant->roomAssignments()
                        ->where('status', 'active')
                        ->when($this->route('assignment'), function ($q, $assignment) {
                            $q->where('id', '!=', $assignment->id);
                        })
                        ->exists();

                    if ($hasActive && ! $this->route('assignment')) {
                        $v->errors()->add('tenant_id', 'Penyewa ini sudah memiliki kamar aktif. Gunakan fitur pindah kamar untuk memindahkan penyewa.');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Pilih penyewa yang akan ditugaskan ke kamar.',
            'room_id.required'   => 'Pilih kamar yang akan ditempati penyewa.',
            'start_date.required'=> 'Tanggal mulai sewa wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai sewa harus sama atau setelah tanggal mulai.',
        ];
    }
}

