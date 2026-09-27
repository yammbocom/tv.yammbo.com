<?php

namespace App\Http\Controllers\AppTv;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Chequeo de acceso para el paywall de la app de TV.
 *
 * Devuelve JSON puro: {"active": true|false}. La pantalla de paywall lo consulta
 * cada pocos segundos; si el usuario se suscribe desde el telefono, la TV entra sola.
 */
class AccessCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) $request->query('t', '');
        if ($token === '') {
            return response()->json(['active' => false]);
        }

        try {
            // Acepta tokens caducados con firma válida (ver AppTvToken): la app no los renueva.
            [$user, $tokenExpired] = \App\Support\AppTvToken::resolve($token);
            if (! $user) {
                return response()->json(['active' => false]);
            }
            $deviceId = (string) $request->query('d', '');
            $active = AppTvAuthController::isSubscriptionActive($user);

            // Si el aparato fue expulsado por el limite del plan, se corta aqui
            $deviceOk = true;
            try {
                if ($deviceId !== '') {
                    // Registrar el aparato si es nuevo (aplica el limite del plan);
                    // asi el movil, que no pasa por el flujo QR de la TV, queda dado de alta.
                    $exists = \Illuminate\Support\Facades\DB::table('tv_devices')
                        ->where('user_id', $user->id)->where('device_id', $deviceId)->exists();
                    // Con token caducado solo se da de alta un aparato si el usuario no
                    // tiene ninguno vivo: así un token viejo filtrado no expulsa a nadie.
                    $mayRegister = ! $tokenExpired || ! \Illuminate\Support\Facades\DB::table('tv_devices')
                        ->where('user_id', $user->id)->whereNull('revoked_at')->exists();
                    if (! $exists && $active && $mayRegister) {
                        $platform = (string) $request->query('platform', 'tv');
                        \App\Support\DeviceGuard::register($user, $deviceId, $platform);
                    }
                    $deviceOk = \App\Support\DeviceGuard::isAllowed($user, $deviceId);
                    if ($deviceOk) { \App\Support\DeviceGuard::touch($user, $deviceId); }
                }
            } catch (\Throwable $e) { $deviceOk = true; }

            $rev = ['revoked' => false, 'by' => null];
            if (! $deviceOk) {
                try { $rev = \App\Support\DeviceGuard::revokedInfo($user, $deviceId); }
                catch (\Throwable $e) {}
            }

            // Aviso de cobro rechazado (lo marca el webhook de Stripe)
            $payFail = null;
            try {
                $payFail = \Illuminate\Support\Facades\Cache::get('pago_fallido_'.$user->id);
            } catch (\Throwable $e) {}

            // Renovación del token para las apps (móvil y TV): si caduca en menos
            // de 7 días o ya caducó, y este aparato está vinculado y no expulsado,
            // se devuelve uno nuevo en "token". La app solo tiene que guardarlo en
            // lugar del que tenía. Sin device_id no se renueva: un token filtrado
            // no podría alargarse para siempre.
            $newToken = null;
            try {
                $parts = explode('.', $token);
                $claims = json_decode(base64_decode(strtr($parts[1] ?? '', '-_', '+/')), true) ?: [];
                $expSoon = (int) ($claims['exp'] ?? 0) < time() + 7 * 86400;
                if ($expSoon && $deviceId !== '' && $deviceOk
                    && \App\Support\DeviceGuard::isAllowed($user, $deviceId)) {
                    \Tymon\JWTAuth\Facades\JWTAuth::factory()->setTTL(43200);
                    $newToken = \Tymon\JWTAuth\Facades\JWTAuth::claims(['typ' => \App\Support\AppTvToken::TYP])->fromUser($user);
                }
            } catch (\Throwable $e) {
                $newToken = null;
            }

            return response()->json([
                'token'                 => $newToken,
                'payment_failed'        => ! empty($payFail),
                'payment_failed_amount' => $payFail['importe'] ?? null,
                'active'      => $active && $deviceOk,
                'device_ok'   => $deviceOk,
                'revoked'     => (bool) $rev['revoked'],
                'revoked_by'  => $rev['by'],
                'limits'      => \App\Support\DeviceGuard::limitsOf($user),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['active' => false]);
        }
    }
}
