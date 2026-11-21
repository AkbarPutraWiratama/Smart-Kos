<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display tenant payment status monitoring for operational staff.
     * Strictly hides rental nominal amounts to enforce staff role isolation.
     */
    public function index(Request $request): View
    {
        $locations = Location::where('status', 'active')->orderBy('name')->get();
        $selectedLocationId = $request->input('location_id');
        $statusFilter = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = RoomAssignment::with([
            'room.location',
            'tenantProfile.user',
            'vaPayments' => fn ($q) => $q->latest()->take(1),
            'manualPayments' => fn ($q) => $q->latest('submitted_at')->take(1),
        ])->where('status', 'active');

        if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
            $query->whereHas('room', function ($q) use ($selectedLocationId) {
                $q->where('location_id', $selectedLocationId);
            });
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('tenantProfile.user', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('room', function ($subQ) use ($search) {
                    $subQ->where('room_number', 'like', "%{$search}%");
                });
            });
        }

        $allAssignments = $query->get();

        // Calculate summary metrics across all filtered active tenants
        $totalTenants = $allAssignments->count();
        $paidCount = $allAssignments->filter(fn ($a) => $a->isPaidForCurrentPeriod())->count();
        $pendingVerificationCount = $allAssignments->filter(fn ($a) => $a->hasPendingPayment())->count();
        $urgentCount = $allAssignments->filter(fn ($a) => ! $a->isPaidForCurrentPeriod() && $a->getDaysUntilDue() <= 7)->count();

        // Filter by operational status tab if selected
        $filteredAssignments = $allAssignments->filter(function (RoomAssignment $a) use ($statusFilter) {
            if ($statusFilter === 'paid') {
                return $a->isPaidForCurrentPeriod();
            }
            if ($statusFilter === 'pending') {
                return $a->hasPendingPayment();
            }
            if ($statusFilter === 'urgent') {
                return ! $a->isPaidForCurrentPeriod() && $a->getDaysUntilDue() <= 7;
            }
            if ($statusFilter === 'unpaid') {
                return ! $a->isPaidForCurrentPeriod();
            }
            return true;
        })->sortBy(function (RoomAssignment $assignment) {
            if ($assignment->hasPendingPayment()) {
                return 0;
            }
            if (! $assignment->isPaidForCurrentPeriod()) {
                $days = $assignment->getDaysUntilDue();
                if ($days <= 0) return 1;
                if ($days <= 7) return 2;
                return 3;
            }
            return 4;
        })->values();

        // Paginate manually or pass mapped collections
        $page = (int) $request->input('page', 1);
        $perPage = 15;
        $paginatedAssignments = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredAssignments->forPage($page, $perPage),
            $filteredAssignments->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('staff.payments.index', compact(
            'locations',
            'selectedLocationId',
            'statusFilter',
            'search',
            'totalTenants',
            'paidCount',
            'pendingVerificationCount',
            'urgentCount',
            'paginatedAssignments'
        ));
    }
}
