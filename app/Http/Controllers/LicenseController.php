<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\License;
use App\Models\LicenseLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class LicenseController extends Controller
{
    /**
     * Mostrar página de renovación de licencia
     */
    public function renewal()
    {
        $currentLicense = License::getCurrentLicense();
        $lastLicense = License::orderBy('created_at', 'desc')->first();
        $machineId = License::generateMachineId();

        return Inertia::render('License/Renewal', [
            'currentLicense' => $currentLicense,
            'lastLicense' => $lastLicense,
            'machineId' => $machineId,
            'hasActiveLicense' => $currentLicense !== null,
        ]);
    }

    /**
     * Generar nuevo código de licencia
     */
    public function generateCode(Request $request)
    {
        try {
            $machineId = License::generateMachineId();
            $userEmail = $request->user()?->email;

            // Crear nueva solicitud de licencia
            $license = License::createRequest($machineId, $userEmail);

            // Enviar email a administradores
            $this->sendCodeToAdministrators($license);

            // Log de la actividad
            LicenseLog::logCodeGenerated($license, $request);

            return back()->with('success',
                'Código de renovación generado exitosamente. ' .
                'Se ha enviado a los administradores del sistema. ' .
                'Código de referencia: ' . $license->license_code
            );

        } catch (\Exception $e) {
            Log::error('Error generando código de licencia', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error',
                'Error al generar el código de renovación. Por favor, inténtelo nuevamente.'
            );
        }
    }

    /**
     * Activar licencia con código
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_code' => 'required|string|size:10',
        ]);

        $licenseCode = strtoupper($request->license_code);

        // Buscar la licencia
        $license = License::where('license_code', $licenseCode)
                         ->where('status', 'pending')
                         ->first();

        if (!$license) {
            LicenseLog::logActivationFailed(
                $licenseCode,
                'Código inválido o ya utilizado',
                $request
            );

            return back()->with('error',
                'Código de licencia inválido o ya utilizado.'
            );
        }

        // Verificar que el machine_id coincida
        $currentMachineId = License::generateMachineId();
        if ($license->machine_id !== $currentMachineId) {
            LicenseLog::logActivationFailed(
                $licenseCode,
                'Machine ID no coincide',
                $request
            );

            return back()->with('error',
                'Este código de licencia no es válido para esta máquina.'
            );
        }

        // Activar la licencia
        if ($license->activate()) {
            LicenseLog::logLicenseActivated($license, $request);

            return redirect()->route('dashboard')->with('success',
                'Licencia activada exitosamente. El sistema está ahora habilitado por 6 meses.'
            );
        }

        return back()->with('error',
            'Error al activar la licencia. Por favor, contacte al administrador.'
        );
    }

    /**
     * Enviar código a administradores
     */
    protected function sendCodeToAdministrators(License $license)
    {
        Log::info('Iniciando envío de código a administradores', [
            'license_code' => $license->license_code
        ]);

        $administrators = config('license.administrators', ['admin@sistema.com']);
        $company = Company::getCompany();

        Log::info('Configuración obtenida', [
            'administrators' => $administrators,
            'company_name' => $company?->name_company
        ]);

        foreach ($administrators as $adminEmail) {
            try {
                Log::info('Intentando enviar email', [
                    'to' => $adminEmail,
                    'license_code' => $license->license_code
                ]);

                $result = Mail::send('emails.license-code', [
                    'license' => $license,
                    'encryptedCode' => $license->encrypted_code,
                    'company' => $company,
                ], function ($message) use ($adminEmail, $license, $company) {
                    $companyName = $company ? $company->name_company : 'Sistema de Inventario';
                    $message->to($adminEmail)
                           ->subject("Nuevo código de licencia - {$companyName} - {$license->license_code}");
                });

                Log::info('Email enviado exitosamente', [
                    'admin_email' => $adminEmail,
                    'company_name' => $company?->name_company,
                    'result' => $result ? 'success' : 'failed'
                ]);

            } catch (\Exception $e) {
                Log::error('Error enviando email a administrador', [
                    'admin_email' => $adminEmail,
                    'license_code' => $license->license_code,
                    'company_name' => $company?->name_company,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Re-lanzar la excepción para que se vea en el navegador
                throw $e;
            }
        }

        Log::info('Envío de códigos completado');
    }
}
