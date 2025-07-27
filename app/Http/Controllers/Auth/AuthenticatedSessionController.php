<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        // Attempt to authenticate without logging in
        if (! Auth::validate($request->only('email', 'password'))) {
            \Illuminate\Support\Facades\RateLimiter::hit($request->throttleKey());

            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Get the user
        $user = \App\Models\User::where('email', $request->email)->first();

        // Check if user has 2FA enabled
        if ($user && $user->hasTwoFactorEnabled()) {
            // Store login attempt in session for 2FA challenge
            $request->session()->put([
                'login.id' => $user->id,
                'login.remember' => $request->boolean('remember'),
            ]);

            \Illuminate\Support\Facades\RateLimiter::clear($request->throttleKey());

            return redirect()->route('two-factor.login');
        }

        // Normal login without 2FA
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        \Illuminate\Support\Facades\RateLimiter::clear($request->throttleKey());

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
