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
            $user = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->authenticate();
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
                    if (! $exists && $active) {
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

            return response()->json([
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
