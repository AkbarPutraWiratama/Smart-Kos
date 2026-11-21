<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialRecord;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses (operational & maintenance) with filtering and metric summaries.
     */
    public function index(Request $request): View
    {
        ExpenseCategory::seedDefaultsIfEmpty();

        $locations = Location::where('status', 'active')->orderBy('name')->get();
        $categories = ExpenseCategory::orderBy('type')->orderBy('name')->get();

        // Filter parameters
        $selectedLocationId = $request->input('location_id', 'all');
        $selectedCategoryId = $request->input('category_id', 'all');
        $selectedType = $request->input('type', 'all'); // 'all' | 'operational' | 'maintenance' | 'other'
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('n')); // 'all' or 1..12
        $search = trim((string) $request->input('search', ''));

        // Determine date range for current period filter
        if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
            $periodStart = Carbon::createFromDate($selectedYear, (int) $selectedMonth, 1)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();
        } else {
            $periodStart = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $periodEnd = $periodStart->copy()->endOfYear();
        }

        // Base query for expenses
        $query = Expense::with(['category', 'location', 'recordedBy'])
            ->whereBetween('expense_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);

        if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
            $query->where('location_id', (int) $selectedLocationId);
        }

        if (! empty($selectedCategoryId) && $selectedCategoryId !== 'all') {
            $query->where('category_id', (int) $selectedCategoryId);
        }

        if (in_array($selectedType, [ExpenseCategory::TYPE_OPERATIONAL, ExpenseCategory::TYPE_MAINTENANCE, ExpenseCategory::TYPE_OTHER], true)) {
            $query->whereHas('category', fn ($q) => $q->where('type', $selectedType));
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('location', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        // Summary metrics calculation for the filtered period & location
        $metricsQuery = Expense::whereBetween('expense_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
        if (! empty($selectedLocationId) && $selectedLocationId !== 'all') {
            $metricsQuery->where('location_id', (int) $selectedLocationId);
        }

        $totalExpensesAmount = (float) (clone $metricsQuery)->sum('amount');
        $operationalTotal = (float) (clone $metricsQuery)->whereHas('category', fn ($q) => $q->where('type', ExpenseCategory::TYPE_OPERATIONAL))->sum('amount');
        $maintenanceTotal = (float) (clone $metricsQuery)->whereHas('category', fn ($q) => $q->where('type', ExpenseCategory::TYPE_MAINTENANCE))->sum('amount');
        $otherTotal = (float) (clone $metricsQuery)->whereHas('category', fn ($q) => $q->where('type', ExpenseCategory::TYPE_OTHER))->sum('amount');
        $totalExpensesCount = (clone $metricsQuery)->count();

        // Paginated results
        $expenses = $query->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $monthsNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('admin.expenses.index', compact(
            'expenses',
            'locations',
            'categories',
            'selectedLocationId',
            'selectedCategoryId',
            'selectedType',
            'selectedYear',
            'selectedMonth',
            'search',
            'totalExpensesAmount',
            'operationalTotal',
            'maintenanceTotal',
            'otherTotal',
            'totalExpensesCount',
            'monthsNames'
        ));
    }

    /**
     * Store a newly created expense in storage and sync to financial records.
     */
    public function store(ExpenseRequest $request): RedirectResponse
    {
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'location_id'  => $request->input('location_id') ?: null,
            'category_id'  => (int) $request->input('category_id'),
            'recorded_by'  => $request->user()->id,
            'title'        => trim((string) $request->input('title')),
            'description'  => $request->input('description') ? trim((string) $request->input('description')) : null,
            'amount'       => $request->input('amount'),
            'expense_date' => $request->input('expense_date'),
            'receipt_path' => $receiptPath,
        ]);

        // Eager load category and location before recording into financial_records
        $expense->load('category', 'location');
        FinancialRecord::recordFromExpense($expense);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Pengeluaran \"{$expense->title}\" berhasil dicatat.");
    }

    /**
     * Update the specified expense in storage and sync to financial records.
     */
    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $receiptPath = $expense->receipt_path;

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path && Storage::disk('public')->exists($expense->receipt_path)) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->update([
            'location_id'  => $request->input('location_id') ?: null,
            'category_id'  => (int) $request->input('category_id'),
            'title'        => trim((string) $request->input('title')),
            'description'  => $request->input('description') ? trim((string) $request->input('description')) : null,
            'amount'       => $request->input('amount'),
            'expense_date' => $request->input('expense_date'),
            'receipt_path' => $receiptPath,
        ]);

        $expense->load('category', 'location');
        FinancialRecord::recordFromExpense($expense);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Data pengeluaran \"{$expense->title}\" berhasil diperbarui.");
    }

    /**
     * Remove the specified expense from storage and remove from financial records.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $title = $expense->title;

        if ($expense->receipt_path && Storage::disk('public')->exists($expense->receipt_path)) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        // Delete associated financial record
        FinancialRecord::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->delete();

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', "Pengeluaran \"{$title}\" berhasil dihapus.");
    }
}
