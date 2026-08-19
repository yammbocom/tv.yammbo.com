<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Borra definitivamente las cuentas cuyo plazo de eliminación ya venció.
 *
 * Venía de Wave y desapareció con el paquete. Importa más que los otros dos:
 * es el que cumple la promesa de "eliminar mi cuenta" que el usuario aceptó,
 * y sin él las cuentas marcadas se quedan indefinidamente.
 */
class ProcessScheduledAccountDeletions extends Command
{
    protected $signature = 'accounts:process-deletions';

    protected $description = 'Elimina las cuentas cuyo plazo de borrado programado ya pasó';

    public function handle(): int
    {
        $pending = User::whereNotNull('deletion_scheduled_at')
            ->where('deletion_scheduled_at', '<=', now())
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No hay cuentas pendientes de eliminar.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($pending as $user) {
            $id = $user->id;

            try {
                // Borrado definitivo, no lógico: es lo que se le prometió.
                $user->syncRoles([]);
                $user->forceDelete();

                // Sin el correo en el log: el registro no debe sobrevivir a la cuenta.
                $this->info("Cuenta #{$id} eliminada.");
                $count++;
            } catch (\Throwable $e) {
                $this->error("No se pudo eliminar la cuenta #{$id}: ".$e->getMessage());
            }
        }

        $this->info("Cuentas eliminadas: {$count}.");

        return self::SUCCESS;
    }
}
