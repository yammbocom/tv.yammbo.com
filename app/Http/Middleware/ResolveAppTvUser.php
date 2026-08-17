<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Resuelve el dueño de los endpoints /api/app-tv/* a partir de la sesión web
 * (la SPA de /app vive en el mismo dominio y manda la cookie) o de un JWT
 * Bearer (APK Android), y lo publica en el atributo `yambo_user_id`.
 *
 * El `user_id` que llegue por querystring o body se ignora: era la vía por la
 * que cualquiera podía leer la biblioteca y el estado de suscripción de otra
 * cuenta sin presentar credencial alguna.
 */
class ResolveAppTvUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = null;

        if (Auth::guard('web')->check()) {
            $userId = (int) Auth::guard('web')->id();
        } elseif ($request->bearerToken()) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
                $userId = $user ? (int) $user->getKey() : null;
            } catch (\Throwable $e) {
                return response()->json(['error' => 'invalid_token'], 401);
            }
        }

        if ($userId === null || $userId <= 0) {
            // Deja rastro del cliente que llama sin credenciales: es la única
            // forma de saber si el APK publicado manda el Bearer o sigue
            // confiando en el user_id suelto.
            Log::channel('single')->info('app-tv: petición sin credenciales', [
                'path' => $request->path(),
                'ua' => substr((string) $request->userAgent(), 0, 120),
                'sent_user_id' => $request->input('user_id'),
                'had_bearer' => $request->bearerToken() !== null,
            ]);

            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $request->attributes->set('yambo_user_id', $userId);

        return $next($request);
    }
}
