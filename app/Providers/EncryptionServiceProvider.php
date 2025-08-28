<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use App\Services\EncryptionService;

class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->decryptSensitiveConfiguration();
    }

    /**
     * Desencriptar configuración sensible al arrancar la aplicación
     */
    private function decryptSensitiveConfiguration(): void
    {
        try {
            // Desencriptar clave de email
            $encryptedPassword = env('MAIL_PASSWORD');
            if ($encryptedPassword && EncryptionService::isEncrypted($encryptedPassword)) {
                $decryptedPassword = EncryptionService::decryptAppPassword($encryptedPassword);
                Config::set('mail.mailers.smtp.password', $decryptedPassword);
            }

            // Desencriptar emails de administradores de licencia
            $this->decryptLicenseEmails();

        } catch (\Exception $e) {
            // Log del error pero no fallar la aplicación
            \Log::warning('Error desencriptando configuración sensible', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Desencriptar emails de administradores de licencia
     */
    private function decryptLicenseEmails(): void
    {
        $emailKeys = [
            'LICENSE_ADMIN_EMAIL_1',
            'LICENSE_ADMIN_EMAIL_2',
        ];

        $decryptedEmails = [];

        foreach ($emailKeys as $key) {
            $encryptedEmail = env($key);
            
            if ($encryptedEmail && EncryptionService::isEncrypted($encryptedEmail)) {
                try {
                    $decryptedEmail = EncryptionService::decryptEmail($encryptedEmail);
                    $decryptedEmails[] = $decryptedEmail;
                } catch (\Exception $e) {
                    \Log::warning("Error desencriptando {$key}", [
                        'error' => $e->getMessage()
                    ]);
                }
            } elseif ($encryptedEmail) {
                // Si no está encriptado, usar tal como está
                $decryptedEmails[] = $encryptedEmail;
            }
        }

        // Actualizar configuración de licencia con emails desencriptados
        if (!empty($decryptedEmails)) {
            Config::set('license.administrators', $decryptedEmails);
        }
    }
}
