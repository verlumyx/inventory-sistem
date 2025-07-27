<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetTwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Show the 2FA verification page for password reset.
     */
    public function create(): Response
    {
        if (!session('password_reset.email')) {
            return redirect()->route('password.request');
        }

        return Inertia::render('auth/password-reset-two-factor', [
            'email' => session('password_reset.email'),
        ]);
    }

    /**
     * Verify 2FA code and show password reset form.
     */
    public function verify(Request $request): RedirectResponse|Response
    {
        $request->validate([
            'code' => 'nullable|string|size:6',
            'recovery_code' => 'nullable|string|min:8|max:8',
        ]);

        // Verificar que al menos uno de los campos esté presente
        if (!$request->filled('code') && !$request->filled('recovery_code')) {
            throw ValidationException::withMessages([
                'code' => ['Debes proporcionar un código de autenticación o un código de recuperación.'],
            ]);
        }

        $email = session('password_reset.email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();
        if (!$user || !$user->hasTwoFactorEnabled()) {
            \Log::warning('Password reset 2FA: User not found or 2FA not enabled', [
                'email' => $email,
                'user_exists' => !!$user,
                'has_2fa' => $user ? $user->hasTwoFactorEnabled() : false,
            ]);
            return redirect()->route('password.request');
        }

        \Log::info('Password reset 2FA verification attempt', [
            'user_id' => $user->id,
            'email' => $email,
            'has_code' => $request->filled('code'),
            'has_recovery_code' => $request->filled('recovery_code'),
            'code_value' => $request->code,
            'recovery_code_value' => $request->recovery_code,
        ]);

        $verified = false;

        if ($request->filled('code')) {
            $verified = $this->twoFactorService->verifyCode(
                \Illuminate\Support\Facades\Crypt::decryptString($user->two_factor_secret),
                $request->code
            );
            \Log::info('Code verification result', ['verified' => $verified]);
        } elseif ($request->filled('recovery_code')) {
            $verified = $user->useRecoveryCode($request->recovery_code);
            \Log::info('Recovery code verification result', ['verified' => $verified]);
        }

        if (!$verified) {
            \Log::warning('Password reset 2FA verification failed');
            throw ValidationException::withMessages([
                'code' => ['El código proporcionado no es válido.'],
                'recovery_code' => ['El código de recuperación no es válido.'],
            ]);
        }

        // 2FA verificado exitosamente, mostrar formulario de nueva contraseña
        $request->session()->put('password_reset.verified', true);
        
        return Inertia::render('auth/reset-password-form', [
            'email' => $email,
        ]);
    }

    /**
     * Reset the password after 2FA verification.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = session('password_reset.email');
        $verified = session('password_reset.verified');

        if (!$email || !$verified) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request');
        }

        // Actualizar la contraseña
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Limpiar la sesión
        $request->session()->forget(['password_reset.email', 'password_reset.verified']);

        // Log del cambio de contraseña
        \Log::info('Password reset via 2FA', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido actualizada exitosamente.');
    }
}
