<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CashAdvanceRequest;
use App\Http\Requests\Admin\PayrollRequest;
use App\Models\CashAdvance;
use App\Models\FinancialRecord;
use App\Models\PayrollRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PayrollController extends Controller
{
    /**
     * Display payroll and cash advances management dashboard.
     */
    public function index(Request $request): View
    {
        $activeTab = $request->input('tab', 'payroll'); // 'payroll' or 'advances'
        $selectedStaffId = $request->input('staff_id', 'all');
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', 'all'); // 'all' or 1..12
        $selectedStatus = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));

        // All staff members for selection dropdown (including inactive so historical filter works)
        $staffMembers = User::where('role', 'staff')
            ->orderBy('name')
            ->get();

        // ── 1. Payroll Records Query ──
        $payrollQuery = PayrollRecord::with(['staff', 'recordedBy', 'financialRecord']);

        if (! empty($selectedStaffId) && $selectedStaffId !== 'all') {
            $payrollQuery->where('staff_id', (int) $selectedStaffId);
        }

        if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
            $pStart = Carbon::createFromDate($selectedYear, (int) $selectedMonth, 1)->startOfMonth();
            $pEnd = $pStart->copy()->endOfMonth();
            $payrollQuery->whereBetween('payment_date', [$pStart->toDateString(), $pEnd->toDateString()]);
        } else {
            $pStart = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $pEnd = $pStart->copy()->endOfYear();
            $payrollQuery->whereBetween('payment_date', [$pStart->toDateString(), $pEnd->toDateString()]);
        }

        if (! empty($search)) {
            $payrollQuery->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('staff', fn ($sub) => $sub->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
            });
        }

        $payrollRecords = (clone $payrollQuery)
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'payroll_page')
            ->withQueryString();

        // ── 2. Cash Advances Query ──
        $advanceQuery = CashAdvance::with(['staff', 'recordedBy']);

        if (! empty($selectedStaffId) && $selectedStaffId !== 'all') {
            $advanceQuery->where('staff_id', (int) $selectedStaffId);
        }

        if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
            $aStart = Carbon::createFromDate($selectedYear, (int) $selectedMonth, 1)->startOfMonth();
            $aEnd = $aStart->copy()->endOfMonth();
            $advanceQuery->whereBetween('advance_date', [$aStart->toDateString(), $aEnd->toDateString()]);
        } else {
            $aStart = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $aEnd = $aStart->copy()->endOfYear();
            $advanceQuery->whereBetween('advance_date', [$aStart->toDateString(), $aEnd->toDateString()]);
        }

        if (! empty($selectedStatus) && $selectedStatus !== 'all') {
            $advanceQuery->where('status', $selectedStatus);
        }

        if (! empty($search)) {
            $advanceQuery->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('staff', fn ($sub) => $sub->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
            });
        }

        $cashAdvances = (clone $advanceQuery)
            ->orderBy('advance_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'advances_page')
            ->withQueryString();

        // ── 3. Summary Metrics ──
        $totalGross = (float) (clone $payrollQuery)->sum('gross_amount');
        $totalDeductions = (float) (clone $payrollQuery)->sum('cash_advance_deduction');
        $totalNetPaid = (float) (clone $payrollQuery)->sum('net_amount');
        $totalPayrollCount = (clone $payrollQuery)->count();

        // Total outstanding cash advances overall
        $totalOutstandingAdvances = (float) CashAdvance::whereIn('status', [CashAdvance::STATUS_OUTSTANDING, CashAdvance::STATUS_PARTIALLY_REPAID])->sum('amount');
        $totalAdvancesDisbursed = (float) (clone $advanceQuery)->sum('amount');
        $totalAdvancesCount = (clone $advanceQuery)->count();

        $monthsNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('admin.payroll.index', compact(
            'payrollRecords',
            'cashAdvances',
            'staffMembers',
            'activeTab',
            'selectedStaffId',
            'selectedYear',
            'selectedMonth',
            'selectedStatus',
            'search',
            'totalGross',
            'totalDeductions',
            'totalNetPaid',
            'totalPayrollCount',
            'totalOutstandingAdvances',
            'totalAdvancesDisbursed',
            'totalAdvancesCount',
            'monthsNames'
        ));
    }

    /**
     * Store a newly created payroll record in storage.
     */
    public function storePayroll(PayrollRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $gross = (float) $validated['gross_amount'];
        $deduction = isset($validated['cash_advance_deduction']) ? (float) $validated['cash_advance_deduction'] : 0.0;
        $net = max(0.0, $gross - $deduction);

        $payroll = PayrollRecord::create([
            'staff_id'               => $validated['staff_id'],
            'recorded_by'            => Auth::id(),
            'gross_amount'           => $gross,
            'cash_advance_deduction' => $deduction,
            'net_amount'             => $net,
            'payment_date'           => $validated['payment_date'],
            'notes'                  => $validated['notes'] ?? null,
        ]);

        // If settle_advances requested and deduction > 0, settle oldest advances
        if ($request->boolean('settle_advances') && $deduction > 0) {
            $this->applyDeductionToAdvances($validated['staff_id'], $deduction);
        }

        // Synchronize with general ledger financial records
        FinancialRecord::recordFromPayroll($payroll);

        return redirect()->route('admin.payroll.index', ['tab' => 'payroll'])
            ->with('success', 'Catatan gaji staff berhasil disimpan dan dibukukan ke laporan keuangan.');
    }

    /**
     * Update the specified payroll record in storage.
     */
    public function updatePayroll(PayrollRequest $request, PayrollRecord $payroll): RedirectResponse
    {
        $validated = $request->validated();

        $gross = (float) $validated['gross_amount'];
        $deduction = isset($validated['cash_advance_deduction']) ? (float) $validated['cash_advance_deduction'] : 0.0;
        $net = max(0.0, $gross - $deduction);

        $payroll->update([
            'staff_id'               => $validated['staff_id'],
            'gross_amount'           => $gross,
            'cash_advance_deduction' => $deduction,
            'net_amount'             => $net,
            'payment_date'           => $validated['payment_date'],
            'notes'                  => $validated['notes'] ?? null,
        ]);

        // Update general ledger financial reporting
        FinancialRecord::recordFromPayroll($payroll);

        return redirect()->route('admin.payroll.index', ['tab' => 'payroll'])
            ->with('success', 'Catatan gaji staff berhasil diperbarui.');
    }

    /**
     * Remove the specified payroll record from storage.
     */
    public function destroyPayroll(PayrollRecord $payroll): RedirectResponse
    {
        // Remove ledger financial record
        $payroll->financialRecord?->delete();
        $payroll->delete();

        return redirect()->route('admin.payroll.index', ['tab' => 'payroll'])
            ->with('success', 'Catatan gaji staff dan jurnal pembukuan terkait berhasil dihapus.');
    }

    /**
     * Store a newly created cash advance in storage.
     */
    public function storeAdvance(CashAdvanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        CashAdvance::create([
            'staff_id'     => $validated['staff_id'],
            'recorded_by'  => Auth::id(),
            'amount'       => $validated['amount'],
            'advance_date' => $validated['advance_date'],
            'status'       => $validated['status'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.payroll.index', ['tab' => 'advances'])
            ->with('success', 'Catatan kasbon staff berhasil dicatat.');
    }

    /**
     * Update the specified cash advance in storage.
     */
    public function updateAdvance(CashAdvanceRequest $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $validated = $request->validated();

        $cashAdvance->update([
            'staff_id'     => $validated['staff_id'],
            'amount'       => $validated['amount'],
            'advance_date' => $validated['advance_date'],
            'status'       => $validated['status'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.payroll.index', ['tab' => 'advances'])
            ->with('success', 'Catatan kasbon staff berhasil diperbarui.');
    }

    /**
     * Quick update of cash advance status.
     */
    public function updateAdvanceStatus(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:outstanding,partially_repaid,repaid,cancelled'],
        ]);

        $cashAdvance->update(['status' => $request->input('status')]);

        return redirect()->route('admin.payroll.index', ['tab' => 'advances'])
            ->with('success', "Status kasbon berhasil diubah menjadi: {$cashAdvance->statusLabel()}.");
    }

    /**
     * Remove the specified cash advance from storage.
     */
    public function destroyAdvance(CashAdvance $cashAdvance): RedirectResponse
    {
        $cashAdvance->delete();

        return redirect()->route('admin.payroll.index', ['tab' => 'advances'])
            ->with('success', 'Catatan kasbon staff berhasil dihapus.');
    }

    /**
     * Helper to apply payroll deduction against outstanding staff cash advances.
     */
    protected function applyDeductionToAdvances(int $staffId, float $deductionAmount): void
    {
        $remaining = $deductionAmount;

        $advances = CashAdvance::where('staff_id', $staffId)
            ->whereIn('status', [CashAdvance::STATUS_OUTSTANDING, CashAdvance::STATUS_PARTIALLY_REPAID])
            ->orderBy('advance_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($advances as $advance) {
            if ($remaining <= 0) {
                break;
            }

            $advanceAmount = (float) $advance->amount;

            if ($remaining >= $advanceAmount) {
                $advance->update(['status' => CashAdvance::STATUS_REPAID]);
                $remaining -= $advanceAmount;
            } else {
                $advance->update(['status' => CashAdvance::STATUS_PARTIALLY_REPAID]);
                $remaining = 0;
            }
        }
    }
}
