<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use App\Models\Plan;
use App\Models\Subscription;

/**
 * Gestión de suscripción para usuarios web (tv.yammbo.com).
 *
 * GET  /mi-suscripcion              → vista con info del plan + acciones
 * GET  /mi-suscripcion/portal       → redirige al Stripe Customer Portal (gateway=stripe)
 * POST /mi-suscripcion/cancelar     → cancela la suscripción local (gateway=manual)
 *
 * Si el user no tiene sub activa, redirige a /pricing.
 * Auth requerida: ResolveAppTvUser (sesión web o Bearer JWT). El `user_id`
 * que venga en el request se ignora.
 */
class BillingController extends Controller
{
    public function show(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect('/auth/login?redirect='.urlencode('/mi-suscripcion'));
        }

        $now = Carbon::now();

        $subscription = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing', 'cancelled'])
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->orderByDesc('id')
            ->first();

        if (! $subscription) {
            return redirect('/pricing');
        }

        $plan = Plan::find($subscription->plan_id);
        $isStripe = ($subscription->vendor_slug === 'stripe' && ! empty($subscription->vendor_customer_id));

        return view('app-tv.billing', [
            'user' => $user,
            'subscription' => $subscription,
            'plan' => $plan,
            'planName' => $plan->name ?? 'Premium',
            'isStripe' => $isStripe,
        ]);
    }

    /**
     * Redirige al Stripe Customer Portal. Funciona si la sub es Stripe y tiene
     * vendor_customer_id válido. Para subs manuales/admin, vuelve a /mi-suscripcion.
     */
    public function portal(Request $request): RedirectResponse
    {
        // El dueño lo resuelve ResolveAppTvUser (sesión web o Bearer JWT del APK).
        // Antes se leía `user_id` del request cuando no había sesión: `?user_id=N`
        // abría el Stripe Customer Portal de un tercero y cancelaba su suscripción.
        $userId = (int) $request->attributes->get('yambo_user_id');
        $user = $userId > 0 ? User::find($userId) : null;
        if (! $user) {
            return redirect('/auth/login?redirect='.urlencode('/mi-suscripcion'));
        }
        $fromApp = ! auth()->check();

        $subscription = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->whereNotNull('vendor_customer_id')
            ->where('vendor_slug', 'stripe')
            ->orderByDesc('id')
            ->first();

        $backUrl = $fromApp ? '/app-tv/subscription?user_id='.$user->id : '/mi-suscripcion';

        if (! $subscription) {
            return redirect($backUrl)->with([
                'message' => __('app-tv.billing.msg_portal_not_stripe'),
                'message_type' => 'warning',
            ]);
        }

        try {
            // Ver nota en AppTvPaymentController: services.stripe.secret está
            // vacío y env() es null con la config cacheada.
            $stripe = new StripeClient(config('yammbo.stripe.secret_key') ?: config('services.stripe.secret'));
            $session = $stripe->billingPortal->sessions->create([
                'customer' => $subscription->vendor_customer_id,
                'return_url' => url($backUrl),
            ]);
            return redirect()->away($session->url);
        } catch (\Throwable $e) {
            return redirect($backUrl)->with([
                'message' => __('app-tv.billing.msg_portal_error', ['error' => $e->getMessage()]),
                'message_type' => 'danger',
            ]);
        }
    }

    public function cancel(Request $request): RedirectResponse
    {
        // El dueño lo resuelve ResolveAppTvUser (sesión web o Bearer JWT del APK).
        // Antes se leía `user_id` del request cuando no había sesión: `?user_id=N`
        // abría el Stripe Customer Portal de un tercero y cancelaba su suscripción.
        $userId = (int) $request->attributes->get('yambo_user_id');
        $user = $userId > 0 ? User::find($userId) : null;
        if (! $user) {
            return redirect('/auth/login?redirect='.urlencode('/mi-suscripcion'));
        }
        $fromApp = ! auth()->check();

        $subscription = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->orderByDesc('id')
            ->first();

        $backUrl = $fromApp ? '/app-tv/subscription?user_id='.$user->id : '/mi-suscripcion';

        if (! $subscription) {
            return redirect($backUrl)->with([
                'message' => __('app-tv.billing.msg_cancel_no_active'),
                'message_type' => 'warning',
            ]);
        }

        // Cancelación: mantener `ends_at` (el usuario sigue con acceso hasta esa fecha)
        // pero marcar status='cancelled' para que no se renueve.
        DB::table('subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'status' => 'cancelled',
                'updated_at' => Carbon::now(),
            ]);

        return redirect($backUrl)->with([
            'message' => __('app-tv.billing.msg_cancel_success'),
            'message_type' => 'success',
        ]);
    }
}
