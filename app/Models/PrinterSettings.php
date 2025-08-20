<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PrinterSettings extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'printer_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'enabled',
        'type',
        'port',
        'printer_name',
        'timeout',
        'network_host',
        'network_port',
        'network_timeout',
        'baud_rate',
        'data_bits',
        'stop_bits',
        'parity',
        'flow_control',
        'paper_width',
        'paper_margin',
        'line_spacing',
        'retry_enabled',
        'retry_attempts',
        'retry_delay',
        'log_enabled',
        'log_level',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'enabled' => 'boolean',
        'timeout' => 'integer',
        'network_port' => 'integer',
        'network_timeout' => 'integer',
        'baud_rate' => 'integer',
        'data_bits' => 'integer',
        'stop_bits' => 'integer',
        'paper_width' => 'integer',
        'paper_margin' => 'integer',
        'line_spacing' => 'integer',
        'retry_enabled' => 'boolean',
        'retry_attempts' => 'integer',
        'retry_delay' => 'integer',
        'log_enabled' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Asegurar que solo una configuración sea la predeterminada
        static::saving(function ($printerSettings) {
            if ($printerSettings->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $printerSettings->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Scope para configuraciones habilitadas.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope para configuración predeterminada.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Obtener la configuración predeterminada.
     */
    public static function getDefault(): ?self
    {
        return static::default()->first();
    }

    /**
     * Obtener la configuración activa (predeterminada y habilitada).
     */
    public static function getActive(): ?self
    {
        return static::enabled()->default()->first();
    }

    /**
     * Crear configuración predeterminada si no existe.
     */
    public static function createDefaultIfNotExists(): self
    {
        $existing = static::getDefault();

        if ($existing) {
            return $existing;
        }

        return static::create([
            'name' => 'Impresora Principal',
            'enabled' => false,
            'type' => 'cups',
            'port' => '',
            'timeout' => 5,
            'paper_width' => 32,
            'retry_enabled' => true,
            'retry_attempts' => 3,
            'retry_delay' => 1,
            'log_enabled' => true,
            'log_level' => 'info',
            'is_default' => true,
        ]);
    }
    /**
     * Convertir a array de configuración para PrintService.
     */
    public function toPrintConfig(): array
    {
        return [
            'enabled' => $this->enabled,
            'type' => $this->type,
            'port' => $this->port,
            'printer_name' => $this->printer_name,
            'timeout' => $this->timeout,
            'network' => [
                'host' => $this->network_host,
                'port' => $this->network_port,
                'timeout' => $this->network_timeout,
            ],
            'serial' => [
                'baud_rate' => $this->baud_rate,
                'data_bits' => $this->data_bits,
                'stop_bits' => $this->stop_bits,
                'parity' => $this->parity,
                'flow_control' => $this->flow_control,
            ],
            'paper' => [
                'width' => $this->paper_width,
                'margin' => $this->paper_margin,
                'line_spacing' => $this->line_spacing,
            ],
            'retry' => [
                'enabled' => $this->retry_enabled,
                'attempts' => $this->retry_attempts,
                'delay' => $this->retry_delay,
            ],
            'logging' => [
                'enabled' => $this->log_enabled,
                'level' => $this->log_level,
            ],
        ];
    }

    /**
     * Convertir a array para API.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'enabled' => $this->enabled,
            'type' => $this->type,
            'port' => $this->port,
            'printer_name' => $this->printer_name,
            'timeout' => $this->timeout,
            'network_host' => $this->network_host,
            'network_port' => $this->network_port,
            'network_timeout' => $this->network_timeout,
            'baud_rate' => $this->baud_rate,
            'data_bits' => $this->data_bits,
            'stop_bits' => $this->stop_bits,
            'parity' => $this->parity,
            'flow_control' => $this->flow_control,
            'paper_width' => $this->paper_width,
            'paper_margin' => $this->paper_margin,
            'line_spacing' => $this->line_spacing,
            'retry_enabled' => $this->retry_enabled,
            'retry_attempts' => $this->retry_attempts,
            'retry_delay' => $this->retry_delay,
            'log_enabled' => $this->log_enabled,
            'log_level' => $this->log_level,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Obtener tipos de impresora disponibles.
     */
    public static function getAvailableTypes(): array
    {
        return [
            'cups' => 'CUPS (macOS/Linux)',
            'usb' => 'USB Directo (Linux)',
            'serial' => 'Puerto Serial',
            'network' => 'Red (IP)',
        ];
    }

    /**
     * Obtener opciones de paridad.
     */
    public static function getParityOptions(): array
    {
        return [
            'none' => 'Ninguna',
            'odd' => 'Impar',
            'even' => 'Par',
        ];
    }

    /**
     * Obtener opciones de control de flujo.
     */
    public static function getFlowControlOptions(): array
    {
        return [
            'none' => 'Ninguno',
            'rts/cts' => 'RTS/CTS',
            'xon/xoff' => 'XON/XOFF',
        ];
    }

    /**
     * Obtener niveles de log.
     */
    public static function getLogLevels(): array
    {
        return [
            'debug' => 'Debug',
            'info' => 'Info',
            'warning' => 'Warning',
            'error' => 'Error',
        ];
    }

}