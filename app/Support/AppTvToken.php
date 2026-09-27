<?php

namespace App\Support;

use App\Models\User;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Tokens de la app de TV/móvil para endpoints de SOLO LECTURA.
 *
 * La app guarda el manage_token (30 días) y no tiene forma de renovarlo. Al
 * caducar, acceso-check respondía active:false y la app enseñaba el muro de pago
 * a usuarios con suscripción activa (le pasó a un Premium el 2026-09-27; los
 * tokens de agosto caducaron todos entre el 21 y el 23 de septiembre).
 *
 * Aquí se acepta un token caducado si la firma es válida, es de un User y no
 * lleva caducado más de GRACE_DAYS. El dispositivo sigue pasando por DeviceGuard
 * (expulsión y límite del plan), que es el control real de acceso. No usar para
 * nada que inicie sesión, cobre o cambie datos: ahí se exige un token vigente.
 */
class AppTvToken
{
    private const GRACE_DAYS = 365;

    public static function userForReadOnly(string $token): ?User
    {
        if ($token === '') {
            return null;
        }

        try {
            return JWTAuth::setToken($token)->authenticate() ?: null;
        } catch (TokenExpiredException $e) {
            return self::expiredButSigned($token);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function expiredButSigned(string $token): ?User
    {
        try {
            // Solo verifica la firma (SignedWith); las fechas se comprueban aquí.
            $claims = JWTAuth::manager()->getJWTProvider()->decode($token);
        } catch (\Throwable $e) {
            return null;
        }

        $now = time();
        $exp = (int) ($claims['exp'] ?? 0);
        if ($exp <= 0 || $exp < $now - self::GRACE_DAYS * 86400) {
            return null;
        }
        if (isset($claims['nbf']) && (int) $claims['nbf'] > $now + 60) {
            return null;
        }
        if (isset($claims['prv']) && $claims['prv'] !== sha1(User::class)) {
            return null;
        }
        $sub = $claims['sub'] ?? null;
        if (! is_numeric($sub)) {
            return null;
        }

        return User::find((int) $sub);
    }
}
