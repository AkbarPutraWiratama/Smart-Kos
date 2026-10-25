<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SensitiveActionConfirmRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of penyewa (tenants).
     */
    public function index(Request $request): View
    {
        $query = User::with(['tenantProfile', 'tenantProfile.roomAssignments' => function ($q) {
            $q->where('status', 'active')->with('room.location');
        }])->where('role', 'penyewa');

        // Optional search by name, username, or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Optional filter by status
        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'inactive'])) {
                $query->where('status', $status);
            }
        }

        $tenants = $query->latest()->paginate(10)->withQueryString();

        $metrics = [
            'total' => User::where('role', 'penyewa')->count(),
            'active' => User::where('role', 'penyewa')->where('status', 'active')->count(),
            'inactive' => User::where('role', 'penyewa')->where('status', 'inactive')->count(),
        ];

        return view('admin.users.index', compact('tenants', 'metrics'));
    }

    /**
     * Show the form for creating a new tenant account.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created tenant account in storage.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'penyewa',
                'status'   => 'active',
            ]);

            $user->tenantProfile()->create([
                'phone'         => $validated['phone'] ?? null,
                'address'       => $validated['address'] ?? null,
                'registered_at' => $validated['registered_at'] ?? now()->toDateString(),
            ]);
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun penyewa baru berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified tenant account.
     */
    public function edit(User $user): View
    {
        // Guard: only allow editing penyewa via this controller
        if ($user->role !== 'penyewa') {
            abort(404);
        }

        $user->load(['tenantProfile', 'tenantProfile.roomAssignments' => function ($q) {
            $q->where('status', 'active')->with('room.location');
        }]);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified tenant account in storage.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'penyewa') {
            abort(404);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $user) {
            $userData = [
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            $user->tenantProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone'         => $validated['phone'] ?? null,
                    'address'       => $validated['address'] ?? null,
                    'registered_at' => $validated['registered_at'] ?? null,
                ]
            );
        });

        return redirect()->route('admin.users.index')
            ->with('success', "Akun penyewa {$user->name} berhasil diperbarui.");
    }

    /**
     * Deactivate the specified tenant account.
     */
    public function deactivate(SensitiveActionConfirmRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'penyewa') {
            abort(404);
        }

        $user->update([
            'status'      => 'inactive',
            'inactive_at' => now(),
        ]);

        return back()->with('success', "Akun penyewa {$user->name} berhasil dinonaktifkan.");
    }

    /**
     * Reactivate the specified tenant account.
     */
    public function reactivate(SensitiveActionConfirmRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'penyewa') {
            abort(404);
        }

        $user->update([
            'status'      => 'active',
            'inactive_at' => null,
        ]);

        return back()->with('success', "Akun penyewa {$user->name} berhasil diaktifkan kembali.");
    }

    /**
     * Reset the tenant's password by admin.
     */
    public function resetPassword(SensitiveActionConfirmRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'penyewa') {
            abort(404);
        }

        $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.min'       => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('success', "Password akun penyewa {$user->name} berhasil direset.");
    }

    /**
     * Delete an inactive tenant account that has passed the 7-day retention period.
     * Architectural invariant: Financial and operational history must be permanently preserved.
     */
    public function destroy(SensitiveActionConfirmRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'penyewa') {
            abort(404);
        }

        if (! $user->isEligibleForDeletion(7)) {
            $remaining = $user->daysUntilDeletionEligible(7);
            return back()->withErrors([
                'deletion' => "Akun penyewa belum memenuhi masa tenggang 7 hari nonaktif (tersisa {$remaining} hari lagi).",
            ]);
        }

        // Invariant: Do not delete account if active or historical financial/assignment records exist
        if ($user->hasFinancialOrOperationalHistory()) {
            return back()->withErrors([
                'deletion' => 'Akun penyewa ini memiliki riwayat kamar atau transaksi finansial yang harus dilestarikan dan tidak dapat dihapus permanen.',
            ]);
        }

        $userName = $user->name;

        DB::transaction(function () use ($user) {
            $user->tenantProfile?->delete();
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', "Akun penyewa {$userName} berhasil dihapus permanen.");
    }
}

