<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

/**
 * Poda el registro de actividad según la retención configurada.
 * Venía de Wave y se fue con el paquete.
 */
class CleanOldActivityLogs extends Command
{
    protected $signature = 'activity:clean {--days= : Días de retención; por defecto, activity.retention_days}';

    protected $description = 'Borra los registros de actividad más antiguos que la retención configurada';

    public function handle(): int
    {
        if (! config('activity.enabled', true)) {
            $this->info('El registro de actividad está desactivado.');

            return self::SUCCESS;
        }

        $days = $this->option('days') ?? config('activity.retention_days', 90);

        if (is_null($days)) {
            $this->info('Sin periodo de retención: los registros se conservan indefinidamente.');

            return self::SUCCESS;
        }

        $deleted = ActivityLog::where('created_at', '<', now()->subDays((int) $days))->delete();

        $this->info("Registros de actividad borrados: {$deleted} (retención {$days} días).");

        return self::SUCCESS;
    }
}
