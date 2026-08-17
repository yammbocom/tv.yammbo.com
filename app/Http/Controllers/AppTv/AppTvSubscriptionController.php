<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\View;
use Stripe\StripeClient;
use Wave\Plan;
use Wave\Subscription;

/**
 * Pricing + checkout para la APK YamboTV (WebView).
 *
 * - GET  /app-tv/subscription?user_id=X[&device_id=Y]
 *        Blade con planes + botones Tarjeta/PayPal. Tema Yambo.
 * - POST /api/app-tv/checkout body: {price_id, gateway, user_id}
 *        Crea Stripe Checkout Session (o PayPal subscription cuando esté habilitado).
 *        Retorna {url: "checkout.stripe.com/..."} para que el WebView redirija.
 */
class AppTvSubscriptionController extends Controller
{
    public function show(Request $request)
    {
        $userId = (int) $request->attributes->get('yambo_user_id');
        $user = $userId > 0 ? User::find($userId) : null;

        // Si el user ya tiene suscripción activa/trialing → mostrar gestión
        // (similar a /mi-suscripcion; el WebView del APK comparte la sesión Wave)
        if ($user) {
            $now = Carbon::now();
            $subscription = Subscription::where('billable_type', 'user')
                ->where('billable_id', $user->id)
                ->whereIn('status', ['active', 'trialing', 'cancelled'])
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
                })
                ->orderByDesc('id')
                ->first();

            if ($subscription) {
                $plan = Plan::find($subscription->plan_id);
                $isStripe = ($subscription->vendor_slug === 'stripe' && ! empty($subscription->vendor_customer_id));

                return View::make('app-tv.billing', [
                    'user' => $user,
                    'subscription' => $subscription,
                    'plan' => $plan,
                    'planName' => $plan->name ?? 'Premium',
                    'isStripe' => $isStripe,
                    'fromApp' => true,
                ]);
            }
        }

        // Sin sub activa → pricing como antes
        $plans = Plan::where('active', true)->orderBy('sort_order')->get()->filter(function ($p) {
            return ! empty($p->monthly_price_id) || ! empty($p->yearly_price_id);
        })->values();

        return View::make('app-tv.subscription', [
            'user' => $user,
            'user_id' => $userId,
            'device_id' => (string) $request->query('device_id', ''),
            'plans' => $plans,
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $this->assertCsrf($request);

        $validated = $request->validate([
            'price_id' => 'required|string|max:191',
            'gateway' => 'required|in:stripe',
            'cycle' => 'nullable|in:month,year',
        ]);

        // Nunca del body: si no, se abría una sesión de pago de Stripe con el
        // customer_email de otra cuenta.
        $user = User::find((int) $request->attributes->get('yambo_user_id'));
        if (! $user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $plan = Plan::where('monthly_price_id', $validated['price_id'])
            ->orWhere('yearly_price_id', $validated['price_id'])
            ->first();
        if (! $plan) {
            return response()->json(['error' => 'Plan inválido'], 422);
        }

        $cycle = $validated['cycle'] ?? ($plan->yearly_price_id === $validated['price_id'] ? 'year' : 'month');

        if ($validated['gateway'] === 'stripe') {
            return $this->createStripeCheckout($user, $plan, $validated['price_id'], $cycle);
        }

        return response()->json(['error' => 'PayPal aún no disponible'], 501);
    }

    private function createStripeCheckout(User $user, Plan $plan, string $priceId, string $cycle): JsonResponse
    {
        $secret = config('wave.stripe.secret_key') ?: (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY'));
        if (empty($secret)) {
            return response()->json(['error' => 'Stripe no configurado'], 500);
        }

        $stripe = new StripeClient($secret);

        try {
            // Metadata aligned with Wave\Http\Controllers\Billing\Webhooks\StripeWebhook::fulfill_checkout
            // so the existing /webhook/stripe auto-activates our AppTv subscriptions too.
            $metadata = [
                'billable_id' => (string) $user->id,
                'billable_type' => 'user',
                'plan_id' => (string) $plan->id,
                'billing_cycle' => $cycle,
                // Yambo-specific extras (for debugging / future use)
                'yambo_user_id' => (string) $user->id,
                'yambo_price_id' => $priceId,
                'yambo_source' => 'apk-v23',
            ];

            $session = $stripe->checkout->sessions->create([
                'mode' => 'subscription',
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'success_url' => url('/app-tv/payment-success?gateway=stripe&user_id='.$user->id.'&session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/app-tv/subscription?user_id='.$user->id),
                'client_reference_id' => (string) $user->id,
                'customer_email' => $user->email,
                'subscription_data' => [
                    'metadata' => $metadata,
                ],
                'metadata' => $metadata,
                'locale' => 'es',
            ]);

            return response()->json(['url' => $session->url]);
        } catch (\Throwable $e) {
            \Log::error('Stripe checkout error', ['ex' => $e->getMessage()]);

            return response()->json(['error' => 'No se pudo crear la sesión de pago: '.$e->getMessage()], 500);
        }
    }

    /**
     * CSRF manual — BeMusic/Wave global bypass notwithstanding (ver HANDOFF música).
     * Aceptamos tanto `_token` en body como header `X-CSRF-TOKEN` (igual que Music).
     */
    private function assertCsrf(Request $request): void
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        if (! $token || ! hash_equals((string) $request->session()->token(), (string) $token)) {
            throw new TokenMismatchException('CSRF token mismatch.');
        }
    }
}
