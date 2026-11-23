<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\ManualPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display list of manual payments for verification.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'pending');
        $search = trim((string) $request->query('search', ''));

        // Query manual payments with eager-loaded relations
        $query = ManualPayment::with([
            'roomAssignment.room.location',
            'roomAssignment.tenantProfile.user',
            'submittedBy',
            'verifiedBy',
        ]);

        if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $statusFilter);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                    ->orWhere('bank_account_number', 'like', "%{$search}%")
                    ->orWhereHas('submittedBy', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    })
                    ->orWhereHas('roomAssignment.room', function ($subQ) use ($search) {
                        $subQ->where('room_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('roomAssignment.room.location', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        // Summary metrics
        $pendingCount = ManualPayment::pending()->count();
        $approvedCount = ManualPayment::approved()->count();
        $rejectedCount = ManualPayment::rejected()->count();
        $totalApprovedAmount = ManualPayment::approved()->sum('amount');

        return view('admin.payments.manual-verification', compact(
            'payments',
            'statusFilter',
            'search',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalApprovedAmount'
        ));
    }

    /**
     * Approve manual payment submission.
     */
    public function approve(Request $request, ManualPayment $payment): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('error', 'Pembayaran ini sudah diverifikasi sebelumnya.');
        }

        $payment->update([
            'status'      => ManualPayment::STATUS_APPROVED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $payment->loadMissing(['roomAssignment.room', 'roomAssignment.tenantProfile.user']);

        FinancialRecord::updateOrCreate(
            [
                'reference_type' => 'manual_payment',
                'reference_id'   => $payment->id,
            ],
            [
                'location_id'      => $payment->roomAssignment?->room?->location_id,
                'recorded_by'      => $request->user()->id,
                'type'             => 'income',
                'description'      => 'Pembayaran Sewa Manual - Kamar ' . ($payment->roomAssignment?->room?->room_number ?? '—') . ' - ' . ($payment->roomAssignment?->tenantProfile?->user?->name ?? 'Penyewa'),
                'amount'           => $payment->amount,
                'transaction_date' => $payment->verified_at?->toDateString() ?? now()->toDateString(),
            ]
        );

        $tenantName = $payment->roomAssignment?->tenantProfile?->user?->name
            ?? $payment->submittedBy?->name
            ?? 'Penyewa';

        return back()->with(
            'success',
            "Pembayaran sebesar {$payment->formattedAmount()} dari {$tenantName} berhasil disetujui."
        );
    }

    /**
     * Reject manual payment submission with a mandatory reason.
     */
    public function reject(Request $request, ManualPayment $payment): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('error', 'Pembayaran ini sudah diverifikasi sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max'      => 'Alasan penolakan maksimal 255 karakter.',
        ]);

        $payment->update([
            'status'           => ManualPayment::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
            'verified_by'      => $request->user()->id,
            'verified_at'      => now(),
        ]);

        return back()->with(
            'warning',
            "Pembayaran {$payment->formattedAmount()} ditolak. Alasan: {$validated['rejection_reason']}"
        );
    }
}
