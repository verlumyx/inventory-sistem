<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class License extends Model
{
    protected $fillable = [
        'license_code',
        'start_date',
        'end_date',
        'status',
        'machine_id',
        'user_email',
        'activated_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Relación con logs de licencia
     */
    public function logs(): HasMany
    {
        return $this->hasMany(LicenseLog::class);
    }

    /**
     * Verificar si la licencia está activa
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               $this->start_date <= now() &&
               $this->end_date >= now();
    }

    /**
     * Verificar si la licencia ha expirado
     */
    public function isExpired(): bool
    {
        return $this->end_date < now();
    }

    /**
     * Obtener días hasta la expiración
     */
    public function daysUntilExpiration(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->end_date);
    }

    /**
     * Generar código de licencia único
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (self::where('license_code', $code)->exists());

        return $code;
    }

    /**
     * Crear nueva solicitud de licencia
     */
    public static function createRequest(string $machineId, string $userEmail = null): self
    {
        return self::create([
            'license_code' => self::generateCode(),
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'status' => 'pending',
            'machine_id' => $machineId,
            'user_email' => $userEmail,
        ]);
    }

    /**
     * Activar licencia con código
     */
    public function activate(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'active',
            'activated_at' => now(),
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
        ]);

        return true;
    }

    /**
     * Obtener la licencia activa actual
     */
    public static function getCurrentLicense(): ?self
    {
        return self::where('status', 'active')
                   ->where('start_date', '<=', now())
                   ->where('end_date', '>=', now())
                   ->orderBy('end_date', 'desc')
                   ->first();
    }

    /**
     * Verificar si necesita notificación de vencimiento
     */
    public function needsExpirationNotification(): bool
    {
        $daysUntilExpiration = $this->daysUntilExpiration();
        return in_array($daysUntilExpiration, [30, 15, 7, 3, 1]);
    }

    /**
     * Encriptar código para envío por email
     */
    public function getEncryptedCodeAttribute(): string
    {
        return Crypt::encryptString($this->license_code);
    }

    /**
     * Generar ID único de máquina
     */
    public static function generateMachineId(): string
    {
        // Combinar información del sistema para crear un ID único
        $systemInfo = [
            php_uname('n'), // hostname
            php_uname('s'), // OS
            php_uname('r'), // release
            $_SERVER['SERVER_NAME'] ?? 'unknown',
            gethostname() ?: 'unknown',
        ];

        return hash('sha256', implode('|', $systemInfo));
    }
}
