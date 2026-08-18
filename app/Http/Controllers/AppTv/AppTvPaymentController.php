<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Stripe\StripeClient;
use App\Models\Plan;
use App\Models\Subscription;

/**
 * GET /app-tv/payment-success?gateway=stripe&user_id=X&session_id=cs_xxx
 *
 * Landing page post-pago. Confirma la suscripción en DB (idempotente),
 * y auto-redirige al deep link yambotvapp://continue que WebViewAty$j
 * intercepta para marcar subscription_active=true en SharedPrefs.
 */
class AppTvPaymentController extends Controller
{
    public function success(Request $request)
    {
        $userId = (int) $request->attributes->get('yambo_user_id');
        $gateway = $request->query('gateway', 'stripe');
        $sessionId = $request->query('session_id');
        $user = $userId > 0 ? User::find($userId) : null;

        $confirmed = false;

        // Best-effort subscription activation si Stripe ya nos dice que está pagado.
        // El webhook también lo hace (idempotente), así que esto es un "fast path" UX.
        if ($user && $gateway === 'stripe' && $sessionId) {
            $confirmed = $this->confirmStripeSession($user, $sessionId);
        }

        return View::make('app-tv.payment-success', [
            'user' => $user,
            'gateway' => $gateway,
            'session_id' => $sessionId,
            'confirmed' => $confirmed,
        ]);
    }

    private function confirmStripeSession(User $user, string $sessionId): bool
    {
        // wave.stripe.secret_key es la única que resuelve de verdad:
        // services.stripe.secret está vacío y env() devuelve null en cuanto la
        // config está cacheada, así que el fallback dejaba el cliente sin clave.
        $secret = config('yammbo.stripe.secret_key') ?: config('services.stripe.secret');
        if (empty($secret)) {
            return false;
        }

        try {
            $stripe = new StripeClient($secret);
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription'],
            ]);

            if ($session->payment_status !== 'paid' && $session->status !== 'complete') {
                return false;
            }

            $vendorSubscriptionId = is_object($session->subscription)
                ? $session->subscription->id
                : (string) $session->subscription;

            $planId = (int) ($session->metadata->plan_id ?? $session->metadata->yambo_plan_id ?? 0);
            $priceId = (string) ($session->metadata->yambo_price_id ?? '');
            $cycle = (string) ($session->metadata->billing_cycle ?? $session->metadata->yambo_cycle ?? 'month');

            if ($planId <= 0) {
                $plan = Plan::where('monthly_price_id', $priceId)
                    ->orWhere('yearly_price_id', $priceId)
                    ->first();
                if ($plan) {
                    $planId = $plan->id;
                }
            }

            $ends = null;
            if (is_object($session->subscription) && isset($session->subscription->current_period_end)) {
                $ends = Carbon::createFromTimestamp($session->subscription->current_period_end);
            } elseif ($cycle === 'year') {
                $ends = Carbon::now()->addYear();
            } else {
                $ends = Carbon::now()->addMonth();
            }

            Subscription::updateOrCreate(
                [
                    'vendor_slug' => 'stripe',
                    'vendor_subscription_id' => $vendorSubscriptionId,
                ],
                [
                    'billable_type' => 'user',
                    'billable_id' => $user->id,
                    'plan_id' => $planId ?: 1,
                    'vendor_customer_id' => is_object($session->customer) ? $session->customer->id : (string) $session->customer,
                    'vendor_transaction_id' => $session->id,
                    'cycle' => $cycle,
                    'status' => 'active',
                    'seats' => 1,
                    'ends_at' => $ends,
                    'last_payment_at' => Carbon::now(),
                    'next_payment_at' => $ends,
                ]
            );

            return true;
        } catch (\Throwable $e) {
            \Log::error('confirmStripeSession error', ['ex' => $e->getMessage()]);

            return false;
        }
    }
}
