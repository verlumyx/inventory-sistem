<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class LicenseLog extends Model
{
    protected $fillable = [
        'license_id',
        'action',
        'license_code',
        'machine_id',
        'user_email',
        'ip_address',
        'user_agent',
        'metadata',
        'level',
        'message',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relación con License
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Crear log de actividad de licencia
     */
    public static function logActivity(
        string $action,
        ?License $license = null,
        string $level = 'info',
        string $message = null,
        array $metadata = [],
        ?Request $request = null
    ): self {
        return self::create([
            'license_id' => $license?->id,
            'action' => $action,
            'license_code' => $license?->license_code,
            'machine_id' => $license?->machine_id ?? License::generateMachineId(),
            'user_email' => $request?->user()?->email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
            'level' => $level,
            'message' => $message,
        ]);
    }

    /**
     * Log de generación de código
     */
    public static function logCodeGenerated(License $license, ?Request $request = null): self
    {
        return self::logActivity(
            'generated',
            $license,
            'info',
            "Código de licencia generado: {$license->license_code}",
            [
                'duration_months' => 6,
                'expires_at' => $license->end_date->toISOString(),
            ],
            $request
        );
    }

    /**
     * Log de activación de licencia
     */
    public static function logLicenseActivated(License $license, ?Request $request = null): self
    {
        return self::logActivity(
            'activated',
            $license,
            'info',
            "Licencia activada exitosamente: {$license->license_code}",
            [
                'activated_at' => $license->activated_at->toISOString(),
                'expires_at' => $license->end_date->toISOString(),
            ],
            $request
        );
    }

    /**
     * Log de intento de activación fallido
     */
    public static function logActivationFailed(
        string $licenseCode,
        string $reason,
        ?Request $request = null
    ): self {
        return self::logActivity(
            'activation_failed',
            null,
            'warning',
            "Intento de activación fallido para código: {$licenseCode}. Razón: {$reason}",
            [
                'reason' => $reason,
                'attempted_code' => $licenseCode,
            ],
            $request
        );
    }

    /**
     * Log de acceso con licencia válida
     */
    public static function logValidAccess(License $license, ?Request $request = null): self
    {
        return self::logActivity(
            'valid_access',
            $license,
            'info',
            "Acceso autorizado con licencia válida",
            [
                'days_remaining' => $license->daysUntilExpiration(),
                'route' => $request?->route()?->getName(),
            ],
            $request
        );
    }

    /**
     * Log de acceso denegado por licencia expirada
     */
    public static function logAccessDenied(string $reason, ?Request $request = null): self
    {
        return self::logActivity(
            'access_denied',
            null,
            'error',
            "Acceso denegado: {$reason}",
            [
                'reason' => $reason,
                'attempted_route' => $request?->route()?->getName(),
            ],
            $request
        );
    }

    /**
     * Log de expiración de licencia
     */
    public static function logLicenseExpired(License $license): self
    {
        return self::logActivity(
            'expired',
            $license,
            'error',
            "Licencia expirada: {$license->license_code}",
            [
                'expired_at' => $license->end_date->toISOString(),
                'was_active_for_days' => $license->start_date->diffInDays($license->end_date),
            ]
        );
    }

    /**
     * Obtener logs recientes
     */
    public static function getRecentLogs(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('license')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Obtener logs por acción
     */
    public static function getLogsByAction(string $action, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return self::with('license')
                   ->where('action', $action)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }
}
