<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Display the public guest landing page.
     */
    public function index(): View
    {
        $locationsCount = Location::where('status', 'active')->count();
        $totalRooms = Room::where('status', 'active')->count();
        $occupiedRooms = Room::where('status', 'active')
            ->whereHas('roomAssignments', function ($query) {
                $query->where('status', 'active');
            })->count();
        $availableRooms = max(0, $totalRooms - $occupiedRooms);

        // Fetch contacts for staff / owner if available
        $staffContacts = User::where('role', 'staff')
            ->where('status', 'active')
            ->select('name', 'email')
            ->take(3)
            ->get();

        $ownerContact = User::where('role', 'admin')
            ->where('status', 'active')
            ->select('name', 'email')
            ->first();

        return view('guest.landing', [
            'title' => 'Selamat Datang di Smart Kos',
            'locationsCount' => $locationsCount,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'staffContacts' => $staffContacts,
            'ownerContact' => $ownerContact,
        ]);
    }
}
