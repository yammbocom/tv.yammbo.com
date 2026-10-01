<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Promo de alta: toda cuenta nueva recibe el plan de config('yammbo.signup_promo')
 * gratis durante N dias (fila en subscriptions con vendor_slug 'promo').
 *
 * Igual que AccessAdmin, el acceso real depende de subscriptions, no del rol:
 * esto inserta/actualiza esa fila y sincroniza el rol del plan.
 *
 * Nunca pisa a quien ya paga por Stripe ni a quien ya tiene un acceso igual o
 * mejor dado a mano (p. ej. Premium manual por 5 anios).
 */
class SignupPromo
{
    public static function enabled(): bool
    {
        $days = (int) config('yammbo.signup_promo.days', 0);
        $planId = (int) config('yammbo.signup_promo.plan_id', 0);

        return $days > 0 && Plan::find($planId) !== null;
    }

    /** Hasta cuando llega la promo para este usuario, segun su fecha de alta. */
    public static function untilFor(User $user): Carbon
    {
        $days = (int) config('yammbo.signup_promo.days', 0);

        return Carbon::parse($user->created_at ?? now())->addDays($days);
    }

    /**
     * Decide que tocaria hacer con este usuario, sin escribir nada. La usan
     * grant() y el --dry-run del comando de backfill, para no duplicar la logica.
     *
     * @return array{action: string, until: ?Carbon, reason: ?string}
     */
    public static function plan(User $user): array
    {
        if (! self::enabled()) {
            return ['action' => 'skip', 'until' => null, 'reason' => 'promo desactivada'];
        }

        $plan = Plan::find((int) config('yammbo.signup_promo.plan_id'));
        if (! $plan) {
            return ['action' => 'skip', 'until' => null, 'reason' => 'plan de la promo no existe'];
        }

        // Nunca interferir con quien ya paga por Stripe.
        $paga = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->where('vendor_slug', 'stripe')
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
        if ($paga) {
            return ['action' => 'skip', 'until' => null, 'reason' => 'ya paga por Stripe'];
        }

        // Una sola promo por cuenta: si ya la tuvo (aunque un admin la haya
        // cancelado), no se vuelve a dar ni se reactiva.
        $yaTuvo = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->where('vendor_slug', 'promo')
            ->exists();
        if ($yaTuvo) {
            return ['action' => 'skip', 'until' => null, 'reason' => 'ya tiene la promo'];
        }

        $until = self::untilFor($user);
        if ($until->lessThanOrEqualTo(now())) {
            return ['action' => 'skip', 'until' => null, 'reason' => 'la promo ya habria caducado'];
        }

        // Si ya tiene un acceso (manual, etc.) igual o mejor que la promo y que
        // dura al menos hasta donde llegaria la promo, no lo pisa.
        $devicesPromo = (int) ($plan->limits['devices'] ?? 0);
        $mejor = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->whereNotIn('vendor_slug', ['promo', 'stripe'])
            ->where(function ($q) use ($until) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $until);
            })
            ->with('plan')
            ->get()
            ->first(function ($sub) use ($devicesPromo) {
                $devices = (int) ($sub->plan->limits['devices'] ?? 0);

                return $devices >= $devicesPromo;
            });
        if ($mejor) {
            return ['action' => 'skip', 'until' => null, 'reason' => 'ya tiene un acceso igual o mejor'];
        }

        return ['action' => 'grant', 'until' => $until, 'reason' => null];
    }

    /**
     * Aplica la promo de alta a un usuario. Idempotente: se puede llamar varias
     * veces sin duplicar filas ni empeorar un acceso ya mejor.
     */
    public static function grant(User $user): ?Carbon
    {
        $decision = self::plan($user);
        if ($decision['action'] !== 'grant') {
            return null;
        }

        $until = $decision['until'];
        $plan = Plan::find((int) config('yammbo.signup_promo.plan_id'));

        // Reutiliza la fila de promo del usuario si ya la tenia (p. ej. al
        // reejecutar el backfill); nunca crea una segunda.
        $sub = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->where('vendor_slug', 'promo')
            ->orderByDesc('id')
            ->first();

        $data = [
            'plan_id' => $plan->id,
            'status' => 'active',
            'cycle' => 'year',
            'seats' => 1,
            'ends_at' => $until->format('Y-m-d H:i:s'),
            'trial_ends_at' => null,
            'updated_at' => now(),
        ];

        if ($sub) {
            DB::table('subscriptions')->where('id', $sub->id)->update($data);
        } else {
            DB::table('subscriptions')->insert(array_merge($data, [
                'billable_type' => 'user',
                'billable_id' => $user->id,
                'vendor_slug' => 'promo',
                'created_at' => now(),
            ]));
        }

        try {
            // Nunca quitar el rol admin: dejaria el panel sin acceso
            if ($plan->role && ! $user->hasRole('admin')) {
                $user->syncRoles([$plan->role->name]);
            }
            $user->clearUserCache();
        } catch (\Throwable $e) {
            // el rol es secundario; el acceso ya quedo en subscriptions
        }

        return $until;
    }
}
