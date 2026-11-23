<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPasswordUpdateRequest;
use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the administrator account settings page.
     */
    public function index(): View
    {
        $user = Auth::user();

        return view('admin.account', compact('user'));
    }

    /**
     * Update administrator profile and contact details.
     */
    public function updateProfile(AdminProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('admin.account')
            ->with('success', 'Profil dan informasi kontak Administrator berhasil diperbarui.');
    }

    /**
     * Update administrator account password.
     */
    public function updatePassword(AdminPasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('admin.account')
            ->with('success', 'Kata sandi Administrator berhasil diperbarui.');
    }
}
