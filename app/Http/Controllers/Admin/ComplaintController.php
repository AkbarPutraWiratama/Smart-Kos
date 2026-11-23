<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComplaintActionRequest;
use App\Models\Complaint;
use App\Models\ComplaintAction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialRecord;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /**
     * Display complaints list for admin with filters and metrics.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $locationId = $request->query('location_id');
        $search = trim((string) $request->query('search', ''));

        $query = Complaint::with([
            'tenantProfile.user',
            'tenantProfile.roomAssignments.room.location',
            'actions.staff',
        ])
        ->orderByDesc('highlighted') // Highlighted complaints appear first
        ->orderByDesc('submitted_at')
        ->orderByDesc('id');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($locationId) {
            $query->whereHas('tenantProfile.roomAssignments.room', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('tenantProfile.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tenantProfile.roomAssignments.room', function ($rq) use ($search) {
                      $rq->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        $complaints = $query->paginate(12)->withQueryString();

        // Calculate statistics
        $allComplaints = Complaint::all();
        $stats = [
            'total'           => $allComplaints->count(),
            'belum'           => $allComplaints->where('status', 'belum')->count(),
            'dalam_perbaikan' => $allComplaints->where('status', 'dalam_perbaikan')->count(),
            'selesai'         => $allComplaints->where('status', 'selesai')->count(),
            'highlighted'     => $allComplaints->where('highlighted', true)->count(),
        ];

        $locations = Location::where('status', 'active')->orderBy('name')->get();

        return view('admin.complaints.complaint-management', compact(
            'complaints',
            'stats',
            'locations',
            'status',
            'locationId',
            'search'
        ));
    }

    /**
     * Record a complaint action by admin and optionally link repair costs to Keuangan.
     */
    public function storeAction(ComplaintActionRequest $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaint-actions', 'public');
        }

        // 1. Create ComplaintAction record
        ComplaintAction::create([
            'complaint_id'       => $complaint->id,
            'staff_id'           => $user->id,
            'action_description' => $validated['action_description'],
            'image_path'         => $imagePath,
            'status'             => $validated['status'],
            'completed_at'       => $validated['status'] === 'selesai' ? now() : null,
        ]);

        // 2. Update status of the complaint
        $complaint->update([
            'status' => $validated['status'],
        ]);

        // 3. Optional: If repair/maintenance cost is recorded, sync to Keuangan
        if (! empty($validated['cost']) && (float) $validated['cost'] > 0) {
            $cost = (float) $validated['cost'];

            $room = $complaint->tenantProfile?->roomAssignments()
                ->where('status', 'active')
                ->first()?->room
                ?? $complaint->tenantProfile?->roomAssignments()->first()?->room;

            $category = ExpenseCategory::firstOrCreate(
                ['name' => 'Perbaikan & Pemeliharaan Fasilitas'],
                ['type' => 'maintenance']
            );

            $expense = Expense::create([
                'location_id'  => $room?->location_id,
                'category_id'  => $category->id,
                'recorded_by'  => $user->id,
                'title'        => 'Biaya Perbaikan Aduan: ' . $complaint->title,
                'description'  => $validated['action_description'],
                'amount'       => $cost,
                'expense_date' => now()->toDateString(),
                'receipt_path' => $imagePath,
            ]);

            FinancialRecord::create([
                'location_id'      => $room?->location_id,
                'recorded_by'      => $user->id,
                'type'             => 'expense',
                'reference_type'   => 'expense',
                'reference_id'     => $expense->id,
                'description'      => 'Biaya Perbaikan Aduan: ' . $complaint->title,
                'amount'           => $cost,
                'transaction_date' => now()->toDateString(),
            ]);
        }

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Tindakan penanganan aduan berhasil dicatat.');
    }

    /**
     * Toggle the highlight status of a complaint (Admin only).
     */
    public function toggleHighlight(Complaint $complaint): RedirectResponse
    {
        $complaint->update([
            'highlighted' => ! $complaint->highlighted,
        ]);

        $statusText = $complaint->highlighted ? 'ditandai prioritas (highlight)' : 'dihapus dari prioritas';

        return redirect()->back()
            ->with('success', "Aduan berhasil {$statusText}.");
    }
}
