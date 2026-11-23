<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\FinancialRecord;
use App\Models\ManualPayment;
use App\Models\VAPayment;

class FinancialRecordSyncService
{
    /**
     * Run sync only if there are approved payments or expenses without financial records.
     */
    public static function syncIfNeeded(): void
    {
        $hasMissingManual = ManualPayment::query()
            ->where('status', ManualPayment::STATUS_APPROVED)
            ->whereDoesntHave('financialRecord')
            ->exists();

        $hasMissingVA = VAPayment::query()
            ->whereIn('transaction_status', ['settlement', 'paid', 'capture', 'success'])
            ->whereDoesntHave('financialRecord')
            ->exists();

        $hasMissingExpense = Expense::query()
            ->whereDoesntHave('financialRecord')
            ->exists();

        if ($hasMissingManual || $hasMissingVA || $hasMissingExpense) {
            self::syncAll();
        }
    }

    /**
     * Synchronize all approved payments and expenses into financial_records.
     */
    public static function syncAll(): void
    {
        // 1. Sync Approved Manual Payments
        $approvedManualPayments = ManualPayment::with([
            'roomAssignment.room.location',
            'roomAssignment.tenantProfile.user',
            'submittedBy',
            'verifiedBy',
        ])
            ->where('status', ManualPayment::STATUS_APPROVED)
            ->get();

        foreach ($approvedManualPayments as $payment) {
            $locationId = $payment->roomAssignment?->room?->location_id;
            $roomNumber = $payment->roomAssignment?->room?->room_number ?? '—';
            $tenantName = $payment->roomAssignment?->tenantProfile?->user?->name
                ?? $payment->submittedBy?->name
                ?? 'Penyewa';

            FinancialRecord::updateOrCreate(
                [
                    'reference_type' => 'manual_payment',
                    'reference_id'   => $payment->id,
                ],
                [
                    'location_id'      => $locationId,
                    'recorded_by'      => $payment->verified_by ?? $payment->submitted_by,
                    'type'             => 'income',
                    'description'      => "Pembayaran Sewa Manual - Kamar {$roomNumber} - {$tenantName}",
                    'amount'           => $payment->amount,
                    'transaction_date' => $payment->verified_at?->toDateString()
                        ?? $payment->submitted_at?->toDateString()
                        ?? $payment->created_at->toDateString(),
                ]
            );
        }

        // Clean up financial records for manual payments that are no longer approved
        $approvedManualIds = $approvedManualPayments->pluck('id')->all();
        FinancialRecord::query()
            ->where('reference_type', 'manual_payment')
            ->whereNotIn('reference_id', $approvedManualIds)
            ->delete();

        // 2. Sync Settled VA Payments
        $settledVaPayments = VAPayment::with([
            'roomAssignment.room.location',
            'roomAssignment.tenantProfile.user',
            'vaAccount.room.location',
        ])
            ->whereIn('transaction_status', ['settlement', 'paid', 'capture', 'success'])
            ->get();

        foreach ($settledVaPayments as $vaPayment) {
            $locationId = $vaPayment->roomAssignment?->room?->location_id
                ?? $vaPayment->vaAccount?->room?->location_id;
            $roomNumber = $vaPayment->roomAssignment?->room?->room_number
                ?? $vaPayment->vaAccount?->room?->room_number
                ?? '—';
            $tenantName = $vaPayment->roomAssignment?->tenantProfile?->user?->name ?? 'Penyewa';

            FinancialRecord::updateOrCreate(
                [
                    'reference_type' => 'va_payment',
                    'reference_id'   => $vaPayment->id,
                ],
                [
                    'location_id'      => $locationId,
                    'recorded_by'      => null,
                    'type'             => 'income',
                    'description'      => "Pembayaran Sewa VA Midtrans - Kamar {$roomNumber} - {$tenantName}",
                    'amount'           => $vaPayment->amount,
                    'transaction_date' => $vaPayment->paid_at?->toDateString()
                        ?? $vaPayment->transaction_time?->toDateString()
                        ?? $vaPayment->created_at->toDateString(),
                ]
            );
        }

        // Clean up financial records for VA payments that are no longer settled
        $settledVaIds = $settledVaPayments->pluck('id')->all();
        FinancialRecord::query()
            ->where('reference_type', 'va_payment')
            ->whereNotIn('reference_id', $settledVaIds)
            ->delete();

        // 3. Sync Expenses
        $expenses = Expense::with(['category', 'location'])->get();
        foreach ($expenses as $expense) {
            FinancialRecord::updateOrCreate(
                [
                    'reference_type' => 'expense',
                    'reference_id'   => $expense->id,
                ],
                [
                    'location_id'      => $expense->location_id,
                    'recorded_by'      => $expense->recorded_by,
                    'type'             => 'expense',
                    'description'      => 'Pengeluaran - ' . $expense->title . ' (' . ($expense->category?->name ?? 'Umum') . ')',
                    'amount'           => $expense->amount,
                    'transaction_date' => $expense->expense_date?->toDateString() ?? $expense->created_at->toDateString(),
                ]
            );
        }

        // Clean up financial records for expenses that no longer exist
        $expenseIds = $expenses->pluck('id')->all();
        FinancialRecord::query()
            ->where('reference_type', 'expense')
            ->whereNotIn('reference_id', $expenseIds)
            ->delete();
    }
}
