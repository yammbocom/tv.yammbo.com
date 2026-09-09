<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;

/**
 * Misma regla de acceso que usa /api/app-tv/whoami: manda la tabla
 * `subscriptions`, no el rol. Extraída aquí para que el proxy del addon
 * premium no tenga una copia distinta que se desincronice.
 */
class YamboSubscription
{
    /** @return array{active: bool, plan: ?string} */
    public static function for($user): array
    {
        if (! $user) {
            return ['active' => false, 'plan' => null];
        }

        $now = Carbon::now();
        $sub = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->orderByDesc('id')
            ->first();

        if ($sub) {
            $plan = Plan::find($sub->plan_id);

            return ['active' => true, 'plan' => $plan->name ?? 'Premium'];
        }

        if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture()) {
            return ['active' => true, 'plan' => 'Trial'];
        }

        return ['active' => false, 'plan' => null];
    }

    public static function isActive($user): bool
    {
        return static::for($user)['active'];
    }
}
