<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /**
     * Display the tenant's complaint submission page and history.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenantProfile = $user->tenantProfile;

        $complaints = collect();
        $activeAssignment = null;
        $rateLimited = false;
        $remainingMinutes = 0;

        $stats = [
            'total'           => 0,
            'open'           => 0,
            'in_progress' => 0,
            'resolved'         => 0,
        ];

        if ($tenantProfile) {
            $activeAssignment = $tenantProfile->roomAssignments()
                ->with(['room.location'])
                ->where('status', 'active')
                ->latest('start_date')
                ->first();

            // Fetch tenant's complaints ordered by newest
            $complaints = Complaint::where('tenant_id', $tenantProfile->id)
                ->with(['actions.staff'])
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->paginate(10);

            // Compute statistics
            $allComplaints = Complaint::where('tenant_id', $tenantProfile->id)->get();
            $stats['total'] = $allComplaints->count();
            $stats['open'] = $allComplaints->where('status', 'open')->count();
            $stats['in_progress'] = $allComplaints->where('status', 'in_progress')->count();
            $stats['resolved'] = $allComplaints->where('status', 'resolved')->count();

            // Check 10-minute rate limit from latest submission
            $lastComplaint = Complaint::where('tenant_id', $tenantProfile->id)
                ->latest('submitted_at')
                ->first();

            if ($lastComplaint && $lastComplaint->submitted_at && $lastComplaint->submitted_at->isAfter(now()->subMinutes(10))) {
                $rateLimited = true;
                $elapsedSeconds = abs((int) now()->diffInSeconds($lastComplaint->submitted_at));
                $remainingMinutes = (int) max(1, ceil((600 - $elapsedSeconds) / 60));
            }
        }

        return view('penyewa.complaints.complaint-submission', compact(
            'complaints',
            'activeAssignment',
            'rateLimited',
            'remainingMinutes',
            'stats'
        ));
    }

    /**
     * Store a newly submitted complaint with 10-minute rate limit enforcement.
     */
    public function store(ComplaintRequest $request): RedirectResponse
    {
        $user = $request->user();
        $tenantProfile = $user->tenantProfile;

        if (! $tenantProfile) {
            return back()->with('error', 'Profil penyewa Anda belum lengkap. Silakan hubungi admin atau staf kos.');
        }

        // Strict server-side 10-minute rate limit enforcement
        $lastComplaint = Complaint::where('tenant_id', $tenantProfile->id)
            ->latest('submitted_at')
            ->first();

        if ($lastComplaint && $lastComplaint->submitted_at && $lastComplaint->submitted_at->isAfter(now()->subMinutes(10))) {
            $elapsedSeconds = abs((int) now()->diffInSeconds($lastComplaint->submitted_at));
            $remainingMinutes = (int) max(1, ceil((600 - $elapsedSeconds) / 60));

            return back()->with('error', "Pengiriman aduan dibatasi 1 kali setiap 10 menit. Silakan tunggu {$remainingMinutes} menit lagi sebelum mengirim kembali.");
        }

        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaints', 'public');
        }

        Complaint::create([
            'tenant_id'    => $tenantProfile->id,
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'image_path'   => $imagePath,
            'status'       => 'open',
            'highlighted'  => false,
            'submitted_at' => now(),
        ]);

        return redirect()->route('penyewa.complaints.index')
            ->with('success', 'Aduan fasilitas berhasil dikirim. Petugas kos akan segera memeriksa dan menindaklanjuti keluhan Anda.');
    }
}
