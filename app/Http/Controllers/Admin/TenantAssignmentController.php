<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomAssignmentRequest;
use App\Http\Requests\Admin\SensitiveActionConfirmRequest;
use App\Http\Requests\Admin\UpdateAssignmentPriceRequest;
use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\VAPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantAssignmentController extends Controller
{
    /**
     * Display the tenant status & room assignments overview.
     */
    public function index(Request $request): View
    {
        $locations = Location::where('status', 'active')
            ->with([
                'photos',
                'rooms' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->withCount([
                'rooms',
                'rooms as active_rooms_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->get();

        $selectedLocationId = $request->input('location_id', $locations->first()?->id);
        $statusTab = $request->input('tab', 'visualization'); // 'visualization' | 'assigned' | 'unassigned'

        // Floor-by-floor room grid for Room Status Visualization (Phase 16)
        $selectedLocation = $locations->firstWhere('id', $selectedLocationId) ?? $locations->first();
        $roomsByFloor = collect();

        if ($selectedLocation) {
            $rooms = $selectedLocation->rooms()
                ->where('status', 'active')
                ->with([
                    'activeAssignment.tenantProfile.user',
                    'activeAssignment.room.midtransVaAccount',
                    'activeAssignment.vaPayments' => fn ($q) => $q->latest('transaction_time')->limit(15),
                    'activeAssignment.manualPayments' => fn ($q) => $q->latest('submitted_at')->limit(15),
                ])
                ->orderBy('floor_name')
                ->orderBy('room_number')
                ->get();

            $roomsByFloor = $rooms->groupBy('floor_name');
        }

        // Query active assignments
        $assignedQuery = RoomAssignment::with([
            'room.location',
            'tenantProfile.user',
            'room.midtransVaAccount',
            'vaPayments' => fn ($q) => $q->latest('transaction_time')->limit(15),
            'manualPayments' => fn ($q) => $q->latest('submitted_at')->limit(15),
        ])->where('status', 'active');

        if ($selectedLocationId) {
            $assignedQuery->whereHas('room', function ($q) use ($selectedLocationId) {
                $q->where('location_id', $selectedLocationId);
            });
        }

        $assignedTenants = $assignedQuery->latest()->paginate(10, ['*'], 'assigned_page')->withQueryString();

        // Query unassigned tenants (Active penyewa users whose tenant profile has NO active room assignment)
        $unassignedTenants = TenantProfile::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'penyewa')->where('status', 'active');
            })
            ->whereDoesntHave('roomAssignments', function ($q) {
                $q->where('status', 'active');
            })
            ->latest()
            ->paginate(10, ['*'], 'unassigned_page')
            ->withQueryString();

        // Calculate metrics
        $totalRooms = Room::where('status', 'active')
            ->when($selectedLocationId, fn ($q) => $q->where('location_id', $selectedLocationId))
            ->count();

        $occupiedRooms = Room::where('status', 'active')
            ->when($selectedLocationId, fn ($q) => $q->where('location_id', $selectedLocationId))
            ->whereHas('roomAssignments', fn ($q) => $q->where('status', 'active'))
            ->count();

        $unassignedCount = TenantProfile::whereHas('user', function ($q) {
            $q->where('role', 'penyewa')->where('status', 'active');
        })->whereDoesntHave('roomAssignments', function ($q) {
            $q->where('status', 'active');
        })->count();

        // Available rooms for assigning
        $availableRooms = Room::with('location')
            ->where('status', 'active')
            ->when($selectedLocationId, fn ($q) => $q->where('location_id', $selectedLocationId))
            ->whereDoesntHave('roomAssignments', fn ($q) => $q->where('status', 'active'))
            ->orderBy('room_number')
            ->get();

        $allUnassignedProfiles = TenantProfile::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'penyewa')->where('status', 'active');
            })
            ->whereDoesntHave('roomAssignments', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        $metrics = [
            'total_rooms'      => $totalRooms,
            'occupied_rooms'   => $occupiedRooms,
            'empty_rooms'      => max(0, $totalRooms - $occupiedRooms),
            'occupancy_rate'   => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0,
            'unassigned_count' => $unassignedCount,
        ];

        return view('admin.tenants.tenant-status', compact(
            'locations',
            'selectedLocation',
            'selectedLocationId',
            'statusTab',
            'roomsByFloor',
            'assignedTenants',
            'unassignedTenants',
            'availableRooms',
            'allUnassignedProfiles',
            'metrics'
        ));
    }

    /**
     * Assign a tenant to a room.
     */
    public function store(RoomAssignmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $room = Room::findOrFail($validated['room_id']);
        $startDate = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        $billingDueDay = $startDate->day;
        $nextDueDate = $startDate->copy();

        DB::transaction(function () use ($validated, $room, $billingDueDay, $nextDueDate) {
            RoomAssignment::create([
                'room_id'              => $room->id,
                'tenant_id'            => $validated['tenant_id'],
                'start_date'           => $validated['start_date'],
                'end_date'             => $validated['end_date'] ?? null,
                'status'               => 'active',
                'rent_amount_snapshot' => $room->rent_amount,
                'payment_cycle'        => $validated['payment_cycle'],
                'billing_due_day'      => $billingDueDay,
                'next_due_date'        => $nextDueDate->toDateString(),
            ]);
        });

        return back()->with('success', "Penyewa berhasil ditugaskan ke Kamar {$room->room_number}.");
    }

    /**
     * Update the current tenant price and the room default for future tenants.
     */
    public function updatePrice(UpdateAssignmentPriceRequest $request, RoomAssignment $assignment): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($assignment, $validated): void {
            $lockedAssignment = RoomAssignment::query()
                ->whereKey($assignment->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $room = Room::query()
                ->whereKey($lockedAssignment->room_id)
                ->lockForUpdate()
                ->firstOrFail();

            $paymentCycle = $validated['payment_cycle'] ?? ($lockedAssignment->payment_cycle ?: 'monthly');
            $startDate = ! empty($validated['start_date'])
                ? \Carbon\Carbon::parse($validated['start_date'])->startOfDay()
                : ($lockedAssignment->start_date
                    ? $lockedAssignment->start_date->copy()->startOfDay()
                    : now()->startOfDay());

            $endDate = $paymentCycle === 'yearly'
                ? $startDate->copy()->addYearsNoOverflow(1)
                : $startDate->copy()->addMonthsNoOverflow(1);

            $lockedAssignment->update([
                'rent_amount_snapshot' => $validated['rent_amount'],
                'payment_cycle' => $paymentCycle,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'billing_due_day' => $startDate->day,
                'next_due_date' => $startDate->toDateString(),
            ]);

            $room->update([
                'rent_amount' => $validated['rent_amount'],
            ]);

            if ($room->midtransVaAccount) {
                $room->midtransVaAccount->update([
                    'amount' => $validated['rent_amount'],
                ]);
            }
        });

        return back()->with('success', 'Harga penyewa dan harga default kamar berhasil diperbarui.');
    }

    /**
     * Change a tenant's room (transfer to another room).
     */
    public function changeRoom(SensitiveActionConfirmRequest $request, RoomAssignment $assignment): RedirectResponse
    {
        $request->validate([
            'new_room_id'     => ['required', 'exists:rooms,id'],
            'transfer_date'   => ['required', 'date'],
        ]);

        $newRoom = Room::findOrFail($request->input('new_room_id'));

        // Validate new room is active and not already occupied
        if ($newRoom->status !== 'active') {
            return back()->withErrors(['new_room_id' => 'Kamar tujuan sedang nonaktif.']);
        }

        if ($newRoom->isOccupied()) {
            return back()->withErrors(['new_room_id' => 'Kamar tujuan sudah ditempati penyewa lain.']);
        }

        DB::transaction(function () use ($assignment, $newRoom, $request) {
            $transferDate = \Carbon\Carbon::parse($request->input('transfer_date'))->startOfDay();
            $paymentCycle = $assignment->payment_cycle ?: 'monthly';

            // End current assignment
            $assignment->update([
                'status'         => 'completed',
                'end_date'       => $request->input('transfer_date'),
                'deactivated_at' => now(),
            ]);

            // Create new assignment
            $newAssignment = RoomAssignment::create([
                'room_id'              => $newRoom->id,
                'tenant_id'            => $assignment->tenant_id,
                'start_date'           => $request->input('transfer_date'),
                'end_date'             => null,
                'status'               => 'active',
                'rent_amount_snapshot' => $newRoom->rent_amount,
                'payment_cycle'        => $paymentCycle,
                'billing_due_day'      => $transferDate->day,
                'next_due_date'        => $transferDate->toDateString(),
            ]);

            // Keep manual payment history and verification state attached to this tenant,
            // now under the new room assignment.
            ManualPayment::where('room_assignment_id', $assignment->id)
                ->update(['room_assignment_id' => $newAssignment->id]);

            VAPayment::where('room_assignment_id', $assignment->id)
                ->update(['room_assignment_id' => $newAssignment->id]);
        });

        return back()->with('success', "Penyewa berhasil dipindahkan ke Kamar {$newRoom->room_number}.");
    }

    /**
     * End a tenant's room assignment (checkout / terminate assignment).
     */
    public function endAssignment(SensitiveActionConfirmRequest $request, RoomAssignment $assignment): RedirectResponse
    {
        $assignment->update([
            'status'         => 'completed',
            'end_date'       => now()->toDateString(),
            'deactivated_at' => now(),
        ]);

        return back()->with('success', 'Penugasan kamar telah diselesaikan. Kamar kini kembali kosong.');
    }
}

