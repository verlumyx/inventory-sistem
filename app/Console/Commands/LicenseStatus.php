<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\LicenseLog;
use Illuminate\Console\Command;

class LicenseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:status {--logs=10 : Number of recent logs to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current license status and recent activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ESTADO DEL SISTEMA DE LICENCIAS ===');
        $this->newLine();

        // Licencia actual
        $currentLicense = License::getCurrentLicense();

        if ($currentLicense) {
            $this->info('✅ LICENCIA ACTIVA');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Código', $currentLicense->license_code],
                    ['Estado', ucfirst($currentLicense->status)],
                    ['Fecha de Inicio', $currentLicense->start_date->format('d/m/Y H:i:s')],
                    ['Fecha de Expiración', $currentLicense->end_date->format('d/m/Y H:i:s')],
                    ['Días Restantes', $currentLicense->daysUntilExpiration()],
                    ['Activada el', $currentLicense->activated_at ? $currentLicense->activated_at->format('d/m/Y H:i:s') : 'N/A'],
                    ['Usuario', $currentLicense->user_email ?? 'N/A'],
                ]
            );

            // Advertencias
            $daysRemaining = $currentLicense->daysUntilExpiration();
            if ($daysRemaining <= 7) {
                $this->warn("⚠️  ADVERTENCIA: La licencia expira en {$daysRemaining} días!");
            } elseif ($daysRemaining <= 30) {
                $this->comment("ℹ️  La licencia expira en {$daysRemaining} días.");
            }
        } else {
            $this->error('❌ NO HAY LICENCIA ACTIVA');

            // Buscar la última licencia
            $lastLicense = License::orderBy('created_at', 'desc')->first();
            if ($lastLicense) {
                $this->info('Última licencia registrada:');
                $this->table(
                    ['Campo', 'Valor'],
                    [
                        ['Código', $lastLicense->license_code],
                        ['Estado', ucfirst($lastLicense->status)],
                        ['Expiró el', $lastLicense->end_date->format('d/m/Y H:i:s')],
                        ['Creada el', $lastLicense->created_at->format('d/m/Y H:i:s')],
                    ]
                );
            }
        }

        $this->newLine();

        // Estadísticas generales
        $totalLicenses = License::count();
        $activeLicenses = License::where('status', 'active')->count();
        $expiredLicenses = License::where('status', 'expired')->count();
        $pendingLicenses = License::where('status', 'pending')->count();

        $this->info('=== ESTADÍSTICAS ===');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de Licencias', $totalLicenses],
                ['Licencias Activas', $activeLicenses],
                ['Licencias Expiradas', $expiredLicenses],
                ['Licencias Pendientes', $pendingLicenses],
            ]
        );

        // Logs recientes
        $logsCount = (int) $this->option('logs');
        if ($logsCount > 0) {
            $this->newLine();
            $this->info("=== ACTIVIDAD RECIENTE (últimos {$logsCount} eventos) ===");

            $recentLogs = LicenseLog::with('license')
                                   ->orderBy('created_at', 'desc')
                                   ->limit($logsCount)
                                   ->get();

            if ($recentLogs->count() > 0) {
                $logData = $recentLogs->map(function ($log) {
                    return [
                        $log->created_at->format('d/m/Y H:i:s'),
                        strtoupper($log->action),
                        $log->license_code ?? 'N/A',
                        ucfirst($log->level),
                        $log->message ? (strlen($log->message) > 50 ? substr($log->message, 0, 47) . '...' : $log->message) : 'N/A',
                    ];
                });

                $this->table(
                    ['Fecha', 'Acción', 'Código', 'Nivel', 'Mensaje'],
                    $logData->toArray()
                );
            } else {
                $this->comment('No hay logs recientes.');
            }
        }

        // ID de máquina actual
        $this->newLine();
        $this->info('=== INFORMACIÓN DEL SISTEMA ===');
        $machineId = License::generateMachineId();
        $this->line("ID de Máquina: <comment>{$machineId}</comment>");

        return 0;
    }
}
