<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Concesion manual de acceso desde el panel de administracion.
 *
 * El acceso de la app NO depende del rol sino de la tabla subscriptions
 * (ver AppTvAuthController::isSubscriptionActive). Cambiar solo el rol no
 * activaba nada; esto sincroniza ambos: crea/reactiva una suscripcion "manual"
 * y ademas asigna el rol del plan.
 *
 * Se usa para cuentas de cortesia, arreglos y pruebas. Las suscripciones de
 * pago las sigue gestionando Stripe por su webhook.
 */
class AccessAdmin
{
    /** Activa (o cancela) el acceso de un usuario a un plan. */
    public static function grant(User $user, int $planId, string $status = 'active'): void
    {
        $plan = Plan::find($planId);
        if (! $plan) {
            return;
        }

        $status = $status === 'cancelled' ? 'cancelled' : 'active';

        // Reutiliza la suscripcion existente del usuario si la hay
        $sub = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->orderByDesc('id')
            ->first();

        $data = [
            'plan_id'    => $plan->id,
            'status'     => $status,
            'cycle'      => $sub->cycle ?? 'month',
            'seats'      => 1,
            'updated_at' => now(),
        ];

        // Acceso concedido a mano: vigencia larga para que no caduque solo.
        // Si se cancela, se corta al momento.
        if ($status === 'active') {
            $data['ends_at'] = now()->addYears(5);
        } else {
            $data['ends_at'] = now();
        }

        if ($sub) {
            // Un acceso dado a mano deja de ser "prueba": si no, Mi Cuenta lo
            // sigue mostrando como "Prueba gratis" en vez del plan real.
            // Las de Stripe conservan su origen para no falsear la facturacion.
            if (in_array((string) $sub->vendor_slug, ['', 'trial', 'manual'], true)) {
                $data['vendor_slug'] = 'manual';
            }

            DB::table('subscriptions')->where('id', $sub->id)->update($data);
        } else {
            DB::table('subscriptions')->insert(array_merge($data, [
                'billable_type' => 'user',
                'billable_id'   => $user->id,
                'vendor_slug'   => 'manual',
                'created_at'    => now(),
            ]));
        }

        // Mantener el rol del plan en sintonia con el acceso
        try {
            if ($status === 'active' && $plan->role) {
                $user->syncRoles([$plan->role->name]);
            }
            $user->clearUserCache();
        } catch (\Throwable $e) {
            // el rol es secundario; el acceso ya quedo en subscriptions
        }
    }
}
