<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guarda las rutas de /panel: solo entra quien tiene el rol Spatie "admin".
 * Va después de "auth" en el grupo de rutas, así que aquí ya hay usuario
 * autenticado; si no tiene el rol, corta con 403 en vez de dejarlo pasar.
 */
class EnsurePanelAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasRole('admin')) {
            abort(403);
        }

        return $next($request);
    }
}
