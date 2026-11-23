<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArrearsController extends Controller
{
    /**
     * Display tenant receivables and arrears list (Tunggakan Sewa).
     * Automatically sorted from most overdue to least overdue.
     */
    public function index(Request $request): View
    {
        $locationId = $request->query('location_id');
        $search = $request->query('search');

        $query = RoomAssignment::with([
            'room.location',
            'tenantProfile.user',
            'vaPayments',
            'manualPayments',
        ])->where('status', 'active');

        if ($locationId) {
            $query->whereHas('room', fn ($q) => $q->where('location_id', $locationId));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('tenantProfile.user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('room', function ($rq) use ($search) {
                    $rq->where('room_number', 'like', "%{$search}%");
                });
            });
        }

        $allActive = $query->get();

        // Filter and map only active room assignments that are overdue
        $arrearsList = $allActive->filter(function (RoomAssignment $assignment) {
            return ! $assignment->isPaidForCurrentPeriod()
                && ! $assignment->hasPendingPayment()
                && $assignment->getDaysUntilDue() < 0;
        })->map(function (RoomAssignment $assignment) {
            $dueDate = $assignment->getPaymentDueDate();
            $daysOverdue = abs($assignment->getDaysUntilDue());
            $amount = (float) ($assignment->rent_amount_snapshot ?? $assignment->room?->price ?? 0);

            return [
                'assignment'         => $assignment,
                'tenant_name'        => $assignment->tenantProfile?->user?->name ?? 'Penyewa',
                'tenant_phone'       => $assignment->tenantProfile?->user?->phone ?? '',
                'tenant_email'       => $assignment->tenantProfile?->user?->email ?? '',
                'location_name'      => $assignment->room?->location?->name ?? '—',
                'room_number'        => $assignment->room?->room_number ?? '—',
                'payment_due_date'   => $dueDate,
                'days_overdue'       => $daysOverdue,
                'outstanding_amount' => $amount,
                'payment_cycle'      => $assignment->payment_cycle ?? 'monthly',
            ];
        })
        // Sort automatically: most overdue -> least overdue
        ->sortByDesc('days_overdue')
        ->values();

        // Calculate KPI metrics at application layer
        $totalArrearsAmount = (float) $arrearsList->sum('outstanding_amount');
        $totalOverdueTenants = $arrearsList->count();
        $maxDaysOverdue = $arrearsList->max('days_overdue') ?? 0;
        $avgDaysOverdue = $totalOverdueTenants > 0 ? round($arrearsList->avg('days_overdue'), 1) : 0.0;

        $locations = Location::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.arrears.arrears-hub', [
            'arrearsList'         => $arrearsList,
            'totalArrearsAmount'  => $totalArrearsAmount,
            'totalOverdueTenants' => $totalOverdueTenants,
            'maxDaysOverdue'      => $maxDaysOverdue,
            'avgDaysOverdue'      => $avgDaysOverdue,
            'locations'           => $locations,
            'selectedLocationId'  => $locationId,
            'search'              => $search,
        ]);
    }
}
