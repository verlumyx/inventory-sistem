<?php

namespace App\Console\Commands;

use App\Models\LicenseLog;
use Illuminate\Console\Command;

class CleanupLicenseLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:cleanup-logs {--days=90 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old license logs to maintain database performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Limpiando logs de licencia anteriores a {$cutoffDate->format('Y-m-d H:i:s')}...");

        // Contar logs a eliminar
        $logsToDelete = LicenseLog::where('created_at', '<', $cutoffDate)->count();

        if ($logsToDelete === 0) {
            $this->info('No hay logs antiguos para eliminar.');
            return 0;
        }

        $this->info("Se encontraron {$logsToDelete} logs para eliminar.");

        if ($this->confirm('¿Desea continuar con la eliminación?')) {
            // Eliminar en lotes para evitar problemas de memoria
            $batchSize = 1000;
            $totalDeleted = 0;

            do {
                $deleted = LicenseLog::where('created_at', '<', $cutoffDate)
                                   ->limit($batchSize)
                                   ->delete();

                $totalDeleted += $deleted;

                if ($deleted > 0) {
                    $this->info("Eliminados {$deleted} logs (Total: {$totalDeleted})");
                }

            } while ($deleted > 0);

            $this->info("✅ Limpieza completada. Total de logs eliminados: {$totalDeleted}");
        } else {
            $this->info('Operación cancelada.');
        }

        return 0;
    }
}
