<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Room;
use App\Models\User;
use App\Models\VAPayment;
use App\Services\FinancialRecordSyncService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->query('tab') === 'expenses') {
            return redirect()->route('admin.expenses.index', $request->except('tab'));
        }

        if ($request->query('tab') === 'payroll') {
            return redirect()->route('admin.payroll.index', $request->except('tab'));
        }

        if ($request->query('tab') === 'cash-flow') {
            return redirect()->route('admin.cash-flow.index', $request->except('tab'));
        }

        if ($request->query('tab') === 'profit-loss') {
            return redirect()->route('admin.profit-loss.index', $request->except('tab'));
        }

        if ($request->query('tab') === 'arrears') {
            return redirect()->route('admin.arrears.index', $request->except('tab'));
        }

        if ($request->query('tab') === 'recap') {
            return redirect()->route('admin.financial-recap.index', $request->except('tab'));
        }

        // Ensure financial records are always accurately synchronized with approved payments and expenses
        FinancialRecordSyncService::syncIfNeeded();

        $requestedScope = $request->query('scope', 'all');
        $scope = is_string($requestedScope) ? $requestedScope : 'all';
        $requestedTimeScope = $request->query('time_scope', 'month');
        $timeScope = is_string($requestedTimeScope) && in_array($requestedTimeScope, ['all', 'year', 'month']) ? $requestedTimeScope : 'month';

        $filters = Validator::make($request->query(), [
            'scope' => ['nullable', Rule::in(['all', 'location', 'room', 'person'])],
            'location_id' => [Rule::requiredIf($scope === 'location'), 'nullable', 'integer', 'exists:locations,id'],
            'room_id' => [Rule::requiredIf($scope === 'room'), 'nullable', 'integer', 'exists:rooms,id'],
            'person_id' => [Rule::requiredIf($scope === 'person'), 'nullable', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['penyewa', 'staff']))],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'time_scope' => ['nullable', Rule::in(['all', 'year', 'month'])],
            'per_page' => ['nullable', 'integer', Rule::in([5, 10])],
        ])->validate();

        $scope = $filters['scope'] ?? 'all';
        $scopeValues = [
            'location_id' => isset($filters['location_id']) ? (int) $filters['location_id'] : null,
            'room_id' => isset($filters['room_id']) ? (int) $filters['room_id'] : null,
            'person_id' => isset($filters['person_id']) ? (int) $filters['person_id'] : null,
        ];
        $month = $filters['month'] ?? now()->format('Y-m');
        $year = isset($filters['year']) ? (int) $filters['year'] : (int) substr($month, 0, 4);
        $perPage = (int) ($filters['per_page'] ?? 5);

        $reportQuery = FinancialRecord::query()
            ->with([
                'location',
                'recordedBy',
                'vaPayment.roomAssignment.room.location',
                'vaPayment.roomAssignment.tenantProfile.user',
                'vaPayment.vaAccount.room.location',
                'manualPayment.roomAssignment.room.location',
                'manualPayment.roomAssignment.tenantProfile.user',
                'manualPayment.submittedBy',
                'manualPayment.verifiedBy',
                'expense.category',
                'expense.recordedBy',
                'expense.location',
                'payrollRecord.staff',
                'cashAdvance.staff',
            ]);

        if ($timeScope === 'month') {
            $reportQuery->whereYear('transaction_date', substr($month, 0, 4))
                ->whereMonth('transaction_date', substr($month, 5, 2));
        } elseif ($timeScope === 'year') {
            $reportQuery->whereYear('transaction_date', $year);
        }
        // 'all' → no date filter
        $this->applyReportScope($reportQuery, $scope, $scopeValues);

        $totalIncome = (clone $reportQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $reportQuery)->where('type', 'expense')->sum('amount');
        $netTotal = (float) $totalIncome - (float) $totalExpense;
        $recordCount = (clone $reportQuery)->count();
        $financialRecords = (clone $reportQuery)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'report_page')
            ->withQueryString();

        $vaQuery = VAPayment::query()
            ->with(['roomAssignment.room.location', 'roomAssignment.tenantProfile.user']);
        $manualQuery = ManualPayment::query()
            ->with(['roomAssignment.room.location', 'roomAssignment.tenantProfile.user', 'submittedBy', 'verifiedBy']);

        if ($timeScope === 'month') {
            $vaQuery->whereYear('transaction_time', substr($month, 0, 4))->whereMonth('transaction_time', substr($month, 5, 2));
            $manualQuery->whereYear('submitted_at', substr($month, 0, 4))->whereMonth('submitted_at', substr($month, 5, 2));
        } elseif ($timeScope === 'year') {
            $vaQuery->whereYear('transaction_time', $year);
            $manualQuery->whereYear('submitted_at', $year);
        }

        $this->applyPaymentScope($vaQuery, $scope, $scopeValues);
        $this->applyPaymentScope($manualQuery, $scope, $scopeValues, true);

        $vaPayments = $vaQuery->orderByDesc('transaction_time')->orderByDesc('id')
            ->paginate($perPage, ['*'], 'va_page')->withQueryString();
        $manualPayments = $manualQuery->orderByDesc('submitted_at')->orderByDesc('id')
            ->paginate($perPage, ['*'], 'manual_page')->withQueryString();

        $timeScopeLabel = match ($timeScope) {
            'all'   => 'Semua periode',
            'year'  => 'Tahun ' . $year,
            default => \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y'),
        };

        return view('admin.finance.finance-overview', [
            'vaPayments' => $vaPayments,
            'manualPayments' => $manualPayments,
            'financialRecords' => $financialRecords,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netTotal' => $netTotal,
            'recordCount' => $recordCount,
            'perPage' => $perPage,
            'scope' => $scope,
            'scopeValues' => $scopeValues,
            'month' => $month,
            'year' => $year,
            'timeScope' => $timeScope,
            'timeScopeLabel' => $timeScopeLabel,
            'locations' => Location::query()->orderBy('name')->get(),
            'rooms' => Room::query()->with('location')->orderBy('room_number')->get(),
            'people' => User::query()->whereIn('role', ['penyewa', 'staff'])->orderBy('name')->get(),
        ]);
    }

    private function applyReportScope(Builder $query, string $scope, array $values): void
    {
        if ($scope === 'location') {
            $locationId = $values['location_id'];
            $query->where(function (Builder $records) use ($locationId): void {
                $records->where('location_id', $locationId)
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'va_payment')
                        ->whereHas('vaPayment.roomAssignment.room', fn (Builder $rooms) => $rooms->where('location_id', $locationId)))
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'manual_payment')
                        ->whereHas('manualPayment.roomAssignment.room', fn (Builder $rooms) => $rooms->where('location_id', $locationId)))
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'expense')
                        ->whereHas('expense', fn (Builder $expenses) => $expenses->where('location_id', $locationId)));
            });
        } elseif ($scope === 'room') {
            $roomId = $values['room_id'];
            $query->where(function (Builder $records) use ($roomId): void {
                $records->where(fn (Builder $source) => $source->where('reference_type', 'va_payment')
                    ->whereHas('vaPayment.roomAssignment', fn (Builder $assignments) => $assignments->where('room_id', $roomId)))
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'manual_payment')
                        ->whereHas('manualPayment.roomAssignment', fn (Builder $assignments) => $assignments->where('room_id', $roomId)));
            });
        } elseif ($scope === 'person') {
            $personId = $values['person_id'];
            $query->where(function (Builder $records) use ($personId): void {
                $records->where('recorded_by', $personId)
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'va_payment')
                        ->whereHas('vaPayment.roomAssignment.tenantProfile', fn (Builder $tenants) => $tenants->where('user_id', $personId)))
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'manual_payment')
                        ->where(function (Builder $payments) use ($personId): void {
                            $payments->whereHas('manualPayment', fn (Builder $payment) => $payment->where('submitted_by', $personId)->orWhere('verified_by', $personId))
                                ->orWhereHas('manualPayment.roomAssignment.tenantProfile', fn (Builder $tenants) => $tenants->where('user_id', $personId));
                        }))
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'expense')
                        ->whereHas('expense', fn (Builder $expenses) => $expenses->where('recorded_by', $personId)))
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'payroll')
                        ->whereHas('payrollRecord', fn (Builder $payroll) => $payroll->where('staff_id', $personId)))
                    ->orWhere(fn (Builder $source) => $source->where('reference_type', 'cash_advance')
                        ->whereHas('cashAdvance', fn (Builder $advances) => $advances->where('staff_id', $personId)));
            });
        }
    }

    private function applyPaymentScope(Builder $query, string $scope, array $values, bool $manual = false): void
    {
        if ($scope === 'location') {
            $query->whereHas('roomAssignment.room', fn (Builder $rooms) => $rooms->where('location_id', $values['location_id']));
        } elseif ($scope === 'room') {
            $query->whereHas('roomAssignment', fn (Builder $assignments) => $assignments->where('room_id', $values['room_id']));
        } elseif ($scope === 'person') {
            $query->where(function (Builder $payments) use ($values, $manual): void {
                $payments->whereHas('roomAssignment.tenantProfile', fn (Builder $tenants) => $tenants->where('user_id', $values['person_id']));

                if ($manual) {
                    $payments->orWhere('submitted_by', $values['person_id'])
                        ->orWhere('verified_by', $values['person_id']);
                }
            });
        }
    }
}
