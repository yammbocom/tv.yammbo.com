<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el contenido a quien no ha confirmado su correo.
 *
 * Solo afecta a sesiones web con usuario logueado; las cuentas anteriores a la
 * verificacion quedaron marcadas como verificadas, asi que no molesta a nadie
 * que ya usaba la plataforma.
 */
class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && empty($user->email_verified_at)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'email_no_verificado',
                    'message' => 'Verifica tu correo electronico para poder ingresar.',
                ], 403);
            }
            return redirect()->route('app-tv.verificar-aviso');
        }

        return $next($request);
    }
}
