<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

/**
 * Webhook de Stripe. Copia de Wave\Http\Controllers\Billing\Webhooks\StripeWebhook
 * para poder retirar el paquete, con el mismo comportamiento observable.
 *
 * Es el único punto que activa suscripciones, y lo alimentan TRES flujos de
 * checkout distintos (el de /pricing, el de la app y el que quedaba de Wave),
 * todos construyendo la misma metadata: billable_id, billable_type, plan_id y
 * billing_cycle. Ese contrato no se puede tocar sin romper los tres a la vez.
 *
 * Única diferencia con el original: devuelve respuestas de Laravel en vez de
 * `http_response_code()` + `exit()`. Los códigos que ve Stripe son los mismos.
 */
class StripeWebhook extends Controller
{
    public function handler(Request $request): Response
    {
        $payload = $request->getContent();
        $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                config('yammbo.stripe.webhook_secret')
            );
        } catch (UnexpectedValueException $e) {
            return response('', 400);
        } catch (SignatureVerificationException $e) {
            return response('', 400);
        }

        if ($event->type == 'checkout.session.completed'
            || $event->type == 'checkout.session.async_payment_succeeded') {
            $this->fulfill_checkout($event->data->object->id, $event);
        }

        // Alguien tocó algo en su portal de cliente: cambio de plan o cancelación.
        if ($event->type == 'customer.subscription.updated') {
            $stripeSubscription = $event->data->object;

            $subscription = Subscription::where('vendor_subscription_id', $stripeSubscription->id)->first();
            if (isset($subscription)) {
                // El intervalo es 'year' o 'month'.
                $subscriptionCycle = $stripeSubscription->plan->interval;
                $plan_price_column = ($subscriptionCycle == 'year') ? 'yearly_price_id' : 'monthly_price_id';
                $updatedPlan = Plan::where($plan_price_column, $stripeSubscription->plan->id)->first();

                // Lo que hacía Wave\User::switchPlans(), en línea: ese método
                // exige un Wave\Plan y aquí llega un App\Models\Plan, así que
                // llamarlo lanzaba TypeError y el webhook devolvía 500.
                $subscription->user->syncRoles([]);
                $subscription->user->assignRole($updatedPlan->role->name);

                $subscription->cycle = $subscriptionCycle;
                $subscription->plan_id = $updatedPlan->id;

                // cancel_at con valor = el usuario canceló y sigue hasta esa fecha.
                if (is_null($stripeSubscription->cancel_at)) {
                    $subscription->ends_at = null;
                } else {
                    $subscription->ends_at = Carbon::createFromTimestamp($stripeSubscription->cancel_at)->toDateTimeString();
                }

                $subscription->save();
                $subscription->user->clearUserCache();
            }
        }

        if ($event->type == 'customer.subscription.deleted') {
            $stripeSubscription = $event->data->object;

            $subscription = Subscription::where('vendor_subscription_id', $stripeSubscription->id)->first();
            if (isset($subscription)) {
                $subscription->cancel();
                $subscription->user->clearUserCache();

                // Aviso al usuario de que su acceso quedo pausado
                try {
                    $plan = \App\Models\Plan::find($subscription->plan_id);
                    \App\Support\YamboMail::subscriptionExpired($subscription->user, array_filter([
                        'Plan' => $plan ? $plan->name : null,
                        'Vencio el' => now()->format('d/m/Y'),
                    ]));
                } catch (\Throwable $e) { \Log::warning('mail vencida: '.$e->getMessage()); }
            }
        }

        // Cobro rechazado: avisar para que actualice la tarjeta
        if ($event->type == 'invoice.payment_failed') {
            try {
                $inv = $event->data->object;
                $sub = Subscription::where('vendor_subscription_id', $inv->subscription)->first();
                if ($sub && $sub->user) {
                    $plan = \App\Models\Plan::find($sub->plan_id);
                    // Marca para que la TV pueda avisarlo al abrir
                    \Illuminate\Support\Facades\Cache::put('pago_fallido_'.$sub->user->id, [
                        'importe' => isset($inv->amount_due) ? ('$'.number_format($inv->amount_due/100, 2)) : null,
                    ], now()->addDays(14));

                    \App\Support\YamboMail::paymentFailed($sub->user, array_filter([
                        'Plan' => $plan ? $plan->name : null,
                        'Importe' => isset($inv->amount_due) ? ('$'.number_format($inv->amount_due/100, 2)) : null,
                        'Intento' => now()->format('d/m/Y'),
                    ]));
                }
            } catch (\Throwable $e) { \Log::warning('mail pago fallido: '.$e->getMessage()); }
        }

        // Pago correcto de una renovacion: recibo
        if ($event->type == 'invoice.paid') {
            try {
                $inv = $event->data->object;
                $sub = Subscription::where('vendor_subscription_id', $inv->subscription)->first();
                if ($sub && $sub->user && ($inv->billing_reason ?? '') === 'subscription_cycle') {
                    $plan = \App\Models\Plan::find($sub->plan_id);
                    \Illuminate\Support\Facades\Cache::forget('pago_fallido_'.$sub->user->id);
                    \App\Support\YamboMail::receipt($sub->user, array_filter([
                        'Plan' => $plan ? $plan->name : null,
                        'Importe' => isset($inv->amount_paid) ? ('$'.number_format($inv->amount_paid/100, 2)) : null,
                        'Fecha' => now()->format('d/m/Y'),
                        'Metodo' => 'Stripe',
                    ]));
                }
            } catch (\Throwable $e) { \Log::warning('mail recibo: '.$e->getMessage()); }
        }

        return response('', 200);
    }

    public function fulfill_checkout($session_id, $event): void
    {
        Stripe::setApiKey(config('yammbo.stripe.secret_key'));

        // Idempotente: Stripe reintenta y puede entregar el mismo evento varias
        // veces, incluso a la vez.
        $cacheKey = 'stripe_checkout_session_'.$session_id;
        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addHours(24));

        $checkout_session = Session::retrieve($session_id);

        if ($checkout_session->payment_status != 'unpaid') {

            $existingSubscription = Subscription::where('vendor_subscription_id', $checkout_session->subscription)->first();
            if ($existingSubscription) {
                // Segunda red por si el evento llega de nuevo pasadas las 24 h.
                return;
            }

            $billable_id = $checkout_session->metadata->billable_id;
            $billable_type = $checkout_session->metadata->billable_type;
            $plan_id = $checkout_session->metadata->plan_id;
            $billing_cycle = $checkout_session->metadata->billing_cycle;

            $user = User::find($billable_id);

            $plan = Plan::find($plan_id);
            $user->syncRoles([]);
            $user->assignRole($plan->role->name);

            // Recibo de la primera compra
            try {
                \App\Support\YamboMail::receipt($user, array_filter([
                    'Plan' => $plan ? $plan->name : null,
                    'Ciclo' => $billing_cycle === 'yearly' ? 'Anual' : 'Mensual',
                    'Fecha' => now()->format('d/m/Y'),
                    'Metodo' => 'Stripe',
                ]));
            } catch (\Throwable $e) { \Log::warning('mail recibo compra: '.$e->getMessage()); }

            Subscription::create([
                'billable_type' => $billable_type,
                'billable_id' => $billable_id,
                'plan_id' => $plan_id,
                'vendor_slug' => 'stripe',
                'vendor_customer_id' => $checkout_session->customer,
                'vendor_subscription_id' => $checkout_session->subscription,
                'cycle' => $billing_cycle,
                'status' => 'active',
                'seats' => 1,
            ]);

            $user->clearUserCache();
        }
    }
}
