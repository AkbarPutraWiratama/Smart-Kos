<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('pages.auth.login', ['title' => 'Login — Smart Kos']);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $remember = $request->boolean('remember');

        // Check if user exists and check active status
        $user = User::where('username', $credentials['username'])->first();

        if ($user && $user->status !== 'active') {
            throw ValidationException::withMessages([
                'username' => __('Akun Anda tidak aktif. Silakan hubungi admin.'),
            ]);
        }

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'username' => __('Username atau password salah.'),
            ]);
        }

        $request->session()->regenerate();

        /** @var User $authUser */
        $authUser = Auth::user();
        $authUser->update([
            'last_login_at' => now(),
        ]);

        return match ($authUser->role) {
            'admin' => redirect()->intended(route('admin.dashboard', absolute: false)),
            'staff' => redirect()->intended(route('staff.dashboard', absolute: false)),
            'penyewa' => redirect()->intended(route('penyewa.dashboard', absolute: false)),
            default => redirect()->intended('/'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
