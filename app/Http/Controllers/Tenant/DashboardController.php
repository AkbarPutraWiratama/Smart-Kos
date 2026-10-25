<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the tenant dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $tenantProfile = $user->tenantProfile;

        // Fetch active assignment strictly belonging to this tenant
        $activeAssignment = $tenantProfile?->roomAssignments()
            ->where('status', 'active')
            ->with(['room.location', 'vaPayments', 'manualPayments'])
            ->latest('start_date')
            ->first();

        // Location announcements filter: 'relevant' | 'all' | 'specific'
        $locationFilter = $request->input('location_filter', 'relevant');
        $locationId = $activeAssignment?->room?->location_id;
        $roomId = $activeAssignment?->room_id;

        $postsQuery = Post::with(['author', 'targets.location', 'targets.room'])
            ->where('status', 'published');

        if ($locationFilter === 'all') {
            // Only global announcements
            $postsQuery->whereHas('targets', function ($q) {
                $q->where('target_type', 'all');
            });
        } elseif ($locationFilter === 'specific') {
            // Announcements specifically targeting this tenant's location or room
            if ($locationId) {
                $postsQuery->whereHas('targets', function ($q) use ($locationId, $roomId) {
                    $q->where(function ($sub) use ($locationId) {
                        $sub->where('target_type', 'location')
                            ->where('location_id', $locationId);
                    })->orWhere(function ($sub) use ($roomId) {
                        $sub->where('target_type', 'room')
                            ->where('room_id', $roomId);
                    });
                });
            } else {
                // If unassigned, no specific location exists
                $postsQuery->whereRaw('1 = 0');
            }
        } else {
            // 'relevant' default: All global announcements + this tenant's location and room
            $postsQuery->whereHas('targets', function ($q) use ($locationId, $roomId) {
                $q->where('target_type', 'all');
                if ($locationId) {
                    $q->orWhere(function ($sub) use ($locationId) {
                        $sub->where('target_type', 'location')
                            ->where('location_id', $locationId);
                    });
                }
                if ($roomId) {
                    $q->orWhere(function ($sub) use ($roomId) {
                        $sub->where('target_type', 'room')
                            ->where('room_id', $roomId);
                    });
                }
            });
        }

        $announcements = $postsQuery->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('penyewa.dashboard', [
            'user'             => $user,
            'activeAssignment' => $activeAssignment,
            'announcements'    => $announcements,
            'locationFilter'   => $locationFilter,
            'assignedLocation' => $activeAssignment?->room?->location,
        ]);
    }
}

