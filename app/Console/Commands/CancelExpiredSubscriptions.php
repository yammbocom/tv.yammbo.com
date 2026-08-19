<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

/**
 * Marca como canceladas las suscripciones cuya fecha de fin ya pasó.
 *
 * Vivía en el paquete Wave y se fue con él; el scheduler lo seguía invocando
 * cada hora y fallaba con "There are no commands defined".
 *
 * El acceso premium no depende de esto — las consultas de `whoami` y `status`
 * filtran por `ends_at > now()` —, así que esto es higiene de datos: deja el
 * estado real en la tabla y devuelve al usuario a su rol por defecto.
 */
class CancelExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:cancel-expired';

    protected $description = 'Cancela las suscripciones vencidas y sincroniza el rol del usuario';

    public function handle(): int
    {
        // El original solo miraba 'active' y dejaba los trials vencidos colgados
        // en 'trialing' para siempre; aquí entran los dos estados.
        $expired = Subscription::whereIn('status', ['active', 'trialing'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expired as $subscription) {
            $user = $subscription->user;

            $subscription->status = 'cancelled';
            $subscription->save();

            if ($user === null) {
                $this->line("Suscripción #{$subscription->id}: usuario eliminado, solo se marca el estado.");
                $count++;

                continue;
            }

            // Nunca degradar a un administrador: dejaría el panel sin acceso y
            // solo se arreglaría por base de datos.
            if ($user->hasRole('admin')) {
                $this->line("Suscripción #{$subscription->id}: se conserva el rol admin de la cuenta.");
            } else {
                $user->syncRoles([]);
                $user->assignRole(config('yammbo.default_user_role', 'registered'));
            }

            $user->clearUserCache();
            $count++;
        }

        $this->info($count > 0 ? "Suscripciones vencidas cerradas: {$count}." : 'Sin suscripciones vencidas.');

        return self::SUCCESS;
    }
}
