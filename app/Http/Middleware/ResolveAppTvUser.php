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
            // Solo se registra a quien manda user_id: ese es el cliente legacy
            // que queremos detectar (el APK publicado). Los escaneos sueltos no
            // lo mandan y quedarían fuera, que es justo lo que interesa para no
            // ahogar el log. Va como error porque el canal `single` filtra por
            // debajo de ese nivel y un info no llegaría a escribirse nunca.
            if ($request->has('user_id')) {
                Log::channel('single')->error('app-tv: cliente legacy sin credenciales', [
                    'path' => $request->path(),
                    'ua' => substr(preg_replace('/[[:cntrl:]]/', '', (string) $request->userAgent()), 0, 120),
                    'had_bearer' => $request->bearerToken() !== null,
                ]);
            }

            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $request->attributes->set('yambo_user_id', $userId);

        // StartSession apunta cada GET como "página anterior", y los fetch de la
        // SPA cuentan como tal. Sin esto, un redirect()->back() de Wave (cancelar
        // plan, cambiar de plan) devolvía al usuario a un JSON de la API.
        $previous = $request->hasSession() ? $request->session()->previousUrl() : null;

        $response = $next($request);

        if ($previous !== null && $request->hasSession()) {
            $request->session()->setPreviousUrl($previous);
        }

        return $response;
    }
}
