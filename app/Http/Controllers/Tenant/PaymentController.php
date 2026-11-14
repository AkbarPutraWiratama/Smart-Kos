<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Display tenant payment bills and payment history.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = $user->tenantProfile;

        $assignment = $tenant?->roomAssignments()
            ->where('status', 'active')
            ->with(['room.location', 'room.midtransVaAccount'])
            ->first();

        $destinationBanks = array_values(array_filter(
            config('payment.manual_banks', []),
            fn ($bank) => ! empty($bank['account_number'])
        ));

        $vaPayments = collect();
        $manualPayments = collect();
        $rateLimited = false;
        $remainingMinutes = 0;

        if ($assignment) {
            $vaPayments = $assignment->vaPayments()
                ->latest()
                ->take(15)
                ->get();

            $manualPayments = $assignment->manualPayments()
                ->latest('submitted_at')
                ->take(15)
                ->get();

            // Check 10-minute submission limit
            $lastManual = ManualPayment::where('submitted_by', $user->id)
                ->latest('submitted_at')
                ->first();

            if ($lastManual && $lastManual->submitted_at) {
                $diffInSeconds = (int) now()->diffInSeconds($lastManual->submitted_at);
                if ($diffInSeconds < 600) {
                    $rateLimited = true;
                    $remainingMinutes = (int) ceil((600 - $diffInSeconds) / 60);
                }
            }
        }

        return view('penyewa.payments.index', compact(
            'assignment',
            'destinationBanks',
            'vaPayments',
            'manualPayments',
            'rateLimited',
            'remainingMinutes'
        ));
    }

    /**
     * Generate or regenerate a Midtrans Virtual Account.
     */
    public function createVa(Request $request, MidtransService $midtransService): RedirectResponse
    {
        $request->validate([
            'bank_code' => ['required', 'string', 'in:bca,bni,bri,mandiri,permata'],
        ]);

        $user = $request->user();
        $assignment = $user->tenantProfile?->roomAssignments()
            ->where('status', 'active')
            ->with('room')
            ->first();

        if (! $assignment) {
            return back()->with('error', 'Anda belum memiliki kamar aktif untuk melakukan pembayaran.');
        }

        try {
            $vaAccount = $midtransService->createVirtualAccount($assignment, $request->input('bank_code'));

            return back()->with('success', "Nomor Virtual Account {$vaAccount->bank_code} berhasil dibuat: {$vaAccount->va_number}. Silakan lakukan transfer.");
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal memproses Virtual Account: '.$e->getMessage());
        }
    }

    /**
     * Submit manual bank transfer payment proof.
     */
    public function storeManual(Request $request): RedirectResponse
    {
        $user = $request->user();
        $assignment = $user->tenantProfile?->roomAssignments()
            ->where('status', 'active')
            ->first();

        if (! $assignment) {
            return back()->with('error', 'Anda belum memiliki penugasan kamar aktif.');
        }

        // Strict 10-minute rate limit enforcement
        $lastManual = ManualPayment::where('submitted_by', $user->id)
            ->latest('submitted_at')
            ->first();

        if ($lastManual && $lastManual->submitted_at) {
            $diffInSeconds = (int) now()->diffInSeconds($lastManual->submitted_at);
            if ($diffInSeconds < 600) {
                $remainingMinutes = (int) ceil((600 - $diffInSeconds) / 60);

                return back()->with('error', "Pengiriman bukti pembayaran dibatasi 1 kali setiap 10 menit. Silakan tunggu {$remainingMinutes} menit lagi sebelum mengirim kembali.");
            }
        }

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:1'],
            'proof' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:4096'],
        ], [
            'bank_name.required' => 'Nama bank pengirim wajib diisi.',
            'bank_account_number.required' => 'Nomor rekening pengirim wajib diisi.',
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.min' => 'Nominal pembayaran minimal Rp 1.',
            'proof.required' => 'Bukti pembayaran wajib diunggah.',
            'proof.mimes' => 'Format file bukti transfer harus berupa JPEG, PNG, JPG, atau PDF.',
            'proof.max' => 'Ukuran file bukti transfer maksimal 4 MB.',
        ]);

        $proofPath = $request->file('proof')->store('payment-proofs', 'public');

        ManualPayment::create([
            'room_assignment_id' => $assignment->id,
            'submitted_by' => $user->id,
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'proof_path' => $proofPath,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Bukti transfer berhasil dikirim dan sedang menunggu verifikasi dari pengelola.');
    }
}
