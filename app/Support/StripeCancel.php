<?php

namespace App\Support;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Baja de una suscripción de Stripe, la misma para los tres caminos que cancelan:
 * el botón de /panel/subscriptions, "Cancelada" en la ficha del usuario del panel
 * y /mi-suscripcion/cancelar del propio usuario. Antes los tres solo tocaban la
 * fila local: el acceso se cortaba y Stripe seguía cobrando.
 *
 * Se cancela al final del período pagado (cancel_at_period_end): no se renueva y
 * lo ya pagado se disfruta. Stripe manda customer.subscription.updated con
 * cancel_at (el webhook fija ends_at) y, al llegar la fecha,
 * customer.subscription.deleted (el webhook la marca cancelada y baja el rol).
 *
 * Primero Stripe, después la fila: si Stripe falla no se toca nada y el error se
 * enseña al humano; si la fila fallara después, el webhook la pone al día.
 */
class StripeCancel
{
    /**
     * @return array{ok: bool, ends_at: ?string, message: string}
     */
    public static function atPeriodEnd(Subscription $sub): array
    {
        if ($sub->vendor_slug !== 'stripe') {
            return ['ok' => true, 'ends_at' => null, 'message' => 'No es de Stripe: no hay cobro que parar.'];
        }

        try {
            $stripe = new \Stripe\StripeClient(config('yammbo.stripe.secret_key'));

            $ids = [];
            if ($sub->vendor_subscription_id) {
                $ids = [$sub->vendor_subscription_id];
            } elseif ($sub->vendor_customer_id) {
                // Fila sin id de suscripción: se buscan las que cobran a ese cliente.
                foreach ($stripe->subscriptions->all(['customer' => $sub->vendor_customer_id, 'status' => 'all', 'limit' => 20])->data as $s) {
                    if (in_array($s->status, ['active', 'trialing', 'past_due', 'unpaid'], true)) {
                        $ids[] = $s->id;
                    }
                }
                if (count($ids) > 1) {
                    return ['ok' => false, 'ends_at' => null, 'message' => 'El cliente '.$sub->vendor_customer_id.' tiene '.count($ids).' suscripciones vivas en Stripe: revísalo en el panel de Stripe, no se canceló nada.'];
                }
            }

            if (! $ids) {
                return ['ok' => true, 'ends_at' => null, 'message' => 'Stripe no tiene ninguna suscripción cobrando para esta fila.'];
            }

            $updated = $stripe->subscriptions->update($ids[0], ['cancel_at_period_end' => true]);
            $end = $updated->cancel_at ?? ($updated->items->data[0]->current_period_end ?? null);

            return [
                'ok' => true,
                'ends_at' => $end ? Carbon::createFromTimestamp($end)->toDateTimeString() : null,
                'message' => 'Cancelada en Stripe: no se renovará'.($end ? ' y termina el '.Carbon::createFromTimestamp($end)->format('d/m/Y') : '').'.',
            ];
        } catch (\Throwable $e) {
            Log::error('stripe-cancel: failed', ['subscription_id' => $sub->id, 'error' => $e->getMessage()]);

            return ['ok' => false, 'ends_at' => null, 'message' => 'Stripe rechazó la cancelación ('.$e->getMessage().'). No se cambió nada: sigue cobrando.'];
        }
    }
}
