<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Post;
use App\Models\Room;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the operational staff dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Retrieve all locations for filter tabs and target selection
        $locations = Location::orderBy('name')->get();

        // Retrieve rooms for announcement room-targeting selector
        $allRooms = Room::with('location')
            ->where('status', 'active')
            ->orderBy('room_number')
            ->get();

        // Location filter: 'all' or specific location ID
        $locationFilter = $request->input('location_filter', 'all');
        $selectedLocation = null;
        if ($locationFilter !== 'all' && is_numeric($locationFilter)) {
            $selectedLocation = $locations->firstWhere('id', (int) $locationFilter);
            if (! $selectedLocation) {
                $locationFilter = 'all';
            }
        } else {
            $locationFilter = 'all';
        }

        // Query active room assignments for payment deadlines
        $assignmentsQuery = RoomAssignment::with([
            'room.location',
            'tenantProfile.user',
            'vaPayments',
            'manualPayments',
        ])->where('status', 'active');

        if ($selectedLocation) {
            $assignmentsQuery->whereHas('room', function ($q) use ($selectedLocation) {
                $q->where('location_id', $selectedLocation->id);
            });
        }

        $assignments = $assignmentsQuery->get()->sortBy(function (RoomAssignment $assignment) {
            $isPaid = $assignment->isPaidForCurrentPeriod();
            $hasPending = $assignment->hasPendingPayment();
            $days = $assignment->getDaysUntilDue();

            if ($hasPending) {
                return 0; // Menunggu verifikasi
            }
            if (! $isPaid) {
                if ($days <= 0) {
                    return 1; // Jatuh tempo hari ini / menunggak
                }
                if ($days <= 7) {
                    return 2; // Mendekati jatuh tempo (H-7)
                }
                return 3; // Belum bayar (> 7 hari)
            }
            return 4; // Sudah lunas
        })->values();

        // Operational metrics
        $totalOccupiedRooms = $assignments->count();
        $urgentPaymentsCount = $assignments->filter(function (RoomAssignment $a) {
            return ! $a->isPaidForCurrentPeriod() && $a->getDaysUntilDue() <= 7;
        })->count();
        $pendingVerificationCount = $assignments->filter(function (RoomAssignment $a) {
            return $a->hasPendingPayment();
        })->count();

        // Query announcements targeted to 'all' or the selected location
        $postsQuery = Post::with(['author', 'targets.location', 'targets.room'])
            ->where('status', 'published');

        if ($selectedLocation) {
            $locId = $selectedLocation->id;
            $postsQuery->whereHas('targets', function ($q) use ($locId) {
                $q->where('target_type', 'all')
                    ->orWhere(function ($sub) use ($locId) {
                        $sub->where('target_type', 'location')
                            ->where('location_id', $locId);
                    });
            });
        }

        $announcements = $postsQuery->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        $activeAnnouncementsCount = $announcements->count();

        return view('staff.dashboard', [
            'user'                     => $user,
            'locations'                => $locations,
            'allRooms'                 => $allRooms,
            'selectedLocation'         => $selectedLocation,
            'locationFilter'           => $locationFilter,
            'assignments'              => $assignments,
            'announcements'            => $announcements,
            'totalOccupiedRooms'       => $totalOccupiedRooms,
            'urgentPaymentsCount'      => $urgentPaymentsCount,
            'pendingVerificationCount' => $pendingVerificationCount,
            'activeAnnouncementsCount' => $activeAnnouncementsCount,
        ]);
    }
}

