<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Services\FinancialRecordSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitLossController extends Controller
{
    /**
     * Display the Profit and Loss statement (Laporan Laba Rugi).
     * All financial calculations are performed here at the application layer.
     */
    public function index(Request $request): View
    {
        // Ensure financial records are synchronized
        FinancialRecordSyncService::syncIfNeeded();

        $timeScope = $request->query('time_scope', 'month');
        if (!in_array($timeScope, ['month', 'year', 'all'])) {
            $timeScope = 'month';
        }

        $month = $request->query('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        $year = (int) $request->query('year', now()->year);
        $locationId = $request->query('location_id');

        // Build query
        $query = FinancialRecord::with(['expense.category', 'location']);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($timeScope === 'month') {
            $startOfMonth = Carbon::parse($month)->startOfMonth()->toDateString();
            $endOfMonth = Carbon::parse($month)->endOfMonth()->toDateString();
            $query->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);
            $periodLabel = 'Bulan ' . Carbon::parse($month)->translatedFormat('F Y');
        } elseif ($timeScope === 'year') {
            $query->whereBetween('transaction_date', ["{$year}-01-01", "{$year}-12-31"]);
            $periodLabel = 'Tahun ' . $year;
        } else {
            $periodLabel = 'Seluruh Periode';
        }

        $records = $query->get();

        // 1. Calculate Income (Application Layer)
        $incomeRecords = $records->where('type', 'income');
        $totalIncome = (float) $incomeRecords->sum('amount');

        $vaIncome = (float) $incomeRecords->where('reference_type', 'va_payment')->sum('amount');
        $manualIncome = (float) $incomeRecords->where('reference_type', 'manual_payment')->sum('amount');
        $otherIncome = (float) $incomeRecords->whereNotIn('reference_type', ['va_payment', 'manual_payment'])->sum('amount');

        $incomeBreakdown = [];
        if ($vaIncome > 0 || $totalIncome === 0.0) {
            $incomeBreakdown[] = [
                'name'   => 'Pendapatan Sewa Kamar (Midtrans VA)',
                'amount' => $vaIncome,
                'share'  => $totalIncome > 0 ? round(($vaIncome / $totalIncome) * 100, 1) : 0,
            ];
        }
        if ($manualIncome > 0) {
            $incomeBreakdown[] = [
                'name'   => 'Pendapatan Sewa Kamar (Transfer Manual)',
                'amount' => $manualIncome,
                'share'  => $totalIncome > 0 ? round(($manualIncome / $totalIncome) * 100, 1) : 0,
            ];
        }
        if ($otherIncome > 0) {
            $incomeBreakdown[] = [
                'name'   => 'Pendapatan Lain-lain',
                'amount' => $otherIncome,
                'share'  => $totalIncome > 0 ? round(($otherIncome / $totalIncome) * 100, 1) : 0,
            ];
        }
        if (empty($incomeBreakdown)) {
            $incomeBreakdown[] = [
                'name'   => 'Pendapatan Sewa Kamar',
                'amount' => 0.0,
                'share'  => 0,
            ];
        }

        // 2. Calculate Expenses (Application Layer)
        $expenseRecords = $records->where('type', 'expense');
        $totalExpenses = (float) $expenseRecords->sum('amount');

        $payrollExpenses = (float) $expenseRecords->where('reference_type', 'payroll')->sum('amount');
        $cashAdvanceExpenses = (float) $expenseRecords->where('reference_type', 'cash_advance')->sum('amount');

        // Group operational expenses by category
        $categoryExpenses = [];
        foreach ($expenseRecords->where('reference_type', 'expense') as $expRecord) {
            $catName = $expRecord->expense?->category?->name ?? 'Operasional Umum';
            if (!isset($categoryExpenses[$catName])) {
                $categoryExpenses[$catName] = 0.0;
            }
            $categoryExpenses[$catName] += (float) $expRecord->amount;
        }

        // Other expenses not in standard reference types
        $miscExpenses = (float) $expenseRecords->whereNotIn('reference_type', ['expense', 'payroll', 'cash_advance'])->sum('amount');

        $expenseBreakdown = [];
        foreach ($categoryExpenses as $categoryName => $catAmount) {
            $expenseBreakdown[] = [
                'name'   => 'Biaya ' . $categoryName,
                'amount' => $catAmount,
                'share'  => $totalExpenses > 0 ? round(($catAmount / $totalExpenses) * 100, 1) : 0,
            ];
        }

        if ($payrollExpenses > 0) {
            $expenseBreakdown[] = [
                'name'   => 'Beban Gaji & Upah Staf',
                'amount' => $payrollExpenses,
                'share'  => $totalExpenses > 0 ? round(($payrollExpenses / $totalExpenses) * 100, 1) : 0,
            ];
        }

        if ($cashAdvanceExpenses > 0) {
            $expenseBreakdown[] = [
                'name'   => 'Penyaluran Kasbon Staf',
                'amount' => $cashAdvanceExpenses,
                'share'  => $totalExpenses > 0 ? round(($cashAdvanceExpenses / $totalExpenses) * 100, 1) : 0,
            ];
        }

        if ($miscExpenses > 0) {
            $expenseBreakdown[] = [
                'name'   => 'Beban Operasional Lain-lain',
                'amount' => $miscExpenses,
                'share'  => $totalExpenses > 0 ? round(($miscExpenses / $totalExpenses) * 100, 1) : 0,
            ];
        }

        if (empty($expenseBreakdown)) {
            $expenseBreakdown[] = [
                'name'   => 'Beban Operasional',
                'amount' => 0.0,
                'share'  => 0,
            ];
        }

        // 3. Profit / Loss Calculations (Application Layer)
        $netProfitOrLoss = $totalIncome - $totalExpenses;
        $isProfit = $netProfitOrLoss > 0;
        $isLoss = $netProfitOrLoss < 0;
        $isBreakEven = $netProfitOrLoss == 0.0;

        $status = $isProfit ? 'profit' : ($isLoss ? 'loss' : 'break_even');
        $profitMargin = $totalIncome > 0 ? round(($netProfitOrLoss / $totalIncome) * 100, 1) : 0.0;
        $expenseRatio = $totalIncome > 0 ? round(($totalExpenses / $totalIncome) * 100, 1) : 0.0;

        // Formatted strings computed at application layer
        $formattedTotalIncome = 'Rp ' . number_format($totalIncome, 0, ',', '.');
        $formattedTotalExpenses = 'Rp ' . number_format($totalExpenses, 0, ',', '.');
        $formattedNetProfitOrLoss = ($isProfit ? '+' : ($isLoss ? '-' : '')) . 'Rp ' . number_format(abs($netProfitOrLoss), 0, ',', '.');

        $locations = Location::orderBy('name')->get(['id', 'name']);

        $minRecordYear = FinancialRecord::min('transaction_date');
        $minYear = $minRecordYear ? (int) Carbon::parse($minRecordYear)->year : now()->year - 2;
        $startYear = min($minYear, now()->year - 2);
        $availableYears = range(now()->year, $startYear);

        return view('admin.profit-loss.profit-loss-hub', [
            'totalIncome'              => $totalIncome,
            'totalExpenses'            => $totalExpenses,
            'netProfitOrLoss'          => $netProfitOrLoss,
            'isProfit'                 => $isProfit,
            'isLoss'                   => $isLoss,
            'isBreakEven'              => $isBreakEven,
            'status'                   => $status,
            'profitMargin'             => $profitMargin,
            'expenseRatio'             => $expenseRatio,
            'formattedTotalIncome'     => $formattedTotalIncome,
            'formattedTotalExpenses'   => $formattedTotalExpenses,
            'formattedNetProfitOrLoss' => $formattedNetProfitOrLoss,
            'incomeBreakdown'          => $incomeBreakdown,
            'expenseBreakdown'         => $expenseBreakdown,
            'periodLabel'              => $periodLabel,
            'timeScope'                => $timeScope,
            'month'                    => $month,
            'year'                     => $year,
            'locationId'               => $locationId,
            'locations'                => $locations,
            'availableYears'           => $availableYears,
            'transactionCount'         => $records->count(),
        ]);
    }
}
