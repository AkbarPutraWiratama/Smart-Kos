<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomViewController extends Controller
{
    /**
     * Display public location list and room availability.
     * Invariant: Never expose tenant names, payment history, or private data.
     */
    public function index(Request $request): View
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

        $selectedLocation = null;
        $roomsByFloor = collect();

        if ($selectedLocationId) {
            $selectedLocation = Location::where('id', $selectedLocationId)
                ->where('status', 'active')
                ->with('photos')
                ->first();
        } elseif ($locations->isNotEmpty()) {
            $selectedLocation = $locations->first();
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
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'floor_name' => $room->floor_name,
                        'rent_amount' => $room->rent_amount,
                        'is_occupied' => $room->activeAssignment !== null,
                    ];
                });

            $roomsByFloor = $rooms->groupBy('floor_name');
        }

        return view('guest.rooms', [
            'title' => 'Informasi Lokasi & Ketersediaan Kamar — Smart Kos',
            'locations' => $locations,
            'selectedLocation' => $selectedLocation,
            'roomsByFloor' => $roomsByFloor,
        ]);
    }
}
