<?php

namespace App\Support;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

/**
 * A que publico pertenece una cuenta de la app de TV, para segmentar novedades y anuncios.
 *
 *   paid  suscripcion de pago vigente
 *   trial prueba gratis vigente (suscripcion "trialing"/"trial" o trial_ends_at futuro)
 *   free  sin acceso (o sin sesion)
 */
class TvAudience
{
    /**
     * Usuario a partir del token de la app (?t=); null si no hay o no vale.
     * Acepta tokens caducados con firma válida (AppTvToken): la app de móvil y la
     * de TV no renuevan el suyo, y con token caducado a quien paga se le trataba
     * como cuenta sin plan (anuncios de "suscríbete", perfiles con 401).
     */
    public static function user(?string $token): ?User
    {
        return $token ? AppTvToken::userForReadOnly($token) : null;
    }

    public static function segment(?User $user): string
    {
        if (! $user) {
            return 'free';
        }
        $now = Carbon::now();
        $sub = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->orderByDesc('id')
            ->first();

        if ($sub) {
            return ($sub->status === 'trialing' || $sub->vendor_slug === 'trial') ? 'trial' : 'paid';
        }
        if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isFuture()) {
            return 'trial';
        }

        return 'free';
    }

    public const LABELS = [
        'all' => 'Todos',
        'trial' => 'Solo prueba gratis',
        'paid' => 'Solo suscriptores de pago',
        'free' => 'Solo cuentas sin plan',
    ];
}
