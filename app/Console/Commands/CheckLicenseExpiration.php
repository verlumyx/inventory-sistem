<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckLicenseExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for licenses that are about to expire and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando licencias próximas a vencer...');

        // Obtener la licencia actual
        $currentLicense = License::getCurrentLicense();

        if (!$currentLicense) {
            $this->warn('No se encontró una licencia activa en el sistema.');
            Log::warning('Sistema sin licencia activa detectado por comando de verificación');
            return 1;
        }

        $daysUntilExpiration = $currentLicense->daysUntilExpiration();
        $warningDays = config('license.warning_days', [30, 15, 7, 3, 1]);

        $this->info("Licencia actual: {$currentLicense->license_code}");
        $this->info("Días hasta expiración: {$daysUntilExpiration}");

        // Verificar si necesita notificación
        if (in_array($daysUntilExpiration, $warningDays)) {
            $this->warn("⚠️  La licencia expirará en {$daysUntilExpiration} días!");

            // Enviar notificación a administradores
            $this->sendExpirationNotification($currentLicense, $daysUntilExpiration);

            Log::warning('Notificación de expiración de licencia enviada', [
                'license_code' => $currentLicense->license_code,
                'days_remaining' => $daysUntilExpiration,
                'expires_at' => $currentLicense->end_date
            ]);

            $this->info('✅ Notificación enviada a los administradores.');
        } else {
            $this->info('✅ La licencia no requiere notificación en este momento.');
        }

        // Verificar licencias expiradas
        $expiredLicenses = License::where('status', 'active')
                                 ->where('end_date', '<', now())
                                 ->get();

        if ($expiredLicenses->count() > 0) {
            $this->error('🚨 Se encontraron licencias expiradas:');

            foreach ($expiredLicenses as $expired) {
                $this->error("  - {$expired->license_code} (expiró el {$expired->end_date->format('d/m/Y')})");

                // Marcar como expirada
                $expired->update(['status' => 'expired']);

                Log::error('Licencia marcada como expirada', [
                    'license_code' => $expired->license_code,
                    'expired_at' => $expired->end_date
                ]);
            }
        }

        return 0;
    }

    /**
     * Enviar notificación de expiración a administradores
     */
    protected function sendExpirationNotification(License $license, int $daysRemaining)
    {
        $administrators = config('license.administrators', ['admin@sistema.com']);
        $company = Company::getCompany();

        foreach ($administrators as $adminEmail) {
            try {
                Mail::send('emails.license-expiration', [
                    'license' => $license,
                    'daysRemaining' => $daysRemaining,
                    'company' => $company,
                ], function ($message) use ($adminEmail, $daysRemaining, $company) {
                    $companyName = $company ? $company->name_company : 'Sistema de Inventario';
                    $message->to($adminEmail)
                           ->subject("⚠️ Licencia expirará en {$daysRemaining} días - {$companyName}");
                });

                $this->info("  ✉️  Notificación enviada a: {$adminEmail}");

            } catch (\Exception $e) {
                $this->error("  ❌ Error enviando a {$adminEmail}: {$e->getMessage()}");

                Log::error('Error enviando notificación de expiración', [
                    'admin_email' => $adminEmail,
                    'license_code' => $license->license_code,
                    'company_name' => $company?->name_company,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
