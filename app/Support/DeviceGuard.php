<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Limite de dispositivos y permisos por plan.
 *
 * Vive en el SERVIDOR a proposito: aunque alguien modifique la app, no puede
 * darse mas dispositivos ni desbloquear lo que su plan no incluye, porque la
 * decision no se toma en el aparato.
 *
 * Limites: Basic 1 · Standard 2 · Premium 3 (y el texto de los planes se
 * actualizo para que coincida con lo que se entrega).
 */
class DeviceGuard
{
    private const DEFAULT_DEVICES = 1;

    /** Plan activo del usuario (o null si solo tiene prueba/nada). */
    public static function planOf(User $user): ?Plan
    {
        $now = Carbon::now();
        $sub = Subscription::where('billable_type', 'user')
            ->where('billable_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->orderByDesc('id')
            ->first();

        return $sub ? Plan::find($sub->plan_id) : null;
    }

    /** Limites efectivos: del plan, o los de prueba si no hay plan de pago. */
    public static function limitsOf(User $user): array
    {
        $plan = self::planOf($user);
        $limits = [];

        if ($plan) {
            // El modelo puede devolver limits ya como array (cast) o como texto JSON
            $raw = $plan->limits;
            if (is_array($raw)) {
                $limits = $raw;
            } elseif (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $limits = $decoded;
                }
            }
        }

        return [
            'plan'    => $plan ? $plan->name : 'Prueba',
            'devices' => (int) ($limits['devices'] ?? self::DEFAULT_DEVICES),
            'live_tv' => (bool) ($limits['live_tv'] ?? true),   // en prueba se ve todo
        ];
    }

    /**
     * Registra el dispositivo y aplica el limite.
     *
     * Si se supera, se expulsa al mas antiguo (mejor experiencia que rechazar:
     * el usuario que acaba de vincular su TV nueva entra, y el aparato que no
     * usaba desde hace meses se cae).
     *
     * @return array{allowed:bool, limit:int, active:int, kicked:?string}
     */
    public static function register(User $user, string $deviceId, string $platform = 'tv', ?string $name = null): array
    {
        $limits = self::limitsOf($user);
        $max = max(1, (int) $limits['devices']);
        $deviceId = substr(trim($deviceId), 0, 191);

        if ($deviceId === '') {
            return ['allowed' => true, 'limit' => $max, 'active' => 0, 'kicked' => null];
        }

        $existe = DB::table('tv_devices')
            ->where('user_id', $user->id)->where('device_id', $deviceId)->exists();

        if ($existe) {
            DB::table('tv_devices')
                ->where('user_id', $user->id)->where('device_id', $deviceId)
                ->update([
                    'platform'     => $platform,
                    'name'         => $name ? substr($name, 0, 191) : null,
                    'last_seen_at' => now(),
                    'updated_at'   => now(),
                    // Si estaba expulsado y el usuario lo vuelve a vincular, revive
                    'revoked_at'   => null,
                    'revoked_by'   => null,
                ]);
        } else {
            DB::table('tv_devices')->insert([
                'user_id'      => $user->id,
                'device_id'    => $deviceId,
                'platform'     => $platform,
                'name'         => $name ? substr($name, 0, 191) : null,
                'last_seen_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // El aparato que acaba de vincularse SIEMPRE se queda; se expulsa al mas
        // antiguo de los demas (si no, con marcas de tiempo iguales se echaba al nuevo).
        $kicked = null;
        $otros = DB::table('tv_devices')
            ->where('user_id', $user->id)
            ->where("device_id", "!=", $deviceId)
            ->whereNull("revoked_at")
            ->orderByDesc("last_seen_at")->orderByDesc("id")
            ->get();

        $sitiosLibres = $max - 1;   // uno lo ocupa el actual
        if ($otros->count() > $sitiosLibres) {
            foreach ($otros->slice(max(0, $sitiosLibres)) as $old) {
                // Se marca, no se borra: asi el aparato expulsado puede
                // explicarle al usuario por que se cerro su sesion.
                DB::table("tv_devices")->where("id", $old->id)->update([
                    "revoked_at" => now(),
                    "revoked_by" => $name ?: "otro dispositivo",
                    "updated_at" => now(),
                ]);
                $kicked = $old->name ?: $old->device_id;
            }
        }

        $devices = DB::table('tv_devices')->where('user_id', $user->id)->get();

        return [
            'allowed' => true,
            'limit'   => $max,
            'active'  => min($devices->count(), $max),
            'kicked'  => $kicked,
        ];
    }

    /** ¿Este aparato sigue autorizado? (lo llama la app en cada arranque) */
    public static function isAllowed(User $user, string $deviceId): bool
    {
        if (trim($deviceId) === '') {
            return true;   // clientes viejos sin identificador: no romperlos
        }
        return DB::table("tv_devices")
            ->where("user_id", $user->id)
            ->where("device_id", $deviceId)
            ->whereNull("revoked_at")
            ->exists();
    }

    /**
     * Por que se cerro la sesion de este aparato (para el aviso al usuario).
     *
     * @return array{revoked:bool, by:?string}
     */
    public static function revokedInfo(User $user, string $deviceId): array
    {
        if (trim($deviceId) === '') {
            return ['revoked' => false, 'by' => null];
        }
        $row = DB::table('tv_devices')
            ->where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->first();

        if ($row && $row->revoked_at) {
            return ['revoked' => true, 'by' => $row->revoked_by];
        }
        return ['revoked' => false, 'by' => null];
    }

    /** Marca actividad (para que el "mas antiguo" sea el realmente inactivo). */
    public static function touch(User $user, string $deviceId): void
    {
        if (trim($deviceId) === '') return;
        DB::table('tv_devices')
            ->where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->update(['last_seen_at' => now()]);
    }
}
