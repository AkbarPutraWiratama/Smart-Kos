<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Services\FinancialRecordSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashFlowController extends Controller
{
    /**
     * Display monthly cash flow visualization and breakdown.
     */
    public function index(Request $request): View
    {
        // Ensure financial records are synchronized with latest approved payments, expenses, and payroll
        FinancialRecordSyncService::syncIfNeeded();

        $selectedYear = (int) $request->query('year', now()->year);
        $selectedLocationId = $request->query('location_id');
        $selectedMonthNum = (int) $request->query('month', ($selectedYear === (int) now()->year ? (int) now()->format('n') : 1));

        if ($selectedMonthNum < 1 || $selectedMonthNum > 12) {
            $selectedMonthNum = 1;
        }

        // Query all financial records for the selected year
        $recordsQuery = FinancialRecord::query()
            ->whereBetween('transaction_date', ["{$selectedYear}-01-01", "{$selectedYear}-12-31"]);

        if ($selectedLocationId) {
            $recordsQuery->where('location_id', $selectedLocationId);
        }

        $records = $recordsQuery->get(['type', 'amount', 'transaction_date']);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $shortMonthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = [
                'month'       => $m,
                'name'        => $monthNames[$m],
                'short_name'  => $shortMonthNames[$m],
                'income'      => 0.0,
                'expense'     => 0.0,
                'net'         => 0.0,
                'status'      => 'balanced',
            ];
        }

        foreach ($records as $record) {
            $m = (int) Carbon::parse($record->transaction_date)->format('n');
            if (isset($monthlyData[$m])) {
                if ($record->type === 'income') {
                    $monthlyData[$m]['income'] += (float) $record->amount;
                } elseif ($record->type === 'expense') {
                    $monthlyData[$m]['expense'] += (float) $record->amount;
                }
            }
        }

        $totalIncome = 0.0;
        $totalExpense = 0.0;

        foreach ($monthlyData as $m => &$data) {
            $data['net'] = $data['income'] - $data['expense'];
            if ($data['net'] > 0) {
                $data['status'] = 'surplus';
            } elseif ($data['net'] < 0) {
                $data['status'] = 'deficit';
            } else {
                $data['status'] = 'balanced';
            }
            $totalIncome += $data['income'];
            $totalExpense += $data['expense'];
        }
        unset($data);

        $totalNet = $totalIncome - $totalExpense;
        $activeMonths = count(array_filter($monthlyData, fn ($d) => $d['income'] > 0 || $d['expense'] > 0));
        $avgMonthlyNet = $activeMonths > 0 ? $totalNet / $activeMonths : 0.0;

        $highlightData = $monthlyData[$selectedMonthNum] ?? [
            'name'       => $monthNames[$selectedMonthNum] ?? 'Bulan',
            'short_name' => $shortMonthNames[$selectedMonthNum] ?? 'Bln',
            'income'     => 0.0,
            'expense'    => 0.0,
            'net'        => 0.0,
            'status'     => 'balanced',
        ];

        $locations = Location::orderBy('name')->get(['id', 'name']);

        $minRecordYear = FinancialRecord::min('transaction_date');
        $minYear = $minRecordYear ? (int) Carbon::parse($minRecordYear)->year : now()->year - 2;
        $startYear = min($minYear, now()->year - 2);
        $availableYears = range(now()->year, $startYear);

        return view('admin.cash-flow.cash-flow-hub', [
            'monthlyData'        => $monthlyData,
            'totalIncome'        => $totalIncome,
            'totalExpense'       => $totalExpense,
            'totalNet'           => $totalNet,
            'avgMonthlyNet'      => $avgMonthlyNet,
            'selectedMonthNum'   => $selectedMonthNum,
            'highlightData'      => $highlightData,
            'selectedYear'       => $selectedYear,
            'selectedLocationId' => $selectedLocationId,
            'locations'          => $locations,
            'availableYears'     => $availableYears,
        ]);
    }
}
