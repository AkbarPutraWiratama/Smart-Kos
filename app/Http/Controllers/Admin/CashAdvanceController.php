<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CashAdvanceRequest;
use App\Models\CashAdvance;
use App\Models\FinancialRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashAdvanceController extends Controller
{
    /**
     * Store a newly created cash advance for a staff member.
     */
    public function store(CashAdvanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $staff = User::findOrFail($validated['staff_id']);

        DB::transaction(function () use ($validated, $staff, $request) {
            $advance = CashAdvance::create([
                'staff_id' => $staff->id,
                'recorded_by' => $request->user()->id,
                'amount' => $validated['amount'],
                'advance_date' => $validated['advance_date'],
                'status' => 'outstanding',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Auto-sync into financial_records
            $dateLabel = Carbon::parse($advance->advance_date)->translatedFormat('d F Y');
            FinancialRecord::updateOrCreate(
                [
                    'reference_type' => 'cash_advance',
                    'reference_id'   => $advance->id,
                ],
                [
                    'location_id'      => null,
                    'recorded_by'      => $request->user()->id,
                    'type'             => 'expense',
                    'description'      => "Kasbon Staf - {$staff->name} ({$dateLabel})",
                    'amount'           => $advance->amount,
                    'transaction_date' => $advance->advance_date->toDateString(),
                ]
            );
        });

        return redirect()->route('admin.payroll.index')->with('success', "Kasbon sebesar Rp " . number_format($validated['amount'], 0, ',', '.') . " untuk {$staff->name} berhasil dicatat.");
    }

    /**
     * Update the status of a cash advance.
     */
    public function updateStatus(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['outstanding', 'partially_repaid', 'repaid', 'cancelled'])],
        ]);

        DB::transaction(function () use ($cashAdvance, $validated, $request) {
            $cashAdvance->update(['status' => $validated['status']]);

            if ($validated['status'] === 'cancelled') {
                // If cancelled, remove the financial record outflow
                FinancialRecord::where('reference_type', 'cash_advance')
                    ->where('reference_id', $cashAdvance->id)
                    ->delete();
            } else {
                // Re-sync financial record if restored
                $staff = $cashAdvance->staff;
                $dateLabel = Carbon::parse($cashAdvance->advance_date)->translatedFormat('d F Y');
                FinancialRecord::updateOrCreate(
                    [
                        'reference_type' => 'cash_advance',
                        'reference_id'   => $cashAdvance->id,
                    ],
                    [
                        'location_id'      => null,
                        'recorded_by'      => $request->user()->id,
                        'type'             => 'expense',
                        'description'      => "Kasbon Staf - " . ($staff?->name ?? 'Staf') . " ({$dateLabel})",
                        'amount'           => $cashAdvance->amount,
                        'transaction_date' => $cashAdvance->advance_date->toDateString(),
                    ]
                );
            }
        });

        return redirect()->route('admin.payroll.index')->with('success', 'Status kasbon berhasil diperbarui.');
    }

    /**
     * Delete a cash advance and remove its financial record.
     */
    public function destroy(CashAdvance $cashAdvance): RedirectResponse
    {
        DB::transaction(function () use ($cashAdvance) {
            FinancialRecord::where('reference_type', 'cash_advance')
                ->where('reference_id', $cashAdvance->id)
                ->delete();

            $cashAdvance->delete();
        });

        return redirect()->route('admin.payroll.index')->with('success', 'Catatan kasbon berhasil dihapus.');
    }
}
