<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SharePreviewController;
use App\Services\SharePreview;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * /app/?ver={type}/{id}#/detail/... — la dirección que muestra la barra en una
 * ficha. Lo que va tras "#" no llega al servidor, pero ?ver sí: a quien no tiene
 * sesión (los bots de WhatsApp/Telegram/Facebook) se le devuelve la vista previa
 * de la ficha en vez de mandarlo al login. Con sesión no hace nada.
 * Va antes de "auth" en la ruta para poder responder a invitados.
 */
class SharePreviewForGuests
{
    public function handle(Request $request, Closure $next): Response
    {
        $ver = $request->query('ver');
        if (! auth()->check() && is_string($ver) && preg_match('~^([a-z]{2,12})/([^/]{1,64})$~D', $ver, $m)) {
            // Mismo límite que la ruta /app/detail (throttle:60,1): cada id nuevo
            // sale a TMDB/Cinemeta y escribe en la caché de ficheros.
            $key = 'share-preview:'.$request->ip();
            if (RateLimiter::tooManyAttempts($key, 60)) {
                abort(429);
            }
            RateLimiter::hit($key, 60);

            return app(SharePreviewController::class)->show($request, app(SharePreview::class), $m[1], $m[2]);
        }

        return $next($request);
    }
}
