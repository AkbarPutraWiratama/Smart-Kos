<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\Room;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    /**
     * Display the financial reporting dashboard (P&L, Cash Flow, Location, Room & Tenant recaps).
     */
    public function index(Request $request): View
    {
        // Ensure historical approved/settled payments are synced into reporting layer
        FinancialRecord::syncFromPayments();

        $locations = Location::where('status', 'active')->orderBy('name')->get();

        // Filter parameters
        $selectedLocationId = $request->input('location_id', 'all');
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('n')); // 'all' or 1..12
        $selectedType = $request->input('type', 'all'); // 'all' | 'income' | 'expense'
        $search = trim((string) $request->input('search', ''));
        $activeTab = $request->input('tab', 'overview'); // 'overview' | 'rooms' | 'tenants' | 'records'

        // Determine date range for current filter
        if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
            $periodStart = Carbon::createFromDate($selectedYear, (int) $selectedMonth, 1)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
        } else {
            $periodStart = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $periodEnd = $periodStart->copy()->endOfYear();
        }

        // Base query for filtered transactions table
        $recordsQuery = FinancialRecord::with(['location', 'recordedBy']);

        // Apply Location Filter
        if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
            $recordsQuery->where('location_id', (int) $selectedLocationId);
        }

        // Apply Date Range Filter
        $recordsQuery->whereBetween('transaction_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

        // Apply Type Filter
        if (in_array($selectedType, [FinancialRecord::TYPE_INCOME, FinancialRecord::TYPE_EXPENSE], true)) {
            $recordsQuery->where('type', $selectedType);
        }

        // Apply Search
        if (! empty($search)) {
            $recordsQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference_type', 'like', "%{$search}%")
                    ->orWhereHas('location', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('recordedBy', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        // Summary metrics query (for the selected year, month, and location)
        $metricsQuery = FinancialRecord::query();
        if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
            $metricsQuery->where('location_id', (int) $selectedLocationId);
        }
        $metricsQuery->whereBetween('transaction_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

        $totalIncome = (float) (clone $metricsQuery)->where('type', FinancialRecord::TYPE_INCOME)->sum('amount');
        $totalExpense = (float) (clone $metricsQuery)->where('type', FinancialRecord::TYPE_EXPENSE)->sum('amount');
        $netProfit = $totalIncome - $totalExpense;
        $totalTransactions = (clone $metricsQuery)->count();

        // ── 1. Monthly Breakdown for the selected Year (12 Months Cash Flow) ──
        $monthlyReport = [];
        $monthsNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::createFromDate($selectedYear, $m, 1)->startOfMonth();
            $mEnd = $mStart->copy()->endOfMonth();

            $mq = FinancialRecord::whereBetween('transaction_date', [$mStart->toDateString(), $mEnd->toDateString()]);
            if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
                $mq->where('location_id', (int) $selectedLocationId);
            }

            $mIncome = (float) (clone $mq)->where('type', FinancialRecord::TYPE_INCOME)->sum('amount');
            $mExpense = (float) (clone $mq)->where('type', FinancialRecord::TYPE_EXPENSE)->sum('amount');
            $mNet = $mIncome - $mExpense;

            $monthlyReport[] = [
                'month_number' => $m,
                'month_name'   => $monthsNames[$m],
                'income'       => $mIncome,
                'expense'      => $mExpense,
                'net'          => $mNet,
                'status'       => $mNet > 0 ? 'Surplus' : ($mNet < 0 ? 'Defisit' : 'Seimbang'),
            ];
        }

        // ── Cash Flow Visualization Data (Phase 31) ──
        $chartCategories = array_map(fn ($m) => substr($m, 0, 3), $monthsNames);
        $chartIncome = array_column($monthlyReport, 'income');
        $chartExpense = array_column($monthlyReport, 'expense');
        $chartNet = array_column($monthlyReport, 'net');

        $cashFlowTurnover = $totalIncome + $totalExpense;
        $incomePercentage = $cashFlowTurnover > 0 ? round(($totalIncome / $cashFlowTurnover) * 100, 1) : 0;
        $expensePercentage = $cashFlowTurnover > 0 ? round(($totalExpense / $cashFlowTurnover) * 100, 1) : 0;

        $peakIncomeMonth = collect($monthlyReport)->sortByDesc('income')->first();
        $peakExpenseMonth = collect($monthlyReport)->sortByDesc('expense')->first();

        $cashFlowSummary = [
            'total_income'        => $totalIncome,
            'total_expense'       => $totalExpense,
            'net_flow'            => $netProfit,
            'turnover'            => $cashFlowTurnover,
            'income_percentage'   => $incomePercentage,
            'expense_percentage'  => $expensePercentage,
            'avg_monthly_income'  => round(array_sum($chartIncome) / 12, 0),
            'avg_monthly_expense' => round(array_sum($chartExpense) / 12, 0),
            'peak_income_month'   => $peakIncomeMonth['month_name'] ?? '-',
            'peak_income_amount'  => $peakIncomeMonth['income'] ?? 0,
            'peak_expense_month'  => $peakExpenseMonth['month_name'] ?? '-',
            'peak_expense_amount' => $peakExpenseMonth['expense'] ?? 0,
        ];

        // ── 2. Location Reporting Breakdown for the selected period ──
        $locationReport = [];
        foreach ($locations as $loc) {
            $lq = FinancialRecord::where('location_id', $loc->id)
                ->whereBetween('transaction_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

            $locIncome = (float) (clone $lq)->where('type', FinancialRecord::TYPE_INCOME)->sum('amount');
            $locExpense = (float) (clone $lq)->where('type', FinancialRecord::TYPE_EXPENSE)->sum('amount');
            $locNet = $locIncome - $locExpense;
            $contribution = $totalIncome > 0 ? round(($locIncome / $totalIncome) * 100, 1) : 0;

            $locationReport[] = [
                'location_id'   => $loc->id,
                'location_name' => $loc->name,
                'income'        => $locIncome,
                'expense'       => $locExpense,
                'net'           => $locNet,
                'contribution'  => $contribution,
            ];
        }

        // ── 3. Room Financial Recap (Full Kamar) ──
        $roomsQuery = Room::with([
            'location',
            'activeAssignment.tenantProfile.user',
            'activeAssignment.vaPayments',
            'activeAssignment.manualPayments',
            'roomAssignments.vaPayments',
            'roomAssignments.manualPayments',
        ]);

        if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
            $roomsQuery->where('location_id', (int) $selectedLocationId);
        }

        if (! empty($search)) {
            $roomsQuery->where(function ($q) use ($search) {
                $q->where('room_number', 'like', "%{$search}%")
                    ->orWhereHas('location', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('activeAssignment.tenantProfile.user', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        $allRooms = $roomsQuery->orderBy('location_id')->orderBy('floor_name')->orderBy('room_number')->get();

        $roomReport = $allRooms->map(function (Room $room) use ($periodStart, $periodEnd) {
            $activeAssignment = $room->activeAssignment;
            $isOccupied = $activeAssignment !== null;
            $activeTenant = $isOccupied ? $activeAssignment->tenantProfile?->user : null;

            // All-time income collected from this room (all past and current assignments)
            $allVaIncome = $room->roomAssignments->flatMap->vaPayments
                ->filter(fn ($vp) => $vp->isPaid())
                ->sum('amount');
            $allManualIncome = $room->roomAssignments->flatMap->manualPayments
                ->where('status', 'approved')
                ->sum('amount');
            $totalIncomeAllTime = (float) ($allVaIncome + $allManualIncome);

            // Income collected within current period filter
            $periodVaIncome = $room->roomAssignments->flatMap->vaPayments
                ->filter(function ($vp) use ($periodStart, $periodEnd) {
                    if (! $vp->isPaid()) {
                        return false;
                    }
                    $t = $vp->paid_at ?? $vp->transaction_time;
                    return $t && $t->between($periodStart, $periodEnd);
                })
                ->sum('amount');

            $periodManualIncome = $room->roomAssignments->flatMap->manualPayments
                ->filter(function ($mp) use ($periodStart, $periodEnd) {
                    if ($mp->status !== 'approved') {
                        return false;
                    }
                    $t = $mp->verified_at ?? $mp->submitted_at;
                    return $t && $t->between($periodStart, $periodEnd);
                })
                ->sum('amount');

            $totalIncomePeriod = (float) ($periodVaIncome + $periodManualIncome);

            // Payment status & arrears
            if ($isOccupied) {
                $status = $activeAssignment->getPaymentStatus();
                $isPaid = $activeAssignment->isPaidForCurrentPeriod();
                $dueDate = $activeAssignment->getPaymentDueDate();
                $daysUntilDue = $activeAssignment->getDaysUntilDue();
                $rentAmount = (float) ($activeAssignment->rent_amount_snapshot ?? $room->rent_amount);
                $outstanding = $isPaid ? 0.0 : $rentAmount;
            } else {
                $status = 'Kosong';
                $isPaid = false;
                $dueDate = null;
                $daysUntilDue = null;
                $rentAmount = (float) $room->rent_amount;
                $outstanding = 0.0;
            }

            return [
                'room_id'              => $room->id,
                'room_number'          => $room->room_number,
                'floor_name'           => $room->floor_name,
                'location_id'          => $room->location_id,
                'location_name'        => $room->location?->name ?? '-',
                'is_occupied'          => $isOccupied,
                'tenant_name'          => $activeTenant?->name ?? '-',
                'tenant_phone'         => $activeAssignment?->tenantProfile?->phone ?? '-',
                'rent_amount'          => $rentAmount,
                'total_income_all'     => $totalIncomeAllTime,
                'total_income_period'  => $totalIncomePeriod,
                'payment_status'       => $status,
                'is_paid'              => $isPaid,
                'due_date'             => $dueDate,
                'days_until_due'       => $daysUntilDue,
                'outstanding'          => $outstanding,
            ];
        });

        // Room Recap Summary Metrics
        $roomSummary = [
            'total_rooms'    => $roomReport->count(),
            'occupied_rooms' => $roomReport->where('is_occupied', true)->count(),
            'empty_rooms'    => $roomReport->where('is_occupied', false)->count(),
            'total_income'   => $roomReport->sum('total_income_all'),
            'period_income'  => $roomReport->sum('total_income_period'),
            'total_arrears'  => $roomReport->sum('outstanding'),
        ];

        // ── 4. Tenant Financial Recap (Per Orang / Penyewa) ──
        $tenantsQuery = TenantProfile::with([
            'user',
            'roomAssignments' => function ($q) {
                $q->with(['room.location', 'vaPayments', 'manualPayments'])->latest('start_date');
            },
        ]);

        if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
            $tenantsQuery->whereHas('roomAssignments.room', function ($q) use ($selectedLocationId) {
                $q->where('location_id', (int) $selectedLocationId);
            });
        }

        if (! empty($search)) {
            $tenantsQuery->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('roomAssignments.room', function ($sub) use ($search) {
                        $sub->where('room_number', 'like', "%{$search}%")
                            ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        $allTenants = $tenantsQuery->get();

        $tenantReport = $allTenants->map(function (TenantProfile $profile) use ($periodStart, $periodEnd) {
            $user = $profile->user;
            $activeAssignment = $profile->roomAssignments->firstWhere('status', 'active');
            $hasActiveAssignment = $activeAssignment !== null;

            // Total all-time payments made by this tenant
            $allVaIncome = $profile->roomAssignments->flatMap->vaPayments
                ->filter(fn ($vp) => $vp->isPaid())
                ->sum('amount');
            $allManualIncome = $profile->roomAssignments->flatMap->manualPayments
                ->where('status', 'approved')
                ->sum('amount');
            $totalPaidAllTime = (float) ($allVaIncome + $allManualIncome);

            $transactionsCount = $profile->roomAssignments->flatMap->vaPayments->filter(fn ($vp) => $vp->isPaid())->count()
                + $profile->roomAssignments->flatMap->manualPayments->where('status', 'approved')->count();

            // Period income
            $periodVa = $profile->roomAssignments->flatMap->vaPayments
                ->filter(function ($vp) use ($periodStart, $periodEnd) {
                    if (! $vp->isPaid()) {
                        return false;
                    }
                    $t = $vp->paid_at ?? $vp->transaction_time;
                    return $t && $t->between($periodStart, $periodEnd);
                })
                ->sum('amount');

            $periodManual = $profile->roomAssignments->flatMap->manualPayments
                ->filter(function ($mp) use ($periodStart, $periodEnd) {
                    if ($mp->status !== 'approved') {
                        return false;
                    }
                    $t = $mp->verified_at ?? $mp->submitted_at;
                    return $t && $t->between($periodStart, $periodEnd);
                })
                ->sum('amount');

            $totalPaidPeriod = (float) ($periodVa + $periodManual);

            if ($hasActiveAssignment) {
                $roomNumber = $activeAssignment->room?->room_number ?? '-';
                $locationName = $activeAssignment->room?->location?->name ?? '-';
                $rentAmount = (float) ($activeAssignment->rent_amount_snapshot ?? $activeAssignment->room?->rent_amount ?? 0);
                $status = $activeAssignment->getPaymentStatus();
                $isPaid = $activeAssignment->isPaidForCurrentPeriod();
                $dueDate = $activeAssignment->getPaymentDueDate();
                $daysUntilDue = $activeAssignment->getDaysUntilDue();
                $outstanding = $isPaid ? 0.0 : $rentAmount;
            } else {
                $roomNumber = '-';
                $locationName = 'Tidak Ada Kamar Aktif';
                $rentAmount = 0.0;
                $status = 'Tidak Ada Tagihan';
                $isPaid = true;
                $dueDate = null;
                $daysUntilDue = null;
                $outstanding = 0.0;
            }

            return [
                'tenant_id'             => $profile->id,
                'user_id'               => $user?->id,
                'name'                  => $user?->name ?? 'Penyewa',
                'username'              => $user?->username ?? '-',
                'email'                 => $user?->email ?? '-',
                'phone'                 => $profile->phone ?? '-',
                'user_status'           => $user?->status ?? 'active',
                'has_active_assignment' => $hasActiveAssignment,
                'room_number'           => $roomNumber,
                'location_name'         => $locationName,
                'rent_amount'           => $rentAmount,
                'total_paid_all'        => $totalPaidAllTime,
                'total_paid_period'     => $totalPaidPeriod,
                'transactions_count'    => $transactionsCount,
                'payment_status'        => $status,
                'is_paid'               => $isPaid,
                'due_date'              => $dueDate,
                'days_until_due'        => $daysUntilDue,
                'outstanding'           => $outstanding,
            ];
        });

        // Tenant Recap Summary Metrics
        $tenantSummary = [
            'total_tenants'     => $tenantReport->count(),
            'active_tenants'    => $tenantReport->where('has_active_assignment', true)->count(),
            'paid_tenants'      => $tenantReport->where('payment_status', 'Lunas')->count(),
            'unpaid_tenants'    => $tenantReport->where('payment_status', 'Belum Bayar')->count(),
            'total_paid'        => $tenantReport->sum('total_paid_all'),
            'total_outstanding' => $tenantReport->sum('outstanding'),
        ];

        // ── 5. Paginated Transaction Records ──
        $records = $recordsQuery->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.finance.index', compact(
            'locations',
            'selectedLocationId',
            'selectedYear',
            'selectedMonth',
            'selectedType',
            'search',
            'activeTab',
            'totalIncome',
            'totalExpense',
            'netProfit',
            'totalTransactions',
            'monthlyReport',
            'locationReport',
            'roomReport',
            'roomSummary',
            'tenantReport',
            'tenantSummary',
            'records',
            'monthsNames',
            'chartCategories',
            'chartIncome',
            'chartExpense',
            'chartNet',
            'cashFlowSummary'
        ));
    }
}
