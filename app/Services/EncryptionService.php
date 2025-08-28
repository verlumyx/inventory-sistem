<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptionService
{
    /**
     * Encriptar un valor sensible
     */
    public static function encrypt(string $value): string
    {
        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            Log::error('Error encriptando valor', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Desencriptar un valor
     */
    public static function decrypt(string $encryptedValue): string
    {
        try {
            return Crypt::decryptString($encryptedValue);
        } catch (DecryptException $e) {
            Log::error('Error desencriptando valor', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verificar si un valor está encriptado
     */
    public static function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException $e) {
            return false;
        }
    }

    /**
     * Encriptar email si no está encriptado
     */
    public static function encryptEmail(string $email): string
    {
        // Si ya está encriptado, devolverlo tal como está
        if (self::isEncrypted($email)) {
            return $email;
        }

        // Validar que sea un email válido antes de encriptar
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido: ' . $email);
        }

        return self::encrypt($email);
    }

    /**
     * Desencriptar email
     */
    public static function decryptEmail(string $encryptedEmail): string
    {
        $email = self::decrypt($encryptedEmail);
        
        // Validar que el email desencriptado sea válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email desencriptado inválido');
        }

        return $email;
    }

    /**
     * Encriptar clave de aplicación
     */
    public static function encryptAppPassword(string $password): string
    {
        if (self::isEncrypted($password)) {
            return $password;
        }

        // Validar que la clave tenga el formato esperado de Gmail
        if (strlen($password) < 10) {
            throw new \InvalidArgumentException('Clave de aplicación muy corta');
        }

        return self::encrypt($password);
    }

    /**
     * Desencriptar clave de aplicación
     */
    public static function decryptAppPassword(string $encryptedPassword): string
    {
        return self::decrypt($encryptedPassword);
    }

    /**
     * Obtener email desencriptado desde configuración
     */
    public static function getDecryptedEmail(string $configKey): ?string
    {
        $encryptedEmail = config($configKey);
        
        if (empty($encryptedEmail)) {
            return null;
        }

        try {
            return self::decryptEmail($encryptedEmail);
        } catch (\Exception $e) {
            Log::warning('No se pudo desencriptar email', [
                'config_key' => $configKey,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtener clave desencriptada desde configuración
     */
    public static function getDecryptedPassword(string $configKey): ?string
    {
        $encryptedPassword = config($configKey);
        
        if (empty($encryptedPassword)) {
            return null;
        }

        try {
            return self::decryptAppPassword($encryptedPassword);
        } catch (\Exception $e) {
            Log::warning('No se pudo desencriptar clave', [
                'config_key' => $configKey,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
