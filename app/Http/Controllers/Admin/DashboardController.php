<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Post;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // All locations for context
        $locations = Location::orderBy('name')->get();

        // All active rooms for announcement targeting modal
        $allRooms = Room::with('location')
            ->where('status', 'active')
            ->orderBy('room_number')
            ->get();

        // ── Room Metrics ──────────────────────────────────────────────────────
        $totalRooms = Room::where('status', 'active')->count();

        $occupiedRoomIds = RoomAssignment::where('status', 'active')
            ->pluck('room_id')
            ->unique();

        $occupiedRooms = $occupiedRoomIds->count();
        $emptyRooms    = max(0, $totalRooms - $occupiedRooms);

        // ── Tenant Metrics ────────────────────────────────────────────────────
        $totalActiveTenants = RoomAssignment::where('status', 'active')->count();

        // ── Pending Manual Payment Verifications ──────────────────────────────
        $pendingVerificationsCount = ManualPayment::where('status', 'pending')->count();

        // ── Payment Deadlines (all active assignments, admin sees rent amounts) ─
        $assignments = RoomAssignment::with([
            'room.location',
            'tenantProfile.user',
            'vaPayments',
            'manualPayments',
        ])->where('status', 'active')
            ->get()
            ->sortBy(function (RoomAssignment $assignment) {
                $hasPending = $assignment->hasPendingPayment();
                $isPaid     = $assignment->isPaidForCurrentPeriod();
                $days       = $assignment->getDaysUntilDue();

                if ($hasPending) {
                    return 0; // Pending verification — highest priority
                }
                if (! $isPaid) {
                    if ($days <= 0) {
                        return 1; // Overdue / due today
                    }
                    if ($days <= 7) {
                        return 2; // Approaching deadline
                    }

                    return 3; // Not yet paid, > 7 days
                }

                return 4; // Paid
            })->values();

        // Computed metrics from assignments collection
        $urgentPaymentsCount = $assignments->filter(function (RoomAssignment $a) {
            return ! $a->isPaidForCurrentPeriod() && $a->getDaysUntilDue() <= 7;
        })->count();

        // ── Financial Summary (current month, from financial_records) ──────────
        $now               = now();
        $monthStart        = $now->copy()->startOfMonth();
        $monthEnd          = $now->copy()->endOfMonth();

        $totalIncomeMonth = FinancialRecord::where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $totalExpenseMonth = FinancialRecord::where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $netIncomeMonth = (float) $totalIncomeMonth - (float) $totalExpenseMonth;

        // ── Announcements (all published posts) ───────────────────────────────
        $announcements = Post::with(['author', 'targets.location', 'targets.room'])
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        $activeAnnouncementsCount = $announcements->count();

        return view('admin.dashboard', [
            'user'                      => $user,
            'locations'                 => $locations,
            'allRooms'                  => $allRooms,
            'totalRooms'                => $totalRooms,
            'occupiedRooms'             => $occupiedRooms,
            'emptyRooms'                => $emptyRooms,
            'totalActiveTenants'        => $totalActiveTenants,
            'pendingVerificationsCount' => $pendingVerificationsCount,
            'urgentPaymentsCount'       => $urgentPaymentsCount,
            'assignments'               => $assignments,
            'announcements'             => $announcements,
            'activeAnnouncementsCount'  => $activeAnnouncementsCount,
            'totalIncomeMonth'          => $totalIncomeMonth,
            'totalExpenseMonth'         => $totalExpenseMonth,
            'netIncomeMonth'            => $netIncomeMonth,
        ]);
    }
}

