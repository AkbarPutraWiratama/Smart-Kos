<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Services\FinancialRecordSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialRecapController extends Controller
{
    /**
     * Display the financial recap hub (Rekapitulasi Keuangan).
     * Supports switching between Tenant recap and Room recap.
     * Calculates: total payments, total outstanding, total expenses, net amount.
     */
    public function index(Request $request): View
    {
        // Ensure financial records are synchronized
        FinancialRecordSyncService::syncIfNeeded();

        $viewMode = $request->query('view', 'tenant');
        if (!in_array($viewMode, ['tenant', 'room'])) {
            $viewMode = 'tenant';
        }

        $locationId = $request->query('location_id');
        $search = $request->query('search');

        // 1. Overall System Financial Metrics (Application Layer)
        $incomeQuery = FinancialRecord::where('type', 'income');
        $expenseQuery = FinancialRecord::where('type', 'expense');

        if ($locationId) {
            $incomeQuery->where('location_id', $locationId);
            $expenseQuery->where('location_id', $locationId);
        }

        $globalTotalPayments = (float) $incomeQuery->sum('amount');
        $globalTotalExpenses = (float) $expenseQuery->sum('amount');
        $globalNetAmount = $globalTotalPayments - $globalTotalExpenses;

        // Calculate global outstanding receivables from active overdue assignments
        $activeAssignmentsQuery = RoomAssignment::with([
            'room.location',
            'tenantProfile.user',
            'vaPayments',
            'manualPayments',
        ])->where('status', 'active');

        if ($locationId) {
            $activeAssignmentsQuery->whereHas('room', fn ($q) => $q->where('location_id', $locationId));
        }

        $allActiveAssignments = $activeAssignmentsQuery->get();

        $globalTotalOutstanding = (float) $allActiveAssignments->filter(function (RoomAssignment $assignment) {
            return ! $assignment->isPaidForCurrentPeriod()
                && ! $assignment->hasPendingPayment()
                && $assignment->getDaysUntilDue() < 0;
        })->sum(function (RoomAssignment $assignment) {
            return (float) ($assignment->rent_amount_snapshot ?? $assignment->room?->rent_amount ?? 0);
        });

        $locations = Location::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $tenantRecap = collect();
        $roomRecap = collect();

        if ($viewMode === 'tenant') {
            // 2. Tenant Financial Recap
            $tenantQuery = TenantProfile::with([
                'user',
                'roomAssignments.room.location',
                'roomAssignments.vaPayments',
                'roomAssignments.manualPayments',
            ]);

            if ($locationId) {
                $tenantQuery->whereHas('roomAssignments.room', fn ($q) => $q->where('location_id', $locationId));
            }

            if ($search) {
                $tenantQuery->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $tenants = $tenantQuery->get();

            $tenantRecap = $tenants->map(function (TenantProfile $profile) {
                $user = $profile->user;
                $assignments = $profile->roomAssignments;

                $activeAssignment = $assignments->firstWhere('status', 'active');

                // Total Payments from all settled VA payments and approved Manual payments
                $vaSum = (float) $assignments->flatMap->vaPayments
                    ->whereIn('transaction_status', ['settlement', 'paid', 'capture', 'success'])
                    ->sum('gross_amount');

                $manualSum = (float) $assignments->flatMap->manualPayments
                    ->where('status', 'approved')
                    ->sum('amount');

                $totalPayments = $vaSum + $manualSum;

                // Total Outstanding
                $totalOutstanding = 0.0;
                $daysOverdue = 0;
                if ($activeAssignment && ! $activeAssignment->isPaidForCurrentPeriod() && ! $activeAssignment->hasPendingPayment() && $activeAssignment->getDaysUntilDue() < 0) {
                    $totalOutstanding = (float) ($activeAssignment->rent_amount_snapshot ?? $activeAssignment->room?->rent_amount ?? 0);
                    $daysOverdue = abs($activeAssignment->getDaysUntilDue());
                }

                // Total Expenses allocated (0 for tenants unless specific)
                $totalExpenses = 0.0;
                $netAmount = $totalPayments - $totalExpenses;

                return [
                    'tenant_id'          => $profile->id,
                    'tenant_name'        => $user?->name ?? 'Penyewa',
                    'tenant_email'       => $user?->email ?? '',
                    'tenant_phone'       => $user?->phone ?? '',
                    'active_room'        => $activeAssignment ? ($activeAssignment->room?->location?->name . ' - Kamar ' . $activeAssignment->room?->room_number) : 'Tidak Ada',
                    'has_active'         => (bool) $activeAssignment,
                    'total_payments'     => $totalPayments,
                    'total_outstanding'  => $totalOutstanding,
                    'total_expenses'     => $totalExpenses,
                    'net_amount'         => $netAmount,
                    'days_overdue'       => $daysOverdue,
                    'transaction_count'  => $assignments->flatMap->vaPayments->whereIn('transaction_status', ['settlement', 'paid', 'capture', 'success'])->count()
                                            + $assignments->flatMap->manualPayments->where('status', 'approved')->count(),
                ];
            })->sortByDesc('total_payments')->values();
        } else {
            // 3. Room Financial Recap
            $roomQuery = Room::with([
                'location',
                'roomAssignments.tenantProfile.user',
                'roomAssignments.vaPayments',
                'roomAssignments.manualPayments',
            ]);

            if ($locationId) {
                $roomQuery->where('location_id', $locationId);
            }

            if ($search) {
                $roomQuery->where('room_number', 'like', "%{$search}%");
            }

            $rooms = $roomQuery->get();

            // Cache location expense totals and room counts for proportional expense calculation
            $locationStats = [];
            foreach ($locations as $loc) {
                $locExp = (float) Expense::where('location_id', $loc->id)->sum('amount');
                $locRooms = max(1, Room::where('location_id', $loc->id)->count());
                $locationStats[$loc->id] = [
                    'total_expenses' => $locExp,
                    'room_count'     => $locRooms,
                    'per_room_exp'   => round($locExp / $locRooms, 2),
                ];
            }

            $roomRecap = $rooms->map(function (Room $room) use ($locationStats) {
                $assignments = $room->roomAssignments;
                $activeAssignment = $assignments->firstWhere('status', 'active');

                // Total revenue collected for this room
                $vaSum = (float) $assignments->flatMap->vaPayments
                    ->whereIn('transaction_status', ['settlement', 'paid', 'capture', 'success'])
                    ->sum('gross_amount');

                $manualSum = (float) $assignments->flatMap->manualPayments
                    ->where('status', 'approved')
                    ->sum('amount');

                $totalPayments = $vaSum + $manualSum;

                // Total outstanding rent if current occupant is overdue
                $totalOutstanding = 0.0;
                $daysOverdue = 0;
                if ($activeAssignment && ! $activeAssignment->isPaidForCurrentPeriod() && ! $activeAssignment->hasPendingPayment() && $activeAssignment->getDaysUntilDue() < 0) {
                    $totalOutstanding = (float) ($activeAssignment->rent_amount_snapshot ?? $room->rent_amount);
                    $daysOverdue = abs($activeAssignment->getDaysUntilDue());
                }

                // Allocated expenses per room based on location operational overhead
                $totalExpenses = $locationStats[$room->location_id]['per_room_exp'] ?? 0.0;
                $netAmount = $totalPayments - $totalExpenses;

                return [
                    'room_id'            => $room->id,
                    'room_number'        => $room->room_number,
                    'floor_name'         => $room->floor_name,
                    'location_name'      => $room->location?->name ?? '—',
                    'rent_amount'        => (float) $room->rent_amount,
                    'is_occupied'        => (bool) $activeAssignment,
                    'current_tenant'     => $activeAssignment ? ($activeAssignment->tenantProfile?->user?->name ?? 'Penyewa') : 'Kosong',
                    'total_payments'     => $totalPayments,
                    'total_outstanding'  => $totalOutstanding,
                    'total_expenses'     => $totalExpenses,
                    'net_amount'         => $netAmount,
                    'days_overdue'       => $daysOverdue,
                    'total_assignments'  => $assignments->count(),
                ];
            })->sortByDesc('total_payments')->values();
        }

        return view('admin.financial-recap.financial-recap-hub', [
            'viewMode'               => $viewMode,
            'tenantRecap'            => $tenantRecap,
            'roomRecap'              => $roomRecap,
            'globalTotalPayments'    => $globalTotalPayments,
            'globalTotalOutstanding' => $globalTotalOutstanding,
            'globalTotalExpenses'    => $globalTotalExpenses,
            'globalNetAmount'        => $globalNetAmount,
            'locations'              => $locations,
            'selectedLocationId'     => $locationId,
            'search'                 => $search,
        ]);
    }
}
