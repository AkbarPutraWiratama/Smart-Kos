<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\ManualPayment;
use App\Models\VAPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display list of payments (manual verification and VA transactions) with full history.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type', 'manual'); // 'manual' | 'va'
        $search = trim((string) $request->query('search', ''));

        // Summary metrics
        $pendingCount = ManualPayment::pending()->count();
        $approvedCount = ManualPayment::approved()->count();
        $rejectedCount = ManualPayment::rejected()->count();
        $totalApprovedAmount = (float) ManualPayment::approved()->sum('amount');

        $vaSettlementCount = VAPayment::settled()->count();
        $vaPendingCount = VAPayment::pending()->count();
        $vaTotalAmount = (float) VAPayment::settled()->sum('amount');

        $totalCombinedAmount = $totalApprovedAmount + $vaTotalAmount;

        if ($type === 'va') {
            $statusFilter = $request->query('status', 'all');

            $query = VAPayment::with([
                'roomAssignment.room.location',
                'roomAssignment.tenantProfile.user',
                'vaAccount',
            ]);

            if ($statusFilter === 'settlement' || $statusFilter === 'paid') {
                $query->settled();
            } elseif ($statusFilter === 'pending') {
                $query->pending();
            } elseif ($statusFilter === 'expired') {
                $query->expired();
            } elseif ($statusFilter === 'cancelled') {
                $query->whereIn('transaction_status', ['deny', 'cancel', VAPayment::STATUS_CANCELLED]);
            }

            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_id', 'like', "%{$search}%")
                        ->orWhere('transaction_id', 'like', "%{$search}%")
                        ->orWhereHas('vaAccount', function ($subQ) use ($search) {
                            $subQ->where('bank_code', 'like', "%{$search}%")
                                ->orWhere('va_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('roomAssignment.tenantProfile.user', function ($subQ) use ($search) {
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

            $payments = $query->latest('created_at')
                ->paginate(15)
                ->withQueryString();
        } else {
            $statusFilter = $request->query('status', 'pending');

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
        }

        return view('admin.payments.index', compact(
            'type',
            'payments',
            'statusFilter',
            'search',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalApprovedAmount',
            'vaSettlementCount',
            'vaPendingCount',
            'vaTotalAmount',
            'totalCombinedAmount'
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

        // Record in financial reporting layer
        FinancialRecord::recordFromManualPayment($payment, $request->user()->id);

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
