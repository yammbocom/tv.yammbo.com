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
    /**
     * Activa (o cancela) el acceso de un usuario a un plan.
     *
     * @return string|null aviso para el admin (p. ej. si Stripe sigue cobrando)
     */
    public static function grant(User $user, int $planId, string $status = 'active'): ?string
    {
        $plan = Plan::find($planId);
        if (! $plan) {
            return 'Plan no encontrado: no se cambió el acceso.';
        }

        $status = $status === 'cancelled' ? 'cancelled' : 'active';

        // Reutiliza la suscripcion manual/prueba del usuario si la hay. Nunca se
        // pisa una fila de Stripe: su webhook la volveria a sobrescribir en la
        // siguiente renovacion y el panel mentiria sobre lo que se cobra.
        $sub = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('vendor_slug')->orWhereIn('vendor_slug', ['', 'trial', 'manual']);
            })
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
            $data['vendor_slug'] = 'manual';

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

        // El panel no cancela nada en Stripe: si paga por ahi, se avisa.
        $stripeActive = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->where('vendor_slug', 'stripe')
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
        if ($stripeActive) {
            return $status === 'cancelled'
                ? 'Acceso manual cancelado, pero este usuario tiene una suscripción de Stripe activa: sigue teniendo acceso y Stripe le sigue cobrando. Cancélala en Stripe.'
                : 'Este usuario además paga una suscripción en Stripe: el cobro continúa. Si el acceso manual la sustituye, cancélala en Stripe.';
        }

        return null;
    }
}
