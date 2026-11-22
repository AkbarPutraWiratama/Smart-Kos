<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualPayment;
use App\Models\VAPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->query('per_page', 5);
        $perPage = in_array($perPage, [5, 10], true) ? $perPage : 5;

        $vaPayments = VAPayment::query()
            ->with(['roomAssignment.room.location', 'roomAssignment.tenantProfile.user'])
            ->orderByDesc('transaction_time')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'va_page')
            ->withQueryString();

        $manualPayments = ManualPayment::query()
            ->with(['roomAssignment.room.location', 'roomAssignment.tenantProfile.user', 'submittedBy', 'verifiedBy'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'manual_page')
            ->withQueryString();

        return view('admin.finance.finance-overview', compact(
            'vaPayments',
            'manualPayments',
            'perPage'
        ));
    }
}
