<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StaffPasswordUpdateRequest;
use App\Http\Requests\Staff\StaffProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the staff account settings page.
     */
    public function index(): View
    {
        $user = Auth::user();

        return view('staff.account', compact('user'));
    }

    /**
     * Update staff profile and contact details.
     */
    public function updateProfile(StaffProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('staff.account')
            ->with('success', 'Profil dan informasi kontak Staff berhasil diperbarui.');
    }

    /**
     * Update staff account password.
     */
    public function updatePassword(StaffPasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('staff.account')
            ->with('success', 'Kata sandi akun Staff berhasil diperbarui.');
    }
}
