<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EncryptionService;

class EncryptSensitiveData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'encrypt:sensitive-data 
                            {--email= : Email a encriptar}
                            {--password= : Clave de aplicación a encriptar}
                            {--decrypt= : Valor a desencriptar}
                            {--check= : Verificar si un valor está encriptado}';

    /**
     * The console command description.
     */
    protected $description = 'Encriptar/desencriptar datos sensibles como emails y claves de aplicación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Herramienta de Encriptación de Datos Sensibles');
        $this->line('');

        // Encriptar email
        if ($email = $this->option('email')) {
            $this->encryptEmail($email);
            return;
        }

        // Encriptar clave
        if ($password = $this->option('password')) {
            $this->encryptPassword($password);
            return;
        }

        // Desencriptar valor
        if ($encrypted = $this->option('decrypt')) {
            $this->decryptValue($encrypted);
            return;
        }

        // Verificar si está encriptado
        if ($value = $this->option('check')) {
            $this->checkEncryption($value);
            return;
        }

        // Modo interactivo
        $this->interactiveMode();
    }

    /**
     * Modo interactivo
     */
    private function interactiveMode()
    {
        $this->info('Selecciona una opción:');
        
        $choice = $this->choice('¿Qué deseas hacer?', [
            'encrypt_email' => 'Encriptar email',
            'encrypt_password' => 'Encriptar clave de aplicación',
            'decrypt' => 'Desencriptar valor',
            'encrypt_current' => 'Encriptar datos actuales del .env',
            'show_current' => 'Mostrar configuración actual',
        ], 'encrypt_current');

        switch ($choice) {
            case 'encrypt_email':
                $email = $this->ask('Ingresa el email a encriptar');
                $this->encryptEmail($email);
                break;

            case 'encrypt_password':
                $password = $this->secret('Ingresa la clave de aplicación a encriptar');
                $this->encryptPassword($password);
                break;

            case 'decrypt':
                $encrypted = $this->ask('Ingresa el valor encriptado');
                $this->decryptValue($encrypted);
                break;

            case 'encrypt_current':
                $this->encryptCurrentEnvData();
                break;

            case 'show_current':
                $this->showCurrentConfiguration();
                break;
        }
    }

    /**
     * Encriptar email
     */
    private function encryptEmail(string $email)
    {
        try {
            $encrypted = EncryptionService::encryptEmail($email);
            
            $this->info("✅ Email encriptado exitosamente:");
            $this->line("Original: {$email}");
            $this->line("Encriptado: {$encrypted}");
            $this->line('');
            $this->comment('Copia el valor encriptado y úsalo en tu archivo .env');
            
        } catch (\Exception $e) {
            $this->error("❌ Error encriptando email: " . $e->getMessage());
        }
    }

    /**
     * Encriptar clave de aplicación
     */
    private function encryptPassword(string $password)
    {
        try {
            $encrypted = EncryptionService::encryptAppPassword($password);
            
            $this->info("✅ Clave encriptada exitosamente:");
            $this->line("Original: " . str_repeat('*', strlen($password)));
            $this->line("Encriptada: {$encrypted}");
            $this->line('');
            $this->comment('Copia el valor encriptado y úsalo en tu archivo .env');
            
        } catch (\Exception $e) {
            $this->error("❌ Error encriptando clave: " . $e->getMessage());
        }
    }

    /**
     * Desencriptar valor
     */
    private function decryptValue(string $encrypted)
    {
        try {
            $decrypted = EncryptionService::decrypt($encrypted);
            
            $this->info("✅ Valor desencriptado exitosamente:");
            $this->line("Encriptado: {$encrypted}");
            $this->line("Desencriptado: {$decrypted}");
            
        } catch (\Exception $e) {
            $this->error("❌ Error desencriptando valor: " . $e->getMessage());
        }
    }

    /**
     * Verificar si un valor está encriptado
     */
    private function checkEncryption(string $value)
    {
        $isEncrypted = EncryptionService::isEncrypted($value);
        
        if ($isEncrypted) {
            $this->info("✅ El valor ESTÁ encriptado");
            try {
                $decrypted = EncryptionService::decrypt($value);
                $this->line("Valor desencriptado: {$decrypted}");
            } catch (\Exception $e) {
                $this->error("Error desencriptando: " . $e->getMessage());
            }
        } else {
            $this->warn("⚠️  El valor NO está encriptado");
            $this->line("Valor actual: {$value}");
        }
    }

    /**
     * Encriptar datos actuales del .env
     */
    private function encryptCurrentEnvData()
    {
        $this->info('🔍 Analizando configuración actual...');
        $this->line('');

        // Datos a encriptar
        $dataToEncrypt = [
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            'LICENSE_ADMIN_EMAIL_1' => env('LICENSE_ADMIN_EMAIL_1'),
            'LICENSE_ADMIN_EMAIL_2' => env('LICENSE_ADMIN_EMAIL_2'),
        ];

        $this->table(['Variable', 'Estado Actual', 'Valor Encriptado'], $this->analyzeCurrentData($dataToEncrypt));

        if ($this->confirm('¿Deseas generar los valores encriptados para tu .env?')) {
            $this->generateEncryptedEnvValues($dataToEncrypt);
        }
    }

    /**
     * Analizar datos actuales
     */
    private function analyzeCurrentData(array $data): array
    {
        $results = [];
        
        foreach ($data as $key => $value) {
            if (empty($value)) {
                $results[] = [$key, '❌ No configurado', 'N/A'];
                continue;
            }

            $isEncrypted = EncryptionService::isEncrypted($value);
            $status = $isEncrypted ? '✅ Ya encriptado' : '⚠️  Sin encriptar';
            
            if ($isEncrypted) {
                $results[] = [$key, $status, 'Ya está encriptado'];
            } else {
                try {
                    if (strpos($key, 'EMAIL') !== false) {
                        $encrypted = EncryptionService::encryptEmail($value);
                    } else {
                        $encrypted = EncryptionService::encryptAppPassword($value);
                    }
                    $results[] = [$key, $status, substr($encrypted, 0, 50) . '...'];
                } catch (\Exception $e) {
                    $results[] = [$key, '❌ Error', $e->getMessage()];
                }
            }
        }

        return $results;
    }

    /**
     * Generar valores encriptados para .env
     */
    private function generateEncryptedEnvValues(array $data)
    {
        $this->info('📝 Valores encriptados para tu archivo .env:');
        $this->line('');

        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (EncryptionService::isEncrypted($value)) {
                $this->line("{$key}=\"{$value}\"");
                continue;
            }

            try {
                if (strpos($key, 'EMAIL') !== false) {
                    $encrypted = EncryptionService::encryptEmail($value);
                } else {
                    $encrypted = EncryptionService::encryptAppPassword($value);
                }
                
                $this->line("{$key}=\"{$encrypted}\"");
                
            } catch (\Exception $e) {
                $this->error("Error encriptando {$key}: " . $e->getMessage());
            }
        }

        $this->line('');
        $this->comment('⚠️  IMPORTANTE: Guarda estos valores en tu archivo .env y reinicia la aplicación');
    }

    /**
     * Mostrar configuración actual
     */
    private function showCurrentConfiguration()
    {
        $this->info('📋 Configuración actual de datos sensibles:');
        $this->line('');

        $configs = [
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            'LICENSE_ADMIN_EMAIL_1' => env('LICENSE_ADMIN_EMAIL_1'),
            'LICENSE_ADMIN_EMAIL_2' => env('LICENSE_ADMIN_EMAIL_2'),
        ];

        foreach ($configs as $key => $value) {
            if (empty($value)) {
                $this->line("{$key}: ❌ No configurado");
                continue;
            }

            $isEncrypted = EncryptionService::isEncrypted($value);
            
            if ($isEncrypted) {
                $this->line("{$key}: ✅ Encriptado");
                try {
                    $decrypted = EncryptionService::decrypt($value);
                    if (strpos($key, 'EMAIL') !== false) {
                        $this->line("  └─ Valor: {$decrypted}");
                    } else {
                        $this->line("  └─ Valor: " . str_repeat('*', strlen($decrypted)));
                    }
                } catch (\Exception $e) {
                    $this->line("  └─ Error desencriptando: " . $e->getMessage());
                }
            } else {
                $this->line("{$key}: ⚠️  Sin encriptar");
                if (strpos($key, 'EMAIL') !== false) {
                    $this->line("  └─ Valor: {$value}");
                } else {
                    $this->line("  └─ Valor: " . str_repeat('*', strlen($value)));
                }
            }
        }
    }
}
