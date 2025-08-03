<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Buscar el usuario por email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Si el usuario no existe, mostrar mensaje genérico por seguridad
            return back()->with('status', 'Si la cuenta existe, se enviará un enlace de recuperación.');
        }

        // Verificar si el usuario tiene 2FA habilitado
        if ($user->hasTwoFactorEnabled()) {
            // Guardar el email en sesión para la verificación 2FA
            $request->session()->put('password_reset.email', $request->email);

            // Redirigir a la verificación 2FA
            return redirect()->route('password.two-factor');
        }

        // Si no tiene 2FA, usar el método tradicional de correo
        Password::sendResetLink(
            $request->only('email')
        );

        return back()->with('status', 'Se ha enviado un enlace de recuperación a tu correo electrónico.');
    }
}
