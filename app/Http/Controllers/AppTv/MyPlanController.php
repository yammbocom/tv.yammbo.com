<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Datos de la suscripcion para la pantalla NATIVA "Mi Cuenta" de la app de TV.
 *
 * GET /api/app-tv/mi-plan?t=<jwt>  ->  plan, estado, ciclo y proxima fecha.
 * Se autentica con el mismo token del login por QR (no toca la sesion web).
 */
class MyPlanController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) $request->query('t', '');
        $out = [
            'ok'        => false,
            'plan'      => null,
            'status'    => null,
            'cycle'     => null,
            'renews_at' => null,
            'is_trial'  => false,
            'active'    => false,
        ];

        if ($token === '') {
            return response()->json($out);
        }

        try {
            $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->authenticate();
            if (! $user) {
                return response()->json($out);
            }

            $now = Carbon::now();
            $out['ok']     = true;
            $out['limits'] = \App\Support\DeviceGuard::limitsOf($user);
            $out['active'] = AppTvAuthController::isSubscriptionActive($user);

            $sub = Subscription::where('billable_type', 'user')
                ->where('billable_id', $user->id)
                ->whereIn('status', ['active', 'trialing'])
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
                })
                ->orderByDesc('id')
                ->first();

            // Sin suscripcion de pago: puede seguir vigente la prueba gratis
            if (! $sub) {
                if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture()) {
                    $out['plan']      = 'Prueba gratis';
                    $out['status']    = 'Prueba';
                    $out['is_trial']  = true;
                    $out['renews_at'] = Carbon::parse($user->trial_ends_at)->format('d/m/Y');
                }
                return response()->json($out);
            }

            $plan = Plan::find($sub->plan_id);
            $isTrial = ($sub->status === 'trialing' || $sub->vendor_slug === 'trial');

            $out['plan']     = $isTrial ? 'Prueba gratis' : ($plan ? $plan->name : 'Suscripcion');
            $out['status']   = $isTrial ? 'Prueba' : 'Activa';
            $out['is_trial'] = $isTrial;
            $out['cycle']    = $sub->cycle === 'year' ? 'Anual' : ($sub->cycle === 'month' ? 'Mensual' : null);

            $next = $sub->ends_at ?: $sub->trial_ends_at;
            if ($next) {
                $out['renews_at'] = Carbon::parse($next)->format('d/m/Y');
            }

            return response()->json($out);
        } catch (\Throwable $e) {
            return response()->json($out);
        }
    }
}
