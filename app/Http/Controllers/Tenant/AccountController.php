<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenantPasswordUpdateRequest;
use App\Http\Requests\Tenant\TenantProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the tenant account and profile management page.
     */
    public function index(): View
    {
        $user = Auth::user();
        $tenantProfile = $user->tenantProfile ?? $user->tenantProfile()->firstOrCreate(['user_id' => $user->id]);

        $activeAssignment = $tenantProfile->roomAssignments()
            ->where('status', 'active')
            ->with(['room.location'])
            ->latest('start_date')
            ->first();

        return view('penyewa.account', compact('user', 'tenantProfile', 'activeAssignment'));
    }

    /**
     * Update the tenant's personal profile information.
     */
    public function updateProfile(TenantProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        $user->tenantProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone'   => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]
        );

        return redirect()->route('penyewa.account')
            ->with('success', 'Informasi profil dan kontak Anda berhasil diperbarui.');
    }

    /**
     * Update the tenant's account password.
     */
    public function updatePassword(TenantPasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('penyewa.account')
            ->with('success', 'Kata sandi akun Anda berhasil diperbarui.');
    }
}

