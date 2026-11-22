<?php

namespace App\Http\Requests\Admin;

use App\Models\Location;
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
            'google_maps_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:4096'],
            'room_photos' => ['nullable', 'array', 'max:10'],
            'room_photos.*' => ['image', 'max:4096'],
            'floor_count' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('google_maps_url') && is_string($this->google_maps_url)) {
            $url = trim($this->google_maps_url);

            // 1. If user pastes full <iframe> tag from Google Maps 'Sematkan peta', extract src
            if (preg_match('/src=["\']([^"\']+)["\']/', $url, $matches)) {
                $url = $matches[1];
            }

            // 2. If user pastes raw coordinates (e.g. -7.782812, 110.367015), convert to Google Maps URL
            if (preg_match('/^-?\d+\.\d+[\s,]+-?\d+\.\d+$/', $url)) {
                $url = 'https://maps.google.com/?q=' . urlencode(preg_replace('/\s+/', '', $url));
            }

            $this->merge(['google_maps_url' => $url ?: null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $location = $this->route('location');
            if (! ($location instanceof Location) && is_numeric($location)) {
                $location = Location::find($location);
            }

            // Invariant: If location has occupied rooms / active tenants, status cannot be changed to inactive
            if ($location && $location->hasOccupiedRooms() && $this->input('status') === 'inactive') {
                $validator->errors()->add('status', 'Status lokasi tidak dapat diubah menjadi nonaktif karena masih ada kamar yang sedang ditempati penyewa aktif.');
            }
        });
    }
}
