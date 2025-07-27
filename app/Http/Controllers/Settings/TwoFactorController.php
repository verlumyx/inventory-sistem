<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Show the two factor authentication settings page.
     */
    public function edit(): Response
    {
        $user = Auth::user();

        $data = [
            'two_factor_enabled' => $user->hasTwoFactorEnabled(),
            'two_factor_confirmed' => $user->hasTwoFactorConfirmed(),
        ];

        // Si el usuario no tiene 2FA habilitado, generar setup automáticamente
        if (!$user->hasTwoFactorEnabled()) {
            try {
                $setup = $this->twoFactorService->setupTwoFactor($user);
                $data['two_factor_setup'] = $setup;
                \Log::info('Auto-generated 2FA setup for user', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                \Log::error('Error auto-generating 2FA setup: ' . $e->getMessage());
            }
        }

        return Inertia::render('settings/two-factor', $data);
    }

    /**
     * Enable two factor authentication.
     */
    public function store(Request $request): RedirectResponse
    {
        \Log::info('TwoFactorController@store called');

        $user = $request->user();
        \Log::info('User ID: ' . $user->id);

        if ($user->hasTwoFactorEnabled()) {
            \Log::info('2FA already enabled for user');
            return back()->withErrors(['two_factor' => 'La autenticación de dos factores ya está habilitada.']);
        }

        try {
            $setup = $this->twoFactorService->setupTwoFactor($user);
            \Log::info('2FA setup successful', $setup);

            return back()->with([
                'two_factor_setup' => $setup,
                'status' => 'two-factor-setup',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error setting up 2FA: ' . $e->getMessage());
            return back()->withErrors(['two_factor' => 'Error al configurar la autenticación de dos factores.']);
        }
    }

    /**
     * Confirm two factor authentication setup.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        \Log::info('Attempting to confirm 2FA', [
            'user_id' => $user->id,
            'code' => $request->code,
            'has_secret' => !empty($user->two_factor_secret),
        ]);

        if ($this->twoFactorService->confirmTwoFactor($user, $request->code)) {
            \Log::info('2FA confirmation successful');
            return back()->with([
                'status' => 'two-factor-confirmed',
                'recovery_codes' => $user->two_factor_recovery_codes,
            ]);
        }

        \Log::warning('2FA confirmation failed', [
            'user_id' => $user->id,
            'code' => $request->code,
        ]);

        throw ValidationException::withMessages([
            'code' => ['El código proporcionado no es válido.'],
        ]);
    }

    /**
     * Disable two factor authentication.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        
        $user = $request->user();
        $user->disableTwoFactor();
        
        return back()->with('status', 'two-factor-disabled');
    }

    /**
     * Generate new recovery codes.
     */
    public function recoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        
        $user = $request->user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return back()->withErrors(['two_factor' => 'La autenticación de dos factores no está habilitada.']);
        }
        
        $codes = $user->generateRecoveryCodes();
        
        return back()->with([
            'status' => 'recovery-codes-generated',
            'recovery_codes' => $codes,
        ]);
    }
}
