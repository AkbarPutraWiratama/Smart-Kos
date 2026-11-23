<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PayrollRequest;
use App\Models\CashAdvance;
use App\Models\FinancialRecord;
use App\Models\PayrollRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollController extends Controller
{
    /**
     * Display payroll and cash advance management overview.
     */
    public function index(Request $request): View
    {
        $staffId = $request->query('staff_id');
        $timeScope = $request->query('time_scope', 'month');
        $month = $request->query('month', now()->format('Y-m'));
        $year = $request->query('year', now()->year);

        // Payroll query
        $payrollQuery = PayrollRecord::with(['staff', 'recordedBy'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($staffId) {
            $payrollQuery->where('staff_id', $staffId);
        }

        if ($timeScope === 'month' && $month) {
            [$y, $m] = explode('-', $month);
            $payrollQuery->whereYear('payment_date', $y)->whereMonth('payment_date', $m);
        } elseif ($timeScope === 'year' && $year) {
            $payrollQuery->whereYear('payment_date', $year);
        }

        $totalGross = (clone $payrollQuery)->sum('gross_amount');
        $totalDeductions = (clone $payrollQuery)->sum('cash_advance_deduction');
        $totalNet = (clone $payrollQuery)->sum('net_amount');

        $payrollRecords = $payrollQuery->paginate(10, ['*'], 'payroll_page')->withQueryString();

        // Cash advances query
        $advanceQuery = CashAdvance::with(['staff', 'recordedBy'])
            ->orderByDesc('advance_date')
            ->orderByDesc('id');

        if ($staffId) {
            $advanceQuery->where('staff_id', $staffId);
        }

        $outstandingAdvances = CashAdvance::where('status', 'outstanding')
            ->when($staffId, fn ($q) => $q->where('staff_id', $staffId))
            ->sum('amount');

        $cashAdvances = $advanceQuery->paginate(10, ['*'], 'advances_page')->withQueryString();

        $staffList = User::where('role', 'staff')->orderBy('name')->get();

        return view('admin.payroll.payroll-hub', [
            'payrollRecords' => $payrollRecords,
            'cashAdvances' => $cashAdvances,
            'staffList' => $staffList,
            'selectedStaffId' => $staffId,
            'timeScope' => $timeScope,
            'month' => $month,
            'year' => (int) $year,
            'totalGross' => (float) $totalGross,
            'totalDeductions' => (float) $totalDeductions,
            'totalNet' => (float) $totalNet,
            'outstandingAdvances' => (float) $outstandingAdvances,
        ]);
    }

    /**
     * Store a newly created payroll record.
     */
    public function store(PayrollRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $gross = (float) $validated['gross_amount'];
        $deduction = isset($validated['cash_advance_deduction']) ? (float) $validated['cash_advance_deduction'] : 0.0;
        $net = max(0, $gross - $deduction);

        $staff = User::findOrFail($validated['staff_id']);

        DB::transaction(function () use ($validated, $gross, $deduction, $net, $staff, $request) {
            $payroll = PayrollRecord::create([
                'staff_id' => $staff->id,
                'recorded_by' => $request->user()->id,
                'gross_amount' => $gross,
                'cash_advance_deduction' => $deduction,
                'net_amount' => $net,
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Auto-sync into financial_records
            $dateLabel = Carbon::parse($payroll->payment_date)->translatedFormat('d F Y');
            FinancialRecord::updateOrCreate(
                [
                    'reference_type' => 'payroll',
                    'reference_id'   => $payroll->id,
                ],
                [
                    'location_id'      => null,
                    'recorded_by'      => $request->user()->id,
                    'type'             => 'expense',
                    'description'      => "Gaji Staf - {$staff->name} ({$dateLabel})",
                    'amount'           => $payroll->net_amount,
                    'transaction_date' => $payroll->payment_date->toDateString(),
                ]
            );

            // If deduction is applied, reduce outstanding cash advances for this staff
            if ($deduction > 0) {
                $remainingDeduction = $deduction;
                $activeAdvances = CashAdvance::where('staff_id', $staff->id)
                    ->whereIn('status', ['outstanding', 'partially_repaid'])
                    ->orderBy('advance_date')
                    ->get();

                foreach ($activeAdvances as $advance) {
                    if ($remainingDeduction <= 0) {
                        break;
                    }

                    if ($advance->amount <= $remainingDeduction) {
                        $remainingDeduction -= (float) $advance->amount;
                        $advance->update(['status' => 'repaid']);
                    } else {
                        $advance->update(['status' => 'partially_repaid']);
                        $remainingDeduction = 0;
                    }
                }
            }
        });

        return redirect()->route('admin.payroll.index')->with('success', "Gaji untuk {$staff->name} sebesar Rp " . number_format($net, 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Delete a payroll record and remove its financial record.
     */
    public function destroy(PayrollRecord $payroll): RedirectResponse
    {
        DB::transaction(function () use ($payroll) {
            FinancialRecord::where('reference_type', 'payroll')
                ->where('reference_id', $payroll->id)
                ->delete();

            $payroll->delete();
        });

        return redirect()->route('admin.payroll.index')->with('success', 'Catatan gaji berhasil dihapus.');
    }
}
