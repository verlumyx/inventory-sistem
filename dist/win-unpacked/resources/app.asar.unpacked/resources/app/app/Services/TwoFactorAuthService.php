<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for two factor authentication.
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code URL for Google Authenticator.
     */
    public function generateQrCodeUrl(User $user, string $secret): string
    {
        $appName = config('app.name', 'Laravel App');
        $email = $user->email;

        return $this->google2fa->getQRCodeUrl(
            $appName,
            $email,
            $secret
        );
    }

    /**
     * Verify a TOTP code.
     */
    public function verifyCode(string $secret, string $code): bool
    {
        \Log::info('Verifying TOTP code with Google2FA', [
            'code' => $code,
            'current_time' => time(),
        ]);

        try {
            $isValid = $this->google2fa->verifyKey($secret, $code, 2); // 2 = window tolerance

            \Log::info('Google2FA verification result', [
                'is_valid' => $isValid,
                'code' => $code,
            ]);

            return $isValid;
        } catch (\Exception $e) {
            \Log::error('Error verifying TOTP code', [
                'error' => $e->getMessage(),
                'code' => $code,
            ]);
            return false;
        }
    }



    /**
     * Setup two factor authentication for a user.
     */
    public function setupTwoFactor(User $user): array
    {
        $secret = $this->generateSecretKey();
        $qrCodeUrl = $this->generateQrCodeUrl($user, $secret);
        
        // Store encrypted secret temporarily (not confirmed yet)
        $user->two_factor_secret = Crypt::encryptString($secret);
        $user->save();
        
        return [
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ];
    }

    /**
     * Confirm two factor authentication setup.
     */
    public function confirmTwoFactor(User $user, string $code): bool
    {
        \Log::info('confirmTwoFactor called', [
            'user_id' => $user->id,
            'code' => $code,
            'has_secret' => !empty($user->two_factor_secret),
        ]);

        if (!$user->two_factor_secret) {
            \Log::warning('No 2FA secret found for user');
            return false;
        }

        try {
            $secret = Crypt::decryptString($user->two_factor_secret);
            \Log::info('Decrypted secret successfully', ['secret_length' => strlen($secret)]);

            $isValid = $this->verifyCode($secret, $code);
            \Log::info('Code verification result', ['is_valid' => $isValid]);

            if ($isValid) {
                $user->enableTwoFactor();
                $user->generateRecoveryCodes();
                \Log::info('2FA enabled successfully for user');
                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Error in confirmTwoFactor', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verify two factor code for login.
     * Note: Login 2FA verification is currently disabled.
     */
    public function verifyLoginCode(User $user, string $code): bool
    {
        // Login 2FA verification disabled
        return true;

        // Original code (commented out):
        // if (!$user->hasTwoFactorEnabled()) {
        //     return false;
        // }
        // $secret = Crypt::decryptString($user->two_factor_secret);
        // return $this->verifyCode($secret, $code);
    }

    /**
     * Verify recovery code for login.
     * Note: Login 2FA verification is currently disabled.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        // Login 2FA verification disabled
        return true;

        // Original code (commented out):
        // if (!$user->hasTwoFactorEnabled()) {
        //     return false;
        // }
        // return $user->useRecoveryCode($code);
    }
}
