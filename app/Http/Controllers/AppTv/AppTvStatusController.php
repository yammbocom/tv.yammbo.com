<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;

/**
 * GET /api/app-tv/status
 *
 * El usuario sale de ResolveAppTvUser (sesión web o JWT Bearer), nunca del
 * request: antes cualquiera podía consultar el plan de otra cuenta.
 *
 * Usado por ActivateAty$StatusTask para chequear si el user tiene suscripción
 * activa al entrar a la app (cold-start).
 *
 * Response:
 *   {
 *     "subscription_active": true|false,
 *     "plan": "Premium"|null,
 *     "trial_ends_at": "2026-04-28T00:00:00Z"|null,
 *     "ends_at": "2026-05-01T00:00:00Z"|null
 *   }
 */
class AppTvStatusController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = (int) $request->attributes->get('yambo_user_id');

        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'active' => false,
                'subscription_active' => false,
                'error' => 'Usuario no encontrado',
            ], 200);
        }

        $now = Carbon::now();

        $subscription = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->orderByDesc('id')
            ->first();

        if ($subscription) {
            $plan = Plan::find($subscription->plan_id);
            $planName = $plan->name ?? 'Premium';

            return response()->json([
                'active' => true,
                'subscription_active' => true,
                'plan' => $planName,
                'status' => $subscription->status,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
            ]);
        }

        // Fallback a trial del User si existe
        if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture()) {
            return response()->json([
                'active' => true,
                'subscription_active' => true,
                'plan' => 'Prueba gratuita',
                'status' => 'trialing',
                'trial_ends_at' => $user->trial_ends_at,
                'ends_at' => $user->trial_ends_at,
            ]);
        }

        return response()->json([
            'active' => false,
            'subscription_active' => false,
            'plan' => null,
            'status' => 'inactive',
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);
    }
}
