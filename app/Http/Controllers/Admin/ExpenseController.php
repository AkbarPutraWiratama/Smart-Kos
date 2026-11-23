<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialRecord;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * List all expenses with optional filters.
     */
    public function index(Request $request): View
    {
        $search     = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');
        $locationId = $request->query('location_id');
        $month      = $request->query('month', now()->format('Y-m'));
        $timeScope  = $request->query('time_scope', 'month');

        $query = Expense::with(['category', 'location', 'recordedBy'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($timeScope === 'month' && $month) {
            [$year, $m] = explode('-', $month);
            $query->whereYear('expense_date', $year)->whereMonth('expense_date', $m);
        } elseif ($timeScope === 'year') {
            $query->whereYear('expense_date', substr((string) $month, 0, 4));
        }
        // 'all' → no date filter

        $expenses   = $query->paginate(15)->withQueryString();
        $totalAmount = (clone $query->getQuery())->sum('amount');

        // Re-execute scoped sum for display
        $sumQuery = Expense::query();
        if ($search !== '') {
            $sumQuery->where('title', 'like', "%{$search}%");
        }
        if ($categoryId) {
            $sumQuery->where('category_id', $categoryId);
        }
        if ($locationId) {
            $sumQuery->where('location_id', $locationId);
        }
        if ($timeScope === 'month' && $month) {
            [$year, $m] = explode('-', $month);
            $sumQuery->whereYear('expense_date', $year)->whereMonth('expense_date', $m);
        } elseif ($timeScope === 'year') {
            $sumQuery->whereYear('expense_date', substr((string) $month, 0, 4));
        }
        $totalAmount = $sumQuery->sum('amount');

        return view('admin.expenses.expense-list', [
            'expenses'    => $expenses,
            'totalAmount' => $totalAmount,
            'categories'  => ExpenseCategory::orderBy('type')->orderBy('name')->get(),
            'locations'   => Location::orderBy('name')->get(),
            'search'      => $search,
            'categoryId'  => $categoryId,
            'locationId'  => $locationId,
            'month'       => $month,
            'timeScope'   => $timeScope,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('admin.expenses.expense-create', [
            'categories' => ExpenseCategory::orderBy('type')->orderBy('name')->get(),
            'locations'  => Location::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new expense and sync to financial_records.
     */
    public function store(ExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'title'        => $data['title'],
            'category_id'  => $data['category_id'],
            'location_id'  => $data['location_id'] ?? null,
            'recorded_by'  => $request->user()->id,
            'amount'       => $data['amount'],
            'expense_date' => $data['expense_date'],
            'description'  => $data['description'] ?? null,
            'receipt_path' => $receiptPath,
        ]);

        // Sync to financial_records
        $this->syncFinancialRecord($expense, $request->user()->id);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Pengeluaran \"{$expense->title}\" sebesar Rp " . number_format($expense->amount, 0, ',', '.') . ' berhasil dicatat.');
    }

    /**
     * Show edit form.
     */
    public function edit(Expense $expense): View
    {
        return view('admin.expenses.expense-edit', [
            'expense'    => $expense,
            'categories' => ExpenseCategory::orderBy('type')->orderBy('name')->get(),
            'locations'  => Location::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update expense and re-sync financial_records.
     */
    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->update([
            'title'        => $data['title'],
            'category_id'  => $data['category_id'],
            'location_id'  => $data['location_id'] ?? null,
            'amount'       => $data['amount'],
            'expense_date' => $data['expense_date'],
            'description'  => $data['description'] ?? null,
            'receipt_path' => $data['receipt_path'] ?? $expense->receipt_path,
        ]);

        // Re-sync financial_records with updated values
        $this->syncFinancialRecord($expense->fresh(), $request->user()->id);

        return redirect()->route('admin.expenses.index')
            ->with('success', "Pengeluaran \"{$expense->title}\" berhasil diperbarui.");
    }

    /**
     * Delete expense (and its financial_record).
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $title = $expense->title;

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        // Remove linked financial record first
        FinancialRecord::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->delete();

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('warning', "Pengeluaran \"{$title}\" telah dihapus.");
    }

    /**
     * Create or update the financial_records entry for this expense.
     */
    private function syncFinancialRecord(Expense $expense, int $recordedBy): void
    {
        $category = $expense->relationLoaded('category') ? $expense->category : $expense->category()->first();

        FinancialRecord::updateOrCreate(
            [
                'reference_type' => 'expense',
                'reference_id'   => $expense->id,
            ],
            [
                'location_id'      => $expense->location_id,
                'recorded_by'      => $recordedBy,
                'type'             => 'expense',
                'description'      => $expense->title . ($category ? ' (' . $category->name . ')' : ''),
                'amount'           => $expense->amount,
                'transaction_date' => $expense->expense_date->toDateString(),
            ]
        );
    }
}
