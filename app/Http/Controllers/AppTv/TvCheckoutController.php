<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Flujo de suscripcion iniciado desde la app de TV (QR del paywall).
 *
 * El QR lleva /precios-tv?t=<jwt>. El token identifica al usuario, asi que el
 * telefono NO tiene que iniciar sesion: elige plan -> Stripe -> pantalla de
 * "vuelve a tu TV" (la TV entra sola porque el paywall consulta cada 5s).
 */
class TvCheckoutController extends Controller
{
    /** Resuelve el usuario del magic-link, sin tocar la sesion web. */
    private function userFromToken(Request $request)
    {
        $t = (string) $request->query('t', $request->input('t', ''));
        if ($t === '') {
            return null;
        }
        try {
            return \Tymon\JWTAuth\Facades\JWTAuth::setToken($t)->authenticate() ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function plans(Request $request)
    {
        $user = $this->userFromToken($request);
        $plans = Plan::where('active', true)->orderBy('id')->get();

        return view('precios-tv', [
            'user'  => $user,
            'plans' => $plans,
            't'     => (string) $request->query('t', ''),
        ]);
    }

    public function checkout(Request $request)
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return redirect('/precios-tv');
        }

        $planId = (int) $request->input('plan_id');
        $cycle  = $request->input('cycle') === 'yearly' ? 'yearly' : 'monthly';

        $plan = Plan::where('id', $planId)->where('active', true)->first();
        if (! $plan) {
            abort(404, 'Plan no encontrado');
        }

        $priceId = $cycle === 'monthly' ? $plan->monthly_price_id : $plan->yearly_price_id;
        if (! $priceId) {
            abort(422, 'El plan no tiene precio configurado para ese ciclo');
        }

        $secretKey = config('yammbo.stripe.secret_key');
        if (! $secretKey) {
            abort(500, 'Stripe no configurado');
        }

        $t = (string) $request->input('t', '');

        $stripe = new \Stripe\StripeClient($secretKey);
        // Fachada (proxy) permitida: Stripe devuelve a la MISMA URL neutra.
        $allowedProxies = ['edge-x7q2m9.pages.dev'];
        $fwdHost = (string) $request->header('X-Forwarded-Host', '');
        $proxyBase = in_array($fwdHost, $allowedProxies, true) ? ('https://' . $fwdHost) : rtrim(url('/'), '/');
        $session = $stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'metadata' => [
                'billable_type' => 'user',
                'billable_id' => $user->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'source' => 'tv',
            ],
            'client_reference_id' => (string) $user->id,
            'customer_email' => $user->email,
            'success_url' => $proxyBase . '/precios-tv/listo',
            'cancel_url' => $proxyBase . '/precios-tv' . ($t !== '' ? ('?t=' . urlencode($t)) : ''),
        ]);

        return redirect()->away($session->url);
    }

    public function done(Request $request)
    {
        // ?src=web cuando el pago viene del landing; sin el, viene de una app.
        return view('precios-tv-listo', ['src' => (string) $request->query('src', '')]);
    }
}
