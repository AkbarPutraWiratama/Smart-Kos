<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomViewController extends Controller
{
    /**
     * Display public location list and room availability.
     * Invariant: Never expose tenant names, payment history, or private data.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $selectedLocationId = $request->query('location_id');

        $locations = Location::where('status', 'active')
            ->with('photos')
            ->withCount([
                'rooms as total_rooms' => function ($query) {
                    $query->where('status', 'active');
                },
                'rooms as occupied_rooms' => function ($query) {
                    $query->where('status', 'active')
                        ->whereHas('roomAssignments', function ($subQuery) {
                            $subQuery->where('status', 'active');
                        });
                },
            ])
            ->get();

        // Single-location shortcut: redirect so the URL always carries location_id,
        // which also collapses the location-tab UI in the view.
        if ($locations->count() === 1 && ! $selectedLocationId) {
            return redirect()->route('guest.rooms', ['location_id' => $locations->first()->id]);
        }

        $selectedLocation = null;
        $roomsByFloor     = collect();
        $mapEmbedUrl      = null;

        if ($selectedLocationId) {
            $selectedLocation = Location::where('id', $selectedLocationId)
                ->where('status', 'active')
                ->with('photos')
                ->first();
        } elseif ($locations->isNotEmpty()) {
            $selectedLocation = $locations->first();
            $selectedLocation->load('photos');
        }

        if ($selectedLocation) {
            $rooms = $selectedLocation->rooms()
                ->where('status', 'active')
                ->with(['activeAssignment'])
                ->orderBy('floor_name')
                ->orderBy('room_number')
                ->get()
                ->map(function ($room) {
                    return [
                        'id'          => $room->id,
                        'room_number' => $room->room_number,
                        'floor_name'  => $room->floor_name,
                        'rent_amount' => $room->rent_amount,
                        'is_occupied' => $room->activeAssignment !== null,
                    ];
                });

            $roomsByFloor = $rooms->groupBy('floor_name');
            $mapEmbedUrl  = $this->resolveMapEmbedUrl(
                $selectedLocation->google_maps_url,
                $selectedLocation->address
            );
        }

        return view('guest.rooms', [
            'title'            => 'Informasi Lokasi & Ketersediaan Kamar – Smart Kos',
            'locations'        => $locations,
            'selectedLocation' => $selectedLocation,
            'roomsByFloor'     => $roomsByFloor,
            'mapEmbedUrl'      => $mapEmbedUrl,
        ]);
    }

    /**
     * Convert any Google Maps URL (share link, place URL, coordinates) to an
     * embeddable src URL for <iframe>.  Falls back to an address-query embed.
     *
     * Supported inputs:
     *  - https://www.google.com/maps/embed?pb=…  → used as-is
     *  - https://maps.google.com/?q=…             → q= extracted
     *  - https://www.google.com/maps/place/…/@lat,lng → coords extracted
     *  - https://goo.gl/maps/…                    → address fallback
     *  - null / empty / other                     → address fallback
     */
    private function resolveMapEmbedUrl(?string $url, string $fallbackAddress): string
    {
        // Already an embed URL — use as-is.
        if ($url && str_contains($url, 'maps/embed')) {
            return $url;
        }

        // Any Google Maps share / place / directions URL — convert to embed.
        if ($url && (str_contains($url, 'maps.google') || str_contains($url, 'google.com/maps') || str_contains($url, 'goo.gl'))) {
            $parsed = parse_url($url);
            parse_str($parsed['query'] ?? '', $qs);

            // Prefer explicit q= param.
            if (! empty($qs['q'])) {
                $query = $qs['q'];
            }
            // Support query= param as used in search API
            elseif (! empty($qs['query'])) {
                $query = $qs['query'];
            }
            // Extract lat,lng from place URL /@lat,lng,zoom syntax.
            elseif (preg_match('~/@(-?\d+\.\d+),(-?\d+\.\d+)~', $url, $m)) {
                $query = $m[1] . ',' . $m[2];
            }
            // Extract place name from /place/Name/ URL segment.
            elseif (preg_match('~/place/([^/@?]+)~', $url, $m)) {
                $query = urldecode(str_replace('+', ' ', $m[1]));
            }
            // Last resort: use the address.
            else {
                $query = $fallbackAddress;
            }

            return 'https://maps.google.com/maps?q=' . urlencode($query) . '&output=embed&hl=id';
        }

        // Fallback: embed by address string.
        return 'https://maps.google.com/maps?q=' . urlencode($fallbackAddress) . '&output=embed&hl=id';
    }
}
