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

    /** Claim que llevan los tokens de la app (manage_token) desde TYP_SINCE. */
    public const TYP = 'app_tv';

    /**
     * Momento desde el que los tokens de la app llevan typ (2026-09-27). Los
     * emitidos antes no lo tienen y se aceptan igual; los posteriores sin typ
     * (enlaces de correo, access_token de 60 min) no entran en la gracia.
     */
    private const TYP_SINCE = 1790546393;

    public static function userForReadOnly(string $token): ?User
    {
        return self::resolve($token)[0];
    }

    /**
     * @return array{0: ?User, 1: bool} usuario y si el token estaba caducado.
     * Con token caducado el llamador no debe dar de alta aparatos nuevos: un
     * token filtrado podría expulsar a los del usuario (DeviceGuard::register).
     */
    public static function resolve(string $token): array
    {
        if ($token === '') {
            return [null, false];
        }

        try {
            return [JWTAuth::setToken($token)->authenticate() ?: null, false];
        } catch (TokenExpiredException $e) {
            return [self::expiredButSigned($token), true];
        } catch (\Throwable $e) {
            return [null, false];
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
        if ((int) ($claims['iat'] ?? 0) >= self::TYP_SINCE && ($claims['typ'] ?? null) !== self::TYP) {
            return null;
        }
        $sub = $claims['sub'] ?? null;
        if (! is_numeric($sub)) {
            return null;
        }

        return User::find((int) $sub);
    }
}
