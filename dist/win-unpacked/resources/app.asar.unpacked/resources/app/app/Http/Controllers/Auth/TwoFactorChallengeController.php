<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Show the two factor challenge page.
     */
    public function create()
    {
        if (!session('login.id')) {
            return redirect()->route('login');
        }

        return Inertia::render('auth/two-factor-challenge');
    }

    /**
     * Verify the two factor authentication code.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required_without:recovery_code|string',
            'recovery_code' => 'required_without:code|string',
        ]);

        $userId = session('login.id');
        $remember = session('login.remember', false);

        if (!$userId) {
            throw ValidationException::withMessages([
                'code' => ['Su sesión ha expirado. Por favor, inicie sesión nuevamente.'],
            ]);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'code' => ['Usuario no encontrado.'],
            ]);
        }

        $verified = false;

        if ($request->filled('code')) {
            $verified = $this->twoFactorService->verifyLoginCode($user, $request->code);
        } elseif ($request->filled('recovery_code')) {
            $verified = $this->twoFactorService->verifyRecoveryCode($user, $request->recovery_code);
        }

        if ($verified) {
            // Clear the login session data
            session()->forget(['login.id', 'login.remember']);
            
            // Log the user in
            Auth::login($user, $remember);
            
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'code' => ['El código proporcionado no es válido.'],
        ]);
    }
}
