<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SensitiveActionConfirmRequest;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Display a listing of staff accounts.
     */
    public function index(Request $request): View
    {
        $query = User::where('role', 'staff');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            }
        }

        $staffMembers = $query->latest()->paginate(10)->withQueryString();

        $metrics = [
            'total'    => User::where('role', 'staff')->count(),
            'active'   => User::where('role', 'staff')->where('status', 'active')->count(),
            'inactive' => User::where('role', 'staff')->where('status', 'inactive')->count(),
        ];

        return view('admin.staff.index', compact('staffMembers', 'metrics'));
    }

    /**
     * Show the form for creating a new staff account.
     */
    public function create(): View
    {
        return view('admin.staff.create');
    }

    /**
     * Store a newly created staff account in storage.
     */
    public function store(StaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Akun staff baru berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified staff account.
     */
    public function edit(User $staff): View
    {
        if ($staff->role !== 'staff') {
            abort(404);
        }

        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff account in storage.
     */
    public function update(StaffRequest $request, User $staff): RedirectResponse
    {
        if ($staff->role !== 'staff') {
            abort(404);
        }

        $validated = $request->validated();

        $userData = [
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $staff->update($userData);

        return redirect()->route('admin.staff.index')
            ->with('success', "Akun staff {$staff->name} berhasil diperbarui.");
    }

    /**
     * Deactivate the specified staff account.
     * Note: Historical financial information & records are preserved permanently.
     */
    public function deactivate(SensitiveActionConfirmRequest $request, User $staff): RedirectResponse
    {
        if ($staff->role !== 'staff') {
            abort(404);
        }

        $staff->update([
            'status'      => 'inactive',
            'inactive_at' => now(),
        ]);

        return back()->with('success', "Akun staff {$staff->name} berhasil dinonaktifkan.");
    }

    /**
     * Reactivate the specified staff account.
     */
    public function reactivate(SensitiveActionConfirmRequest $request, User $staff): RedirectResponse
    {
        if ($staff->role !== 'staff') {
            abort(404);
        }

        $staff->update([
            'status'      => 'active',
            'inactive_at' => null,
        ]);

        return back()->with('success', "Akun staff {$staff->name} berhasil diaktifkan kembali.");
    }

    /**
     * Reset the staff member's password by admin.
     */
    public function resetPassword(SensitiveActionConfirmRequest $request, User $staff): RedirectResponse
    {
        if ($staff->role !== 'staff') {
            abort(404);
        }

        $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.min'       => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $staff->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('success', "Password akun staff {$staff->name} berhasil direset.");
    }

    /**
     * Delete an inactive staff account that has passed the 7-day retention period.
     * Invariant: Never delete an account that has historical financial records.
     */
    public function destroy(SensitiveActionConfirmRequest $request, User $staff): RedirectResponse
    {
        if ($staff->role !== 'staff') {
            abort(404);
        }

        if (! $staff->isEligibleForDeletion(7)) {
            $remaining = $staff->daysUntilDeletionEligible(7);
            return back()->withErrors([
                'deletion' => "Akun staff belum memenuhi masa tenggang 7 hari nonaktif (tersisa {$remaining} hari lagi).",
            ]);
        }

        // Invariant: Permanent deletion must not destroy historical financial records
        if ($staff->hasFinancialOrOperationalHistory()) {
            return back()->withErrors([
                'deletion' => 'Akun staff ini memiliki riwayat pencatatan transaksi, penggajian, kasbon, atau operasional yang harus dilestarikan dan tidak dapat dihapus permanen.',
            ]);
        }

        $staffName = $staff->name;
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', "Akun staff {$staffName} berhasil dihapus permanen.");
    }
}

